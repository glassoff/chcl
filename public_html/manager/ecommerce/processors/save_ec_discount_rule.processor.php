<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!$modx->hasPermission('ec_manage_discounts')) {
	$e->setError(3);
	$e->dumpError();
}
$list_id = intval($_REQUEST['discount_id']);
$minqty = intval($_POST['minqty']);
$rate = floatval($_POST['rate']);
$sql = "INSERT INTO $dbase.`" . $table_prefix . "site_ec_discount_entries` (minqty,discount,list_id)
	    VALUES(".$minqty.",".$rate . ",".$list_id.")";
$rs = mysql_query($sql);
header("Location: index.php?a=5400");
exit;

?>
