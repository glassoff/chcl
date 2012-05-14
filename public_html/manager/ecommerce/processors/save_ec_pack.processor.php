<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_manage_packs')) {
	$e->setError(3);
	$e->dumpError();	
}

$opcode = isset($_POST['op']) ? $_POST['op'] : "" ;
// add pack
if($opcode=="addpack") {
	$pack = array(
		name => mysql_escape_string($_POST["name"]),		
		weight => floatval($_POST["weight"])		
	);	
	if($pack["name"]) {		
		$modx->db->insert($pack,$modx->getFullTableName("site_ec_packs"));
	}
}
// edit pack
else if($opcode=="edtpack") {
	$id = intval($_POST["id"]);	
	$pack = array(
		name => mysql_escape_string($_POST["name"]),		
		weight => floatval($_POST["weight"])		
	);		
	if($pack["name"]) {
		$modx->db->update($pack,$modx->getFullTableName("site_ec_packs"),"id=$id");
	}
}
// delete
else if($opcode=="delpacks") {
	
	$pack = $_POST["pack"];
	if(is_array($pack) && count($pack)>0) {
		for($i=0;$i<count($pack);$i++) $pack[$i]=mysql_escape_string($pack[$i]);
		$modx->db->delete($modx->getFullTableName("site_ec_packs"),"id IN('".implode("','",$pack)."')");
	}
}

// empty cache
$header="Location: index.php?a=3002";
header($header);

?>
