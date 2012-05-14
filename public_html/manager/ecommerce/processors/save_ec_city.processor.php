<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_manage_taxes')) {
	$e->setError(3);
	$e->dumpError();	
}

$opcode = isset($_POST['op']) ? $_POST['op'] : "keys" ;
// add city
if($opcode=="addcity") {
	list($city,$http_equiv) = explode(";",$_POST["city"]);
	$city = array(
		rid => mysql_escape_string($_POST["rid"]),
		name => mysql_escape_string($_POST["name"]),
		postcode => mysql_escape_string($_POST["postcode"]),
		rate_zone => intval($_POST["rate_zone"]),
		//description => mysql_escape_string($_POST["description"]),
		note => mysql_escape_string($_POST["note"]),
		listindex => intval($_POST["listindex"]),
		isactive => intval($_POST["isactive"])		
	);	
	if($city["name"]) {		
		$modx->db->insert($city,$modx->getFullTableName("site_ec_cities"));
	}
}
// edit city
else if($opcode=="edtcity") {
	$id = intval($_POST["id"]);	
	$city = array(
		name => mysql_escape_string($_POST["name"]),
		postcode => mysql_escape_string($_POST["postcode"]),
		rate_zone => intval($_POST["rate_zone"]),
		//description => mysql_escape_string($_POST["description"]),
		note => mysql_escape_string($_POST["note"]),
		listindex => intval($_POST["listindex"]),
		isactive => intval($_POST["isactive"])		
	);		
	if($city["name"]) {
		$modx->db->update($city,$modx->getFullTableName("site_ec_cities"),"id=$id");
	}
}
// delete
else if($opcode=="delcities") {
	
	$city = $_POST["city"];
	if(is_array($city) && count($city)>0) {
		for($i=0;$i<count($city);$i++) $city[$i]=mysql_escape_string($city[$i]);
		$modx->db->delete($modx->getFullTableName("site_ec_cities"),"id IN('".implode("','",$city)."')");
	}
}

// empty cache
$header="Location: index.php?a=5300";
header($header);

?>
