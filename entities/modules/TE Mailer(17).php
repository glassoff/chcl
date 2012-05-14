<?php 
/*
	TE Mailer v1.5beta

	Author: Brandon Jones / Kevin Frey / TransEffect LLC
	Copyright: TransEffect LLC (http://www.transeffect.com)
	Description: Used to send bulk email (but not spam!) from within MODx

	Update History:
	8-21-09 v1.5beta released
	9-17-07 v1.0 released
*/
 
global $theme;

//-- get theme
$tb_prefix = $modx->db->config['table_prefix'];
$theme = $modx->db->select('setting_value', '`' . $tb_prefix . 'system_settings`', 'setting_name=\'manager_theme\'', '');
$theme = $modx->db->getRow($theme);
$theme = ($theme['setting_value'] <> '') ? '/' . $theme['setting_value'] : '';

include_once($path . '/temailer.php');
?>
