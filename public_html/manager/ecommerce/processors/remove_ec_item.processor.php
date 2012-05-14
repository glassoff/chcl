<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('ec_remove_item')) {
	$e->setError(3);
	$e->dumpError();	
}

$sql = "SELECT id FROM $dbase.`".$table_prefix."site_ec_items` WHERE $dbase.`".$table_prefix."site_ec_items`.deleted=1;";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);
$ids = array();
if($limit>0) {
	for($i=0;$i<$limit;$i++) {
		$row=mysql_fetch_assoc($rs);
		array_push($ids, @$row['id']);
	}
}

// invoke OnBeforeEmptyTrash event
/*
$modx->invokeEvent("OnBeforeEmptyTrash",
						array(
							"ids"=>$ids
						));
*/
// remove the TV content values.
$sql = "DELETE $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
		FROM $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues`  
		INNER JOIN $dbase.`".$table_prefix."site_ec_items` ON $dbase.`".$table_prefix."site_ec_items`.id = $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues`.itemid 
		WHERE $dbase.`".$table_prefix."site_ec_items`.deleted=1;";
@mysql_query($sql);

//'undelete' the document.
$sql = "DELETE FROM $dbase.`".$table_prefix."site_ec_items` WHERE deleted=1;";
$rs = mysql_query($sql);
if(!$rs) {
	echo "Something went wrong while trying to remove deleted documents!";
	exit;
} else {
	// invoke OnEmptyTrash event
	/*
	$modx->invokeEvent("OnEmptyTrash",
						array(
							"ids"=>$ids
						));
	
	// empty cache
	include_once "cache_sync.class.processor.php";
	$sync = new synccache();
	$sync->setCachepath("../assets/cache/");
	$sync->setReport(false);
	$sync->emptyCache(); // first empty the cache	
	*/	
	// finished emptying cache - redirect
	$header="Location: index.php?r=1&a=5005";
	header($header);
}
?>
