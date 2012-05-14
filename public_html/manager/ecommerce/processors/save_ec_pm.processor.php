<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!$modx->hasPermission('ec_payment_methods')) {
	$e->setError(3);
	$e->dumpError();
}

$id = is_numeric($_POST['id']) ? $_POST['id'] : "";
$name = mysql_escape_string($_POST['name']);
$description = mysql_escape_string($_POST['description']);
$listindex = intval(mysql_escape_string($_POST['listindex']));
$active = $_POST['active'];
$payment_page = intval($_POST['payment_page']);
$auto = intval($_POST['auto']);
$confirm = intval($_POST['confirm']);
$params = mysql_escape_string($_POST['properties']);


$actionToTake = "new";
if ($_POST['mode'] == '5204') {
	$actionToTake = "edit";
}
// get the document, but only if it already exists (d'oh!)
if ($actionToTake != "new") {
	$sql = "SELECT * FROM $dbase.`" . $table_prefix . "site_ec_payment_methods` WHERE id = $id;";
	$rs = mysql_query($sql);
	$limit = mysql_num_rows($rs);
	if ($limit > 1) {
		$e->setError(6);
		$e->dumpError();
	}
	if ($limit < 1) {
		$e->setError(7);
		$e->dumpError();
	}
	$existingDocument = mysql_fetch_assoc($rs);
}
switch ($actionToTake) {
	case 'new' :
		$sql = "INSERT INTO $dbase.`" . $table_prefix . "site_ec_payment_methods` (auto,confirm,name,description,listindex,payment_page,params,active)
			    VALUES($auto,$confirm,'".$name."','".$description."',".$listindex.",".$payment_page.",'".$params."'," . $active . ")";
		$rs = mysql_query($sql);
		//echo $sql;
		if (!$rs) {
			$modx->manager->saveFormValues(5203);
			echo "An error occured while attempting to save the new document: " . mysql_error();
			exit;
		}

		if (!$key = mysql_insert_id()) {
			$modx->manager->saveFormValues(5203);
			echo "Couldn't get last insert key!";
			exit;
		}

		
		// redirect/stay options
		if ($_POST['stay'] != '') {			
			// document
			if ($_POST['mode'] == "5204")
				$a = ($_POST['stay'] == '2') ? "5204&id=$key" : "5203&pid=$parent";
			$header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
		} else {
			$header = "Location: index.php?r=1&id=$id&a=5202&dv=1";
		}
		header($header);
		
		break;
	case 'edit' :		
		// update the document
		$sql = "UPDATE $dbase.`" . $table_prefix . "site_ec_payment_methods` SET auto = $auto,confirm = $confirm,name='$name', description='$description', payment_page=$payment_page, listindex=$listindex, active=$active, params='$params' WHERE id=$id;";

		$rs = mysql_query($sql);
		if (!$rs) {
			echo "An error occured while attempting to save the edited document. The generated SQL is: <i> $sql </i>.";
		}

		if ($_POST['stay'] != '') {
			$id = $_REQUEST['id'];				
			// document
			$a = ($_POST['stay'] == '2') ? "5204&id=$id" : "5203";				
			$header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
		} else {
			$header = "Location: index.php?r=1&id=$id&a=5202&dv=1";
		}		
		header($header);	
		break;
	default :
		header("Location: index.php?a=5202");
		exit;
}
?>
