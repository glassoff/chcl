<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('ec_publish_item')) {
	$e->setError(3);
	$e->dumpError();	
}

$id = $_REQUEST['id'];
// update the document
$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET published=0, editedby=".$modx->getLoginUserID().", editedon=".time().", publishedby=0, publishedon=0 WHERE id=$id;";

$rs = mysql_query($sql);
if(!$rs){
	echo "An error occured while attempting to unpublish the document.";
}
// invoke OnDocUnpublished  event
//$modx->invokeEvent("OnDocUnpublished",array("docid"=>$id));	
$header="Location: index.php?r=1&id=$id&a=5000";
header($header);

?>
