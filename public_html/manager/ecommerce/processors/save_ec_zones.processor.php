<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_manage_taxes')) {
	$e->setError(3);
	$e->dumpError();	
}

$opcode = isset($_POST['op']) ? $_POST['op'] : "keys" ;
// add rate
if($opcode=="addrate") {
	$rate = array(		
		zone => intval($_POST["zone"]),
		description => mysql_escape_string($_POST["description"]),
		rate => mysql_escape_string($_POST["rate"])		
	);	
	if($rate["zone"]) {	
		$sel = $modx->db->select("*",$modx->getFullTableName("site_ec_shipping_rates"),"zone=".$rate['zone']);
		if ($modx->db->getRecordCount($sel) != 1) 	
		$modx->db->insert($rate,$modx->getFullTableName("site_ec_shipping_rates"));
	}
}
// edit rate
else if($opcode=="edtrate") {
	$id = intval($_POST["id"]);	
	$rate = array(		
		zone => intval($_POST["zone"]),
		description => mysql_escape_string($_POST["description"]),
		rate => mysql_escape_string($_POST["rate"])		
	);		
	if($rate["zone"]) {
		$sel = $modx->db->select("*",$modx->getFullTableName("site_ec_shipping_rates"),"zone=".$rate['zone']." AND id != $id");
		if ($modx->db->getRecordCount($sel) != 1) 
		$modx->db->update($rate,$modx->getFullTableName("site_ec_shipping_rates"),"id=$id");
	}
}
// delete
else if($opcode=="delrates") {
	
	$rate = $_POST["rate"];
	if(is_array($rate) && count($rate)>0) {
		for($i=0;$i<count($rate);$i++) $rate[$i]=mysql_escape_string($rate[$i]);
		$modx->db->delete($modx->getFullTableName("site_ec_shipping_rates"),"id IN('".implode("','",$rate)."')");
	}
}

// empty cache
$header="Location: index.php?a=5300";
header($header);

?>
