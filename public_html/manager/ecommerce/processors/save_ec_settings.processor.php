<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('ec_settings')) {
	$e->setError(3);
	$e->dumpError();	
}

foreach ($_POST as $k => $v) {	
	
	$sql = "REPLACE INTO ".$modx->getFullTableName("ec_settings")." (setting_name, setting_value) VALUES('".mysql_escape_string($k)."', '".mysql_escape_string($v)."')";
	
	if(!@$rs = mysql_query($sql)) {
		echo "Failed to update setting value!";
		exit;
	}
}

// empty cache
$header="Location: index.php?a=2";
header($header);
?>
