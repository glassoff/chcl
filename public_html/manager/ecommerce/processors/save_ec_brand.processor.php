<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_manage_brands')) {
	$e->setError(3);
	$e->dumpError();	
}

$opcode = isset($_POST['op']) ? $_POST['op'] : "" ;
// add brand
if($opcode=="addbrand") {
	$brand = array(
		name => mysql_escape_string($_POST["name"]),		
		listindex => intval($_POST["listindex"]),
		isactive => intval($_POST["isactive"])		
	);	
	if($brand["name"]) {		
		$modx->db->insert($brand,$modx->getFullTableName("site_ec_brands"));
	}
}
// edit brand
else if($opcode=="edtbrand") {
	$id = intval($_POST["id"]);	
	$brand = array(
		name => mysql_escape_string($_POST["name"]),		
		listindex => intval($_POST["listindex"]),
		isactive => intval($_POST["isactive"])		
	);		
	if($brand["name"]) {
		$modx->db->update($brand,$modx->getFullTableName("site_ec_brands"),"id=$id");
	}
}
// delete
else if($opcode=="delbrands") {	
	$brand = $_POST["brand"];
	if(is_array($brand) && count($brand)>0) {
		for($i=0;$i<count($brand);$i++) $brand[$i]=mysql_escape_string($brand[$i]);
		$modx->db->delete($modx->getFullTableName("site_ec_brands"),"id IN('".implode("','",$brand)."')");
	}
}

// empty cache
$header="Location: index.php?a=3000";
header($header);

?>
