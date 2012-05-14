<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('delete_document')) {
	$e->setError(3);
	$e->dumpError();	
}
?>
<?php
// check the document doesn't have any children
$id=intval($_GET['id']);
$deltime = time();
$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET deleted=1, deletedby=".$modx->getLoginUserID().", deletedon=$deltime WHERE id=$id;";
$rs = mysql_query($sql);
if(!$rs) {
	echo "Something went wrong while trying to set the document to deleted status...";
	exit;
} else {
	$header="Location: index.php?r=1&a=5000";
	header($header);
}
?>
