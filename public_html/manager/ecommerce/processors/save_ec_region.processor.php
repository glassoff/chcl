<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_manage_taxes')) {
	$e->setError(3);
	$e->dumpError();	
}

$opcode = isset($_POST['op']) ? $_POST['op'] : "keys" ;
// add region
if($opcode=="addregion") {
	$region = array(
		name => mysql_escape_string($_POST["name"]),
		rate_zone => intval($_POST["rate_zone"]),
		note => mysql_escape_string($_POST["note"]),
		listindex => intval($_POST["listindex"]),
		isactive => intval($_POST["isactive"])		
	);	
	if($region["name"]) {		
		$modx->db->insert($region,$modx->getFullTableName("site_ec_regions"));
	}
}
// edit region
else if($opcode=="edtregion") {
	$id = intval($_POST["id"]);	
	$region = array(
		name => mysql_escape_string($_POST["name"]),
		rate_zone => intval($_POST["rate_zone"]),
		note => mysql_escape_string($_POST["note"]),
		listindex => intval($_POST["listindex"]),
		isactive => intval($_POST["isactive"])		
	);		
	if($region["name"]) {
		$modx->db->update($region,$modx->getFullTableName("site_ec_regions"),"id=$id");
	}
}
// delete
else if($opcode=="delregions") {
	
	$region = $_POST["region"];
	if(is_array($region) && count($region)>0) {
		for($i=0;$i<count($region);$i++) $region[$i]=mysql_escape_string($region[$i]);
		$modx->db->delete($modx->getFullTableName("site_ec_regions"),"id IN('".implode("','",$region)."')");
	}
}

// empty cache
$header="Location: index.php?a=5300";
header($header);

?>
