<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if (!$modx->hasPermission('ec_new_item') || !$modx->hasPermission('ec_edit_item')) {
	$e->setError(3);
	$e->dumpError();
}

$id = is_numeric($_POST['id']) ? $_POST['id'] : "";
$pagetitle = mysql_escape_string($_POST['pagetitle']); //replace apostrophes with ticks :(
$menuindex = mysql_escape_string($_POST['menuindex']);
$parent = !empty($_POST['parent']) ? intval($_POST['parent']) : 0;

$tv_id = 56;
$sql = "SELECT value FROM ".$modx->getFullTableName('site_tmplvar_contentvalues');
$sql.= " WHERE contentid = $parent AND tmplvarid=$tv_id LIMIT 1";					
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);					
if($limit===1) {
	$item = mysql_fetch_assoc($rs);
	$folder_code = trim($item['value']);						  
} else {	
	$folder_code = '';			
}



$cds = !empty($_POST['cds']) ? intval($_POST['cds']) : 0;
$medium = !empty($_POST['medium']) ? mysql_escape_string($_POST['medium']) : '';
$brand_id = !empty($_POST['brand_id']) ? intval($_POST['brand_id']) : 0;
$pack_id = !empty($_POST['pack_id']) ? intval($_POST['pack_id']) : 0;
$template = !empty($_POST['template']) ? intval($_POST['template']) : 0;
$sell = !empty($_POST['sell']) ? intval($_POST['sell']) : 0;
$new = !empty($_POST['new']) ? intval($_POST['new']) : 0;
$soon = !empty($_POST['soon']) ? intval($_POST['soon']) : 0;
$popular = !empty($_POST['popular']) ? intval($_POST['popular']) : 0;
$recommended = !empty($_POST['recommended']) ? intval($_POST['recommended']) : 0;
$byorder = !empty($_POST['byorder']) ? intval($_POST['byorder']): 0;
$published = !empty($_POST['published']) ? intval($_POST['published']): 0;
$date_issue = $_POST['date_issue'];
$keywords = $_POST['keywords'];
$metatags = $_POST['metatags'];
$retail_price = floatval($_POST['retail_price']);
$mdealer_price = floatval($_POST['mdealer_price']);
$dealer_price = floatval($_POST['dealer_price']);
$mdealer_cnt = floatval($_POST['mdealer_cnt']);
$dealer_cnt = floatval($_POST['dealer_cnt']);
$sku = intval($_POST['sku']);
$acc_id = mysql_escape_string((trim($_POST['acc_id'])));
$longtitle = mysql_escape_string($_POST['longtitle']);
$variablesmodified = explode(",", $_POST['variablesmodified']);
$similaritems = mysql_escape_string($_POST['similaritems']);




if (trim($pagetitle == "")) {
	if ($type == "reference") {
		$pagetitle = $_lang['untitled_weblink'];
	} else {
		$pagetitle = $_lang['untitled_ec_item'];
	}
}

$actionToTake = "new";
if ($_POST['mode'] == '5002') {
	$actionToTake = "edit";
}

if ($actionToTake == 'new') {
	$a = 5001;
	$sql = "SELECT id FROM ".$modx->getFullTableName('site_ec_items')." WHERE acc_id = '$acc_id'  LIMIT 1";
} else {
	$a = 5002;
	$sql = "SELECT id FROM ".$modx->getFullTableName('site_ec_items')." WHERE acc_id = '$acc_id' AND id <> '$id' LIMIT 1";	
}

$rs = mysql_query($sql);

$limit = mysql_num_rows($rs);					
if($limit==1) {	 
	$_SESSION['mngform'][$a] = $_POST;
	$modx->manager->saveFormValues($a);
	$url = "index.php?&id=$id&a=$a&r=1&stay=" . $_POST['stay'];
	include_once "header.inc.php";
	$modx->webAlert($_lang['dublicated_acc_id'], $url);
	include_once "footer.inc.php";
	exit;
} 



$currentdate = time();

