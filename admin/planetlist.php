<?php

/**
 * planetlist.php
 *
 * @version 1.0
 * @copyright 2008 by ??????? for XNova
 */

define('INSIDE'  , true);
define('INSTALL' , false);
define('IN_ADMIN', true);

require('../common.' . substr(strrchr(__FILE__, '.'), 1));

if($user['authlevel'] < 1)
{
  AdminMessage($lang['adm_err_denied']);
}

$parse = $lang;
$query = doquery("SELECT * FROM {{planets}} WHERE planet_type='1'");
$i = 0;
while ($u = mysql_fetch_array($query)) {
  $parse['planetes'] .= "<tr>"
  . "<td class=b><center><b>" . $u[0] . "</center></b></td>"
  . "<td class=b><center><b>" . $u[1] . "</center></b></td>"
  . "<td class=b><center><b>" . $u[4] . "</center></b></td>"
  . "<td class=b><center><b>" . $u[5] . "</center></b></td>"
  . "<td class=b><center><b>" . $u[6] . "</center></b></td>"
  . "</tr>";
  $i++;
}

if ($i == "1")
  $parse['planetes'] .= "<tr><th class=b colspan=5>Il y a qu'une seule plan&egrave;te</th></tr>";
else
  $parse['planetes'] .= "<tr><th class=b colspan=5>Il y a {$i} plan&egrave;tes</th></tr>";

display(parsetemplate(gettemplate('admin/planetlist_body'), $parse), 'Planetlist', false, '', true);

// Created by e-Zobar. All rights reversed (C) XNova Team 2008
?>