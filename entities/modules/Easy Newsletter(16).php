<?php 
/*
Easy Newsletter 0.3
Copyright by: Flux - www.simpleshop.dk
Date: 10. september 2007
Notes: This newsletter system is heavily inspired by KoopsmailinglistX so a bow in respect and appreciation to the original author Jasper Koops and sottwell@sottwell.com who ported it to MODx.

This is version 0.3 so there might be some errors I have missed and functionality that you might think is missing. I have not tested the system with say 1000 subscribers. Error logging/handling is very simple - It will just stop if an error has occurred with no resume function.
---------------------------------------------------------------------
This file is part of Easy Newsletter 0.3

Easy Newsletter 0.3 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

Easy Newsletter 0.3 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. 
---------------------------------------------------------------------*/

$sql = "SHOW TABLES LIKE 'easynewsletter_config'";
$rs = $modx->db->query($sql);
$count = $modx->db->getRecordCount($rs);
if($count < 1) {
  $sql = "CREATE TABLE IF NOT EXISTS `easynewsletter_config` (
  `id` int(11) NOT NULL default '0',
  `mailmethod` varchar(20) NOT NULL default '',
  `port` int(11) NOT NULL default '0',
  `smtp` varchar(200) NOT NULL default '',
  `auth` varchar(5) NOT NULL default '',
  `authuser` varchar(100) NOT NULL default '',
  `authpassword` varchar(100) NOT NULL default '',
  `sendername` varchar(200) NOT NULL default '',
  `senderemail` varchar(200) NOT NULL default '',
  `lang_frontend` varchar(100) NOT NULL default '',
  `lang_backend` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`id`)
)";
$modx->db->query($sql);
$sql = "INSERT INTO `easynewsletter_config` VALUES (1, 'IsSMTP', 0, '', 'false', '', '', '', '', 'english', 'english')";
$modx->db->query($sql);
  $sql = "CREATE TABLE IF NOT EXISTS `easynewsletter_newsletter` (
  `id` int(11) NOT NULL auto_increment,
  `date` date NOT NULL default '0000-00-00',
  `status` int(11) NOT NULL default '0',
  `sent` int(11) NOT NULL default '0',
  `header` longtext,
  `subject` text NOT NULL,
  `newsletter` longtext,
  `footer` longtext,
  PRIMARY KEY  (`id`)
)";
$modx->db->query($sql);
$sql = "CREATE TABLE IF NOT EXISTS `easynewsletter_subscribers` (
  `id` int(11) NOT NULL auto_increment,
  `firstname` varchar(50) NOT NULL default '',
  `lastname` varchar(50) NOT NULL default '',
  `email` varchar(50) NOT NULL default '',
  `status` int(11) NOT NULL default '1',
  `blocked` int(11) NOT NULL default '0',
  `lastnewsletter` varchar(50) NOT NULL default '',
  `created` date NOT NULL default '0000-00-00',
  PRIMARY KEY  (`id`)
)";
$modx->db->query($sql);
echo 'Easy Newsletter has now been installed. Please click <strong>Easy Newsletter</strong> in the navigation bar.';
} else {
$theme = $modx->config['manager_theme'];
$sql = "SELECT * FROM `easynewsletter_config` WHERE `id` = 1";
$result = $modx->db->query($sql);
include($path.'languages/'.mysql_result($result,$i,"lang_backend").'.php');
echo '
<html>
<head>
	<title>MODx</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="media/style/'.$theme.'/style.css?" />
</head>
<body>
<br />
<div class="sectionHeader">Easy Newsletter 0.3 - &copy; 2007 <a href="http://www.simpleshop.dk" target="_blank">SimpleShop.dk</a></div><div class="sectionBody">
<div class="searchbar">
&nbsp;&nbsp;&nbsp;<b>'.$lang_links_header.'</b>&nbsp;&nbsp;&nbsp;<a href="index.php?a=112&id='.$modId.'&action=1">'.$lang_links_subscribers.'</a> | <a href="index.php?a=112&id='.$modId.'&p=1&action=1">'.$lang_links_newsletter.'</a> | <a href="index.php?a=112&id='.$modId.'&p=2&action=1">'.$lang_links_configuration.'</a>
</div><br />';
include($path.'backend.php');
echo '
</div>
</body>
</html>
';
return;
}

?>
