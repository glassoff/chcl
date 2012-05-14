<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('ec_delete_item')) {	
	$e->setError(3);
	$e->dumpError();	
}

$id=$_REQUEST['id'];

// get the timestamp on which the document was deleted.
$sql = "SELECT deletedon FROM $dbase.`".$table_prefix."site_ec_items` WHERE $dbase.`".$table_prefix."site_ec_items`.id=".$id." AND deleted=1;";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);
if($limit!=1) {
	echo "Couldn't find document to determine it's date of deletion!";
	exit;
} else {
	$row=mysql_fetch_assoc($rs);
	$deltime = $row['deletedon'];
}

$children = array();
//'undelete' the document.
$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET deleted=0, deletedby=0, deletedon=0 WHERE id=$id;";
$rs = mysql_query($sql);
if(!$rs) {
	echo "Something went wrong while trying to set the document to undeleted status...";
	exit;
} else {
	//event place
	$header="Location: index.php?r=1&a=5000";	
	header($header);
}
?>
