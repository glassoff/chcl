<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('save_document')||!$modx->hasPermission('publish_document')) {
	$e->setError(3);
	$e->dumpError();	
}

$id = $_REQUEST['id'];
$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET unpub_date=0, editedby=".$modx->getLoginUserID().", editedon=".time().", publishedby=".$modx->getLoginUserID().", publishedon=".time()." WHERE id=$id;";

$rs = mysql_query($sql);
if(!$rs){
	echo "An error occured while attempting to publish the document.";
}
$header="Location: index.php?r=1&id=$id&a=5000";
header($header);

?>
