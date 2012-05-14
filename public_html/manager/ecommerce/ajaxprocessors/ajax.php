<?php
	header("Content-type: text/html; charset=windows-1251");
	include_once $base_path."manager/includes/controls/datagrid.class.php";
	$oktxns = array();
	$theme = $manager_theme ? "$manager_theme/":"";
	function ajax_encode($value) {		
		return mb_convert_encoding($value, "windows-1251", "UTF-8");
	} 
	function db_getrows($qry) {
		$result = mysql_query($qry);
		if ($result === FALSE) return NULL;
		
		$ret = array();
		while ($row = mysql_fetch_assoc($result)) {
			$ret[] = $row;
		}
		
		mysql_free_result($result);
		return $ret;
	}

	include_once($base_path."manager/ecommerce/ajaxprocessors/tax.php");
	include_once($base_path."manager/ecommerce/ajaxprocessors/drl.php");
	
	$txn = $_POST['trxntype'];
	if (in_array($txn, $oktxns)) {
		$txn();
	}
?>