if (empty ($date_issue)) {
	$date_issue = 0;
} else {
	list ($d, $m, $Y, $H, $M, $S) = sscanf($date_issue, "%2d-%2d-%4d %2d:%2d:%2d");
	$date_issue = mktime($H, $M, $S, $m, $d, $Y);

	
}


// Modified by Raymond for TV - Orig Added by Apodigm - DocVars
// get document groups for current user

$tmplvars = array ();
if ($_SESSION['mgrDocgroups']) {
	$docgrp = implode(",", $_SESSION['mgrDocgroups']);
}
$sql = "SELECT DISTINCT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value ";
$sql .= "FROM $dbase.`" . $table_prefix . "site_tmplvars` tv ";
$sql .= "INNER JOIN $dbase.`" . $table_prefix . "site_tmplvar_templates` tvtpl ON tvtpl.tmplvarid = tv.id ";
$sql .= "LEFT JOIN $dbase.`" . $table_prefix . "site_tmplvar_ec_itemvalues` tvc ON tvc.tmplvarid=tv.id AND tvc.itemid = '$id' ";
$sql .= "LEFT JOIN $dbase.`" . $table_prefix . "site_tmplvar_access` tva ON tva.tmplvarid=tv.id  ";
$sql .= "WHERE tvtpl.templateid = '" . $template . "' AND (1='" . $_SESSION['mgrRole'] . "' OR ISNULL(tva.documentgroup)" . ((!$docgrp) ? "" : " OR tva.documentgroup IN ($docgrp)") . ") ORDER BY tv.rank;";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);

$uniqid = time();
$uniqid = intval($uniqid);

