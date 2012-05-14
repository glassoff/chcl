<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!$modx->hasPermission('ec_manage_discounts')) {
	$e->setError(3);
	$e->dumpError();
}

$id = is_numeric($_POST['id']) ? $_POST['id'] : "";
$name = mysql_escape_string($_POST['name']);
$rule = mysql_escape_string($_POST['rule']);
$description = mysql_escape_string($_POST['description']);
$groupids = is_array($_POST['groupids']) ? serialize($_POST['groupids']) : '';
$active = $_POST['active'];



$actionToTake = "new";
if ($_POST['mode'] == '5402') {
	$actionToTake = "edit";
}
// get the document, but only if it already exists (d'oh!)
if ($actionToTake != "new") {
	$sql = "SELECT * FROM $dbase.`" . $table_prefix . "site_ec_discounts` WHERE id = $id;";
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
		$sql = "INSERT INTO $dbase.`" . $table_prefix . "site_ec_discounts` (name,rule,groupids,description,active)
			    VALUES('".$name."','".$rule."','".$groupids."','".$description."',". $active . ")";
		$rs = mysql_query($sql);
		
		if (!$rs) {
			$modx->manager->saveFormValues(5403);
			echo "An error occured while attempting to save the new document: " . mysql_error();
			exit;
		}

		if (!$key = mysql_insert_id()) {
			$modx->manager->saveFormValues(5403);
			echo "Couldn't get last insert key!";
			exit;
		}

		
		// redirect/stay options
		if ($_POST['stay'] != '') {			
			// document
			if ($_POST['mode'] == "5401")
				$a = ($_POST['stay'] == '2') ? "5403&id=$key" : "5401&pid=$parent";
			$header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
		} else {
			$header = "Location: index.php?r=1&id=$id&a=5400&dv=1";
		}
		header($header);
		break;
	case 'edit' :		
		// update the document
		$sql = "UPDATE $dbase.`" . $table_prefix . "site_ec_discounts` SET name='$name', rule='$rule',groupids='$groupids',description='$description', active=$active WHERE id=$id;";

		$rs = mysql_query($sql);
		if (!$rs) {
			echo "An error occured while attempting to save the edited document. The generated SQL is: <i> $sql </i>.";
		}

		if ($_POST['stay'] != '') {
			$id = $_REQUEST['id'];				
			// document
			$a = ($_POST['stay'] == '2') ? "5402&id=$id" : "5401";				
			$header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
		} else {
			$header = "Location: index.php?r=1&id=$id&a=5400&dv=1";
		}		
		header($header);	
		break;
	default :
		header("Location: index.php?a=5400");
		exit;
}
?>
