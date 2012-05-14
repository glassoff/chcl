<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!$modx->hasPermission('ec_manage_orders')) {
	$e->setError(3);
	$e->dumpError();
}



switch ($_REQUEST['a']) {
	case '5510' :			
		$name = mysql_escape_string($_POST['name']);
		$listindex = intval(mysql_escape_string($_POST['listindex']));
		$sql = "INSERT INTO $dbase.`" . $table_prefix . "ec_order_status` (name,listindex)
			    VALUES('".$name."',".$listindex.")";
		$rs = mysql_query($sql);
		//echo $sql;
		break;
	case '5511' :		
		// remove order status
		$remove_ids = $_POST['remove'];
		$remove_ids = @implode(',', $remove_ids);
		if (!empty($remove_ids)) {
			$sql = "DELETE FROM $dbase.`".$table_prefix."ec_order_status` WHERE id IN ($remove_ids);";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to set the document to deleted status...";
				exit;
			} 	
		}
		foreach ($_POST['name'] as $k => $v) {		
			if (!isset($_POST['remove'][$k])) {				
				$name = mysql_escape_string($v);
				$listindex = intval($_POST['listindex'][$k]);				
				$sql = "UPDATE $dbase.`".$table_prefix."ec_order_status` SET name='$name',listindex=$listindex WHERE id = $k";
				$rs = mysql_query($sql);
			//echo $sql;	
			}			
		}
			
	break;
	
}
header("Location: index.php?a=5500");
?>