if ($limit > 0) {
	for ($i = 0; $i < $limit; $i++) {
		$tmplvar = "";
		$row = mysql_fetch_assoc($rs);
		if ($row['type'] == 'uploadimage') {			 
			$file = $_FILES["tv" . $row['name']];			
			include_once(MODX_BASE_PATH.'manager/includes/controls/fileUpload.class.php'); 
			$max_size = 1024*1024*10; // the max. size for uploading		
			$fu = new fileUpload($_lang);
			$fu->upload_dir = MODX_BASE_PATH.'assets/images/items/'; 
			$fu->extensions = array(".gif",".jpg",".jpeg",".png",".flv"); 	
			$fu->max_length_filename = 200; 
			$fu->rename_file = true;
			$fu->the_temp_file = $file['tmp_name'];
			$fu->the_file = $file['name'];	
			$fu->http_error = $file['error'];
			$fu->replace = 'y'; 
			$fu->do_filename_check = "y"; 
			$new_name = 'i'.$uniqid++;			
			$doupload = $fu->upload($new_name);
			if ($doupload) {
				$ext = $fu->get_extension($fu->the_file);
				$arr["tv".$row['name']] = 'assets/images/items/'.$new_name.$ext;
				$tmplvar = $arr["tv".$row['name']];
				
			}			
		} elseif ($row['type'] == 'url') {
			$tmplvar = $_POST["tv" . $row['name']];
			if ($_POST["tv" . $row['name'] . '_prefix'] != '--') {
				$tmplvar = str_replace(array (
					"ftp://",
					"http://"
				), "", $tmplvar);
				$tmplvar = $_POST["tv" . $row['name'] . '_prefix'] . $tmplvar;
			}
		} else
			if ($row['type'] == 'file') {
				/* Modified by Timon for use with resource browser */
				$tmplvar = $_POST["tv" . $row['name']];
			} else {
				if (is_array($_POST["tv" . $row['name']])) {
					// handles checkboxes & multiple selects elements
					$feature_insert = array ();
					$lst = $_POST["tv" . $row['name']];
					while (list ($featureValue, $feature_item) = each($lst)) {
						$feature_insert[count($feature_insert)] = $feature_item;
					}
					$tmplvar = implode("||", $feature_insert);
				} else {
					$tmplvar = $_POST["tv" . $row['name']];
				}
			}
		// save value if it was mopdified
		if (in_array($row['name'], $variablesmodified)) {
			if ($tmplvar != $row['default_text'] || empty($tmplvar))
				$tmplvars[$row['name']] = array (
					$row['id'],
					$tmplvar
				);
			else
				$tmplvars[$row['name']] = $row['id'];
		}
	}
}
//End Modification
// get the document, but only if it already exists (d'oh!)
if ($actionToTake != "new") {
	$sql = "SELECT * FROM $dbase.`" . $table_prefix . "site_ec_items` WHERE $dbase.`" . $table_prefix . "site_ec_items`.id = $id;";
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
		// invoke OnBeforeDocFormSave event
		/*
		$modx->invokeEvent("OnBeforeDocFormSave", array (
			"mode" => "new",
			"id" => $id
		));
		*/
		// Deny publishing if not permitted
		if (!$modx->hasPermission('ec_publish_item')) {			
			$published = 0;	
		}
		$publishedon = ($published ? time() : 0);
		$publishedby = ($published ? $modx->getLoginUserID() : 0);
		$sql = "INSERT INTO $dbase.`" . $table_prefix . "site_ec_items` (similaritems,folder_code,date_issue,medium,dealer_cnt,mdealer_cnt,cds,parent,brand_id,pack_id,acc_id,mdealer_price,dealer_price,retail_price,sku,menuindex,pagetitle, longtitle, published, template, new, popular,recommended, byorder, sell, soon, createdby, createdon, editedby, editedon,publishedby, publishedon)
		VALUES('$similaritems','$folder_code','$date_issue','$medium','$dealer_cnt','$mdealer_cnt','$cds','$parent','$brand_id','$pack_id','$acc_id','$mdealer_price','$dealer_price','$retail_price','$sku','$menuindex','$pagetitle','$longtitle','$published','$template','$new','$popular','$recommended','$byorder','$sell','$soon',"."'$modx->getLoginUserID()', '" . time() . "', '" . $modx->getLoginUserID() . "', '" . time() . "', '" . $publishedby . "', '" . $publishedon ."')";
		$rs = mysql_query($sql);
		if (!$rs) {
			$modx->manager->saveFormValues(5002);
			echo "An error occured while attempting to save the new document: " . mysql_error();
			exit;
		}

		if (!$key = mysql_insert_id()) {
			$modx->manager->saveFormValues(5002);
			echo "Couldn't get last insert key!";
			exit;
		}

		// Modified by Raymond for TV - Orig Added by Apodigm for DocVars
		foreach ($tmplvars as $field => $value) {
			if (is_array($value)) {
				$tvId = $value[0];
				$tvVal = $value[1];
				$sql = "INSERT INTO $dbase.`" . $table_prefix . "site_tmplvar_ec_itemvalues` (tmplvarid, itemid, value) VALUES('$tvId','$key', '" . mysql_escape_string($tvVal) . "')";
				$rs = mysql_query($sql);
			}
		}
		//End Modification

		// invoke OnDocFormSave event
		/*
		$modx->invokeEvent("OnDocFormSave", array (
			"mode" => "new",
			"id" => $key
		));
		*/
		// redirect/stay options
		if ($_POST['stay'] != '') {			
			// document
			if ($_POST['mode'] == "5001")
				$a = ($_POST['stay'] == '2') ? "5002&id=$key" : "5001&pid=$parent";
			$header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
		} else {
			$header = "Location: index.php?r=1&id=$id&a=5000&dv=1";
		}
		header($header);

		break;
	case 'edit' :

		// first, get the document's current parent.
		$sql = "SELECT parent FROM $dbase.`" . $table_prefix . "site_ec_items` WHERE id=" . $_REQUEST['id'] . ";";
		$rs = mysql_query($sql);
		if (!$rs) {
			$modx->manager->saveFormValues(27);
			echo "An error occured while attempting to find the document's current parent.";
			exit;
		}
		$row = mysql_fetch_assoc($rs);
		$oldparent = $row['parent'];
		// ok, we got the parent

		$doctype = $row['type'];
		// Set publishedon and publishedby
		$was_published = $modx->db->getValue("SELECT published FROM `{$table_prefix}site_ec_items` WHERE id = '{$id}';");

		// Keep original publish state, if change is not permitted
		if (!$modx->hasPermission('ec_publish_item')) {
			$published = $was_published;
			$pub_date = 'pub_date';
			$unpub_date = 'unpub_date';
		}

		// If it was changed from unpublished to published
		if (!$was_published && $published) {
			$publishedon = time();
			$publishedby = $modx->getLoginUserID();
		}
		elseif ($was_published && !$published) {
			$publishedon = 0;
			$publishedby = 0;
		} else {
			$publishedon = 'publishedon';
			$publishedby = 'publishedby';
		}

		// invoke OnBeforeDocFormSave event
		/*
		$modx->invokeEvent("OnBeforeDocFormSave", array (
			"mode" => "upd",
			"id" => $id
		));
		*/
		// update the document
		$sql = "UPDATE $dbase.`" . $table_prefix . "site_ec_items` SET similaritems = '$similaritems',folder_code = '$folder_code', date_issue='$date_issue', medium='$medium',dealer_cnt='$dealer_cnt',mdealer_cnt='$mdealer_cnt', introtext='$introtext', pagetitle='$pagetitle', longtitle='$longtitle', 
				cds = '$cds',parent='$parent', brand_id='$brand_id',pack_id='$pack_id', published='$published',template='$template', 
				acc_id='$acc_id',sku=$sku,mdealer_price='$mdealer_price',dealer_price='$dealer_price',retail_price='$retail_price',
				popular='$popular', recommended='$recommended', byorder='$byorder', sell='$sell', menuindex='$menuindex', new='$new', soon='$soon', editedby=" . $modx->getLoginUserID() . ", editedon=" . time() . ", publishedon=$publishedon, publishedby=$publishedby  WHERE id=$id;";

		$rs = mysql_query($sql);
		if (!$rs) {
			echo "An error occured while attempting to save the edited document. The generated SQL is: <i> $sql </i>.";
		}
		
		// Modified by Raymond for TV - Orig Added by Apodigm - DocVars
		$sql = "SELECT tmplvarid FROM $dbase.`" . $table_prefix . "site_tmplvar_ec_itemvalues` WHERE itemid=$id";
		$rs = mysql_query($sql);
		$tvIds = array ();
		while (list ($tvId) = mysql_fetch_row($rs)) {
			$tvIds[count($tvIds)] = $tvId;
		}
		foreach ($tmplvars as $field => $value) {
			if (!is_array($value) && isset($_POST['removetvcmd'.$field])) {
				if (in_array($value, $tvIds) && isset($_POST['removetvcmd'.$field])) {
					//delete unused variable
					$sql = "DELETE FROM $dbase.`" . $table_prefix . "site_tmplvar_ec_itemvalues` WHERE tmplvarid=$value AND itemid='$id';";
					$rs = mysql_query($sql);
				}
			} else {
				$tvId = $value[0];
				$tvVal = $value[1];
				if (in_array($tvId, $tvIds)) {
					//update the existing record
					$sql = "UPDATE $dbase.`" . $table_prefix . "site_tmplvar_ec_itemvalues` SET value='" . mysql_escape_string($tvVal) . "' WHERE tmplvarid=$tvId AND itemid='$id';";
					$rs = mysql_query($sql);
				} else {
					//add a new record
					$sql = "INSERT INTO $dbase.`" . $table_prefix . "site_tmplvar_ec_itemvalues` (tmplvarid, itemid,value) VALUES($tvId, '$id', '" . mysql_escape_string($tvVal) . "')";
					$rs = mysql_query($sql);
				}
			}
		}
		//End Modification
		// invoke OnDocFormSave event
		
		$modx->invokeEvent("OnDocFormSave", array (
			"mode" => "upd",
			"id" => $id
		));
		
		
		
		if ($_POST['stay'] != '') {
			$id = $_REQUEST['id'];				
			// document
			$a = ($_POST['stay'] == '2') ? "5002&id=$id" : "5001&pid=$parent";				
			$header = "Location: index.php?a=" . $a . "&r=1&stay=" . $_POST['stay'];
		} else {
			$header = "Location: index.php?r=1&id=$id&a=5000&dv=1";
		}		
		
		header($header);	
		break;
	default :
		header("Location: index.php?a=5000");
		exit;
}

