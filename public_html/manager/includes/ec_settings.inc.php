<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// get the settings from the database.
$ec_settings = array();
//if ($modx && count($modx->config)>0) $settings = $modx->config;
//if (count($modx->config)>0) $settings = $modx->config;
//else{
	$sql = "SELECT setting_name, setting_value FROM $dbase.`".$table_prefix."ec_settings`";
	$rs = mysql_query($sql);
	$number_of_ec_settings = mysql_num_rows($rs);
	while ($row = mysql_fetch_assoc($rs)) {
		$ec_settings[$row['setting_name']] = $row['setting_value'];
	}
//}
extract($ec_settings, EXTR_OVERWRITE);
// add for backwards compatibility - garryn FS#104
?>