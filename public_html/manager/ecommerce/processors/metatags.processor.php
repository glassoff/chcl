<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_edit_item')) {
	$e->setError(3);
	$e->dumpError();	
}
$opcode = isset($_POST['op']) ? $_POST['op'] : "keys" ;
$item_id = intval($_POST["item_id"]);
// add tag
if($opcode=="addtag") {
	list($tag,$http_equiv) = explode(";",$_POST["tag"]);
	$f = array(
		item_id => $item_id,
		name => mysql_escape_string($_POST["tagname"]),
		tag => mysql_escape_string($tag),
		tagvalue => mysql_escape_string($_POST["tagvalue"]),
		http_equiv => intval($http_equiv)
	);	
	if($f["name"] && $f["tagvalue"]) {
		$modx->db->insert($f,$modx->getFullTableName("site_ec_item_metatags"));
	}
}
// edit tag
else if($opcode=="edttag") {
	$id = intval($_POST["id"]);
	list($tag,$http_equiv) = explode(";",$_POST["tag"]);
	$f = array(	
		name => mysql_escape_string($_POST["tagname"]),
		tag => mysql_escape_string($tag),
		tagvalue => mysql_escape_string($_POST["tagvalue"]),
		http_equiv => intval($http_equiv)
	);	
	if($f["name"] && $f["tagvalue"]) {
		$modx->db->update($f,$modx->getFullTableName("site_ec_item_metatags"),"id='$id'");
	}
}
// delete
else if($opcode=="deltag") {
	$f = $_POST["tag"];
	if(is_array($f) && count($f)>0) {
		for($i=0;$i<count($f);$i++) $f[$i]=mysql_escape_string($f[$i]);
		$modx->db->delete($modx->getFullTableName("site_ec_item_metatags"),"id IN('".implode("','",$f)."')");
	}
}

// empty cache
$header="Location: index.php?a=5002&id=".$item_id;
header($header);

?>