function stripAlias($alias) {
	global $modx;

	// Convert accented characters to their non-accented counterparts. Idea originally from Brett Florio (thanks!) ... expanded list from Textpattern (double-thanks!)
	$replace_array = array(
        '&' => 'and',
        '\'' => '',
        '?ˆ' => 'A',
        '?ˆ' => 'A',
        '??' => 'A',
        '??' => 'A',
        '?‚' => 'A',
        '?‚' => 'A',
        '??' => 'A',
        '??' => 'A',
        '?„' => 'e',
        '?„' => 'A',
        '?…' => 'A',
        '?…' => 'A',
        '?†' => 'e',
        '?†' => 'E',
        '?ˆ' => 'A',
        '?„' => 'A',
        '?‚' => 'A',
        '?‡' => 'C',
        '?‡' => 'C',
        '?†' => 'C',
        '??' => 'E',
        '??' => 'C',
        '??' => 'C',
        '??' => 'C',
        '??' => 'D',
        '??' => 'D',
        '??' => 'E',
        '?‰' => 'E',
        '?‰' => 'E',
        '??' => 'E',
        '??' => 'E',
        '?‹' => 'E',
        '?‹' => 'E',
        '?’' => 'E',
        '??' => 'E',
        '??' => 'E',
        '?”' => 'E',
        '?–' => 'E',
        '??' => 'G',
        '??' => 'G',
        '? ' => 'G',
        '??' => 'G',
        '?¤' => 'H',
        '?¦' => 'H',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'I',
        '?¬' => 'I',
        '?®' => 'I',
        '?°' => 'I',
        '??' => 'J',
        '??' => 'J',
        '?¶' => 'K',
        '??' => 'K',
        '??' => 'K',
        '?»' => 'K',
        '??' => 'K',
        '?‘' => 'N',
        '?‘' => 'N',
        '??' => 'N',
        '?‡' => 'N',
        '?…' => 'N',
        '??' => 'N',
        '?’' => 'O',
        '?’' => 'O',
        '?“' => 'O',
        '?“' => 'O',
        '?”' => 'O',
        '?”' => 'O',
        '?•' => 'O',
        '?•' => 'O',
        '?–' => 'e',
        '?–' => 'e',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '??' => 'O',
        '?’' => 'E',
        '?”' => 'R',
        '??' => 'R',
        '?–' => 'R',
        '??' => 'S',
        '??' => 'S',
        '??' => 'S',
        '??' => 'S',
        '?¤' => 'T',
        '??' => 'T',
        '?¦' => 'T',
        '??' => 'T',
        '?™' => 'U',
        '?™' => 'U',
        '??' => 'U',
        '??' => 'U',
        '?›' => 'U',
        '?›' => 'U',
        '??' => 'e',
        '??' => 'U',
        '??' => 'e',
        '?®' => 'U',
        '?°' => 'U',
        '?¬' => 'U',
        '??' => 'U',
        '??' => 'U',
        '??' => 'W',
        '?¶' => 'Y',
        '??' => 'Y',
        '??' => 'Z',
        '?»' => 'Z',
        '? ' => 'a',
        '??' => 'a',
        '??' => 'a',
        '??' => 'a',
        '?¤' => 'e',
        '?¤' => 'e',
        '??' => 'a',
        '??' => 'a',
        '?…' => 'a',
        '??' => 'a',
        '??' => 'a',
        '?¦' => 'e',
        '?§' => 'c',
        '?‡' => 'c',
        '??' => 'c',
        '?‰' => 'c',
        '?‹' => 'c',
        '??' => 'd',
        '?‘' => 'd',
        '??' => 'e',
        '?©' => 'e',
        '??' => 'e',
        '?«' => 'e',
        '?“' => 'e',
        '?™' => 'e',
        '?›' => 'e',
        '?•' => 'e',
        '?—' => 'e',
        '?’' => 'f',
        '??' => 'g',
        '??' => 'g',
        '??' => 'g',
        '??' => 'g',
        '??' => 'h',
        '?§' => 'h',
        '?¬' => 'i',
        '?­' => 'i',
        '?®' => 'i',
        '??' => 'i',
        '?«' => 'i',
        '?©' => 'i',
        '?­' => 'i',
        '??' => 'i',
        '?±' => 'i',
        '??' => 'j',
        '?µ' => 'j',
        '?·' => 'k',
        '??' => 'k',
        '?‚' => 'l',
        '??' => 'l',
        '??' => 'l',
        '??' => 'l',
        '?ˆ' => 'l',
        '?±' => 'n',
        '?„' => 'n',
        '??' => 'n',
        '?†' => 'n',
        '?‰' => 'n',
        '?‹' => 'n',
        '??' => 'o',
        '??' => 'o',
        '??' => 'o',
        '?µ' => 'o',
        '?¶' => 'e',
        '?¶' => 'e',
        '??' => 'o',
        '??' => 'o',
        '?‘' => 'o',
        '??' => 'o',
        '?“' => 'e',
        '?•' => 'r',
        '?™' => 'r',
        '?—' => 'r',
        '??' => 'u',
        '??' => 'u',
        '?»' => 'u',
        '??' => 'e',
        '?«' => 'u',
        '??' => 'e',
        '??' => 'u',
        '?±' => 'u',
        '?­' => 'u',
        '?©' => 'u',
        '??' => 'u',
        '?µ' => 'w',
        '??' => 'y',
        '?·' => 'y',
        '??' => 'z',
        '??' => 'z',
        '??' => 's',
        '??' => 's',
        '?‘' => 'A',
        '?†' => 'A',
        '?’' => 'B',
        '?“' => 'G',
        '?”' => 'D',
        '?•' => 'E',
        '??' => 'E',
        '?–' => 'Z',
        '?—' => 'I',
        '?‰' => 'I',
        '??' => 'TH',
        '?™' => 'I',
        '??' => 'I',
        '??' => 'I',
        '??' => 'K',
        '?›' => 'L',
        '??' => 'M',
        '??' => 'N',
        '??' => 'KS',
        '??' => 'O',
        '??' => 'O',
        '? ' => 'P',
        '??' => 'R',
        '??' => 'S',
        '?¤' => 'T',
        '??' => 'Y',
        '??' => 'Y',
        '?«' => 'Y',
        '?¦' => 'F',
        '?§' => 'X',
        '??' => 'PS',
        '?©' => 'O',
        '??' => 'O',
        '?±' => 'a',
        '?¬' => 'a',
        '??' => 'b',
        '??' => 'g',
        '??' => 'd',
        '?µ' => 'e',
        '?­' => 'e',
        '?¶' => 'z',
        '?·' => 'i',
        '?®' => 'i',
        '??' => 'th',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'i',
        '??' => 'k',
        '?»' => 'l',
        '??' => 'm',
        '??' => 'n',
        '??' => 'ks',
        '??' => 'o',
        '??' => 'o',
        '?ˆ' => 'p',
        '??' => 'r',
        '??' => 's',
        '?„' => 't',
        '?…' => 'y',
        '??' => 'y',
        '?‹' => 'y',
        '?°' => 'y',
        '?†' => 'f',
        '?‡' => 'x',
        '??' => 'ps',
        '?‰' => 'o',
        '??' => 'o',
	);
    $alias = strtr($alias, $replace_array);
    $alias = strip_tags($alias);
    $alias = preg_replace('/&.+?;/', '', $alias); // kill entities
    $alias = preg_replace('/[^\.%A-Za-z0-9 _-]/', '', $alias);
    $alias = preg_replace('/\s+/', '-', $alias);
    $alias = preg_replace('|-+|', '-', $alias);
    $alias = trim($alias, '-');
    return $alias;
}
?>