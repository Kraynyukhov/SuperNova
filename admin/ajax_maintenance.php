<?php

require('../includes/init.' . substr(strrchr(__FILE__, '.'), 1));

$user = sn_autologin();

if ($user['authlevel'] < 3)
{
  message($lang['sys_noalloaw'], $lang['sys_noaccess']);
  die();
}

lng_include('admin');

$totaltime = microtime(true);
$msg = '<div align="left"><ul>';

doquery('START TRANSACTION;');

$msg .= sprintf($lang['adm_inactive_removed'], $rows);

$ques = array(
  'DELETE {{users}}.* FROM {{users}} WHERE `user_as_ally` IS NULL and `onlinetime` < unix_timestamp(now()) - ( 60 * 60 * 24 * 45);',

  'DELETE FROM `{{notes}}`     WHERE `owner`          not in (select id from {{users}});',
  'DELETE FROM `{{fleets}}`    WHERE `fleet_owner`    not in (select id from {{users}});',
// FK 'DELETE FROM `{{buddy}}`     WHERE `sender`         not in (select id from {{users}});',
// FK  'DELETE FROM `{{buddy}}`     WHERE `owner`          not in (select id from {{users}});',
  'DELETE FROM `{{annonce}}`   WHERE `user`           not in (select id from {{users}});',
//  'DELETE FROM `{{messages}}`  WHERE `message_sender` not in (select id from {{users}});',
  'DELETE FROM `{{messages}}`  WHERE `message_owner`  not in (select id from {{users}});',
  'DELETE FROM `{{planets}}`   WHERE `id_owner`       not in (select id from {{users}});',
//  'DELETE FROM `{{rw}}`        WHERE `id_owner1`      not in (select id from {{users}});',
//  'DELETE FROM `{{rw}}`        WHERE `id_owner2`      not in (select id from {{users}});',
  'DELETE FROM `{{referrals}}` WHERE `id`             not in (select id from {{users}});',
  'DELETE FROM `{{referrals}}` WHERE `id_partner`     not in (select id from {{users}});',
  /*
    'DELETE {{messages}}.* FROM {{messages}} LEFT OUTER JOIN {{users}} ON {{messages}}.message_owner = {{users}}.id WHERE {{users}}.username IS NULL;',
    'DELETE {{planets}}.* FROM {{planets}} LEFT OUTER JOIN {{users}} ON {{planets}}.id_owner = {{users}}.id WHERE {{users}}.username IS NULL;',
    'DELETE {{rw}}.* FROM {{rw}} LEFT OUTER JOIN {{users}} ON {{rw}}.id_owner1 = {{users}}.id WHERE {{users}}.username IS NULL;',
    'DELETE {{rw}}.* FROM {{rw}} LEFT OUTER JOIN {{users}} ON {{rw}}.id_owner2 = {{users}}.id WHERE {{users}}.username IS NULL;',
   */

  // 
  'DELETE FROM {{statpoints}} WHERE stat_type=1 AND id_owner not in (select id from {{users}});',

  'DELETE FROM {{alliance}} WHERE id not in (select ally_id from {{users}} WHERE `user_as_ally` IS NOT NULL group by ally_id);',
  'DELETE FROM {{statpoints}} WHERE stat_type=2 AND id_owner not in (select id from {{alliance}});',
  "UPDATE {{users}} SET ally_id = null, ally_name = null, ally_rank_id=0 WHERE ally_id not in (select id from {{alliance}});",

  'DELETE FROM {{messages}} WHERE message_time < unix_timestamp(now()) - (60 * 60 * 24 * 30);',
  'DELETE FROM {{rw}} WHERE time < unix_timestamp(now()) - (60 * 60 * 24 * 14);',
  'DELETE FROM {{chat}} WHERE timestamp < unix_timestamp(now()) - (60 * 60 * 24 * 14);',
  'DELETE FROM {{aks}} WHERE `id` NOT IN (SELECT DISTINCT `fleet_group` FROM {{fleets}});',

  // Recalculate Alliance members
  "UPDATE {{alliance}} as a LEFT JOIN (SELECT ally_id, count(*) as ally_memeber_count FROM {{users}} WHERE ally_id IS NOT NULL GROUP BY ally_id) as u ON u.ally_id = a.id SET a.`ally_members` = u.ally_memeber_count;",
);

foreach ($ques as $que)
{
  $QryResult = doquery($que);

  $que = str_replace('{{', "", $que);
  $que = str_replace('}}', "", $que);
  $msg .= '<li>' . htmlspecialchars($que) . ' --- <font color=';
  if ($QryResult)
  {
    $msg .= 'green>OK.';
  }
  else
  {
    $msg .= 'red>FAILED!';
  };
  $msg .= '</font> ' . mysql_affected_rows($link) . ' ' . $lang['adm_records'];
  set_time_limit(120);
}
$msg .= '</ul></div>';

$user_count = doquery("SELECT COUNT(*) AS user_count FROM {{users}} WHERE user_as_ally IS NULL;", '', true);
$config->db_saveItem('users_amount', $user_count['user_count']);

doquery('COMMIT;');

$totaltime = microtime(true) - $totaltime;

$msg = htmlspecialchars($msg);
$xml = "<message>" . $msg . ' ' . $totaltime . "</message>";

header('Content-type: text/xml');
echo $xml;

?>
