<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('ec_manage_discounts')) {
	$e->setError(3);
	$e->dumpError();	
}
$id=intval($_REQUEST['id']);
//'undelete' the document.
$sql = "DELETE FROM $dbase.`".$table_prefix."site_ec_discounts` WHERE id=$id;";
$rs = mysql_query($sql);
if(!$rs) {
	echo "Something went wrong while trying to remove deleted documents!";
	exit;
} else {
	$header="Location: index.php?r=1&a=5400";
	header($header);	
}
?>
