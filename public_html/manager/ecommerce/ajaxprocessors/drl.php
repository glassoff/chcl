<?php
	function drl_add() {
		global $modx;


		$name = addslashes(trim(ajax_encode($_POST['name'])));
		$desc = addslashes(trim(ajax_encode($_POST['description'])));
		$active = intval($_POST['active']);
		
		if (strlen($name) < 1)
			die('ERROR: Please supply a name.');		  	
		
		$qry = 'insert into ' . $modx->getFullTableName("site_ec_discounts") .' (name, description,active) values ("' . $name . '","' . $desc . '"," . $active . ")';
		mysql_query($qry);
	}		

	function drl_delete() {
		global $modx;

		$id = intval($_POST['listid']);
		
		$qry = 'delete from ' . $modx->getFullTableName("site_ec_discounts") .' where id=' . $id;
		mysql_query($qry);
	}		

	function drl_gridload() {
		global $modx, $_lang, $theme;
		
		$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_discounts") .' order by name';
		$ds = mysql_query($sql) or die ('ERROR: ' . mysql_error());
		$grd = new DataGrid('',$ds, $number_of_results);
		$grd->noRecordMsg = $_lang['no_records_found'];
		$grd->cssClass='grid';
		$grd->columnHeaderClass='gridHeader';
		$grd->itemClass='gridItem';
		$grd->altItemClass='gridAltItem';
		$grd->fields='num, name, description,active';
		$grd->columns= $_lang['ec_disc_num'] . ', '.$_lang['ec_disc_name'] . ', ' . $_lang['ec_disc_desc'] . ' ,'. $_lang['ec_disc_actions'] ;
		$grd->colWidths='100,300,50,50,50,50';
		$grd->colAligns='left,left,left,left';
		$grd->colTypes  ='template:[+num+],,,';
		$grd->colTypes .='php:if ($row["active"] == 1) echo $_lang["yes"]; else echo $_lang["no"];,'; 				
		$grd->colTypes .= 'template:<a href="#" title="'.$_lang['ec_disc_btn_edit'].'" onClick="drl_properties([+id+])"><img src="media/style/'.$theme.'/images/icons/save.gif"></a> &nbsp;';			   
		$grd->colTypes .= '<a href="#" title="'.$_lang['ec_disc_btn_entries_edit'].'" onClick="drl_entries([+id+])"><img src="media/style/'.$theme.'/images/icons/comment.gif"></a> &nbsp;';			   
		$grd->colTypes .= '<a href="#" title="'.$_lang['ec_disc_btn_delete'].'" onClick="drl_delete([+id+])"><img src="media/style/'.$theme.'/images/icons/delete.gif"></a>';			   
		
		echo $grd->render();
	}

	function drl_properties() {
		global $modx, $_lang;
		
		$listid = intval($_POST['listid']);
		
		$qry = 'select * from ' . $modx->getFullTableName("site_ec_discounts") .' where id=' . $listid;
		$rows = db_getrows($qry);
		if (sizeof($rows) < 1) die('ERROR: ' . mysql_error());

		$lines = array();
		$lines[] = '<div id="title" style="font-weight: bold; font-size: 14px;">'.$_lang["ec_disc_properties"].'</div><br />';
		$lines[] = '<table border=0 cellpadding=5><tbody>';
		$lines[] = '<tr><td><label  for="listname">' . $_lang['ec_disc_name'] . ':</label></td>';
		$lines[] = '<td width=20>&nbsp;</td>';
		$lines[] = '<td><input id="listname" name="listname" type="text" value="' . $rows[0]['name'] . '" length=20 /></td></tr>';
		$lines[] = '<tr><td><label  for="listdesc">' . $_lang['ec_disc_desc'] . ':</label></td>';
		$lines[] = '<td width=20>&nbsp;</td>';
		$lines[] = '<td><input id="listdesc" name="listdesc" type="text" value="' . $rows[0]['description'] . '" length=20 /></td></tr>';
		$lines[] = '</tbody></table>';
		$lines[] = '<input style="color: green" type="button" onclick="drl_update(' . $listid . ')" name="Save" value="' . $_lang['ec_disc_save_changes'] . '">';
		$lines[] = '<input type="button" style="color: green" name="Cancel" onclick="hideBox()" value="' . $_lang['ec_disc_cancel'] . '">';
		echo implode("\n", $lines);
	}

	function drl_update() {
		global $modx;

		$name = addslashes(trim(ajax_encode($_POST['name'])));
		$desc = addslashes(trim(ajax_encode($_POST['desc'])));
		$id = intval($_POST['id']);
		
		if (strlen($name) < 1)
		  die('ERROR: Please supply a valid name.');
		
		$qry = 'update ' . $modx->getFullTableName("site_ec_discounts") .' set name="' . $name . '",description="' . $desc . '" where id=' . $id;
		mysql_query($qry);
		drl_gridload();
	}

	function drl_entries() {
		global $modx, $_lang,$theme;
		
		$listid = intval($_POST['listid']);

		$lines = array();
		$qry = 'select * from ' . $modx->getFullTableName("site_ec_discounts") .' where id=' . $listid;
		$rows = db_getrows($qry);
		if (sizeof($rows) < 1) die('ERROR: ' . mysql_error());
		$didc_name = $rows[0]['name'];
		$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_discount_entries") .' where list_id=' . $listid . ' order by minqty';
		$ds = mysql_query($sql) or die ('ERROR: ' . mysql_error());
		$grd = new DataGrid('', $ds, $number_of_results);
		$grd->noRecordMsg = $_lang['no_records_found'];
		$grd->cssClass='grid';
		$grd->columnHeaderClass='gridHeader';
		$grd->itemClass='gridItem';
		$grd->altItemClass='gridAltItem';
		$grd->fields='num,minqty, discount';
		$grd->columns= $_lang['ec_disc_num']. ',' . $_lang['ec_disc_en_minqty'] . ', ' . $_lang['ec_disc_en_discount'] . ',' . $_lang['ec_disc_btn_delete'];
		$grd->colWidths='10, 50, 60, 30';
		$grd->colAligns='left,left,left,center';
		$grd->colTypes ='template:[+num+],,,template:<a href="#" title="'.$_lang['ec_disc_btn_delete'].'" onClick="drl_entries_delete([+id+])"><img src="media/style/'.$theme.'/images/icons/delete.gif"></a> &nbsp;';			   
		
		$lines[] = '<div id="title" style="font-weight: bold; font-size: 14px;">'.$_lang["ec_disc_btn_entries"].' - '.$didc_name.'</div><br />';
		$lines[] = $grd->render();
		$lines[] = '<form align="right" style="margin-left:33px;"><input type=text id="drl_minqty" size=8 id=minqty>&nbsp;&nbsp;';
		$lines[] = '<input type=text id="drl_discrate" size=8>&nbsp;&nbsp;';
		$lines[] = '<input type="button" style="color: green" onclick="drl_entries_add(' . $listid . ')" value="' . $_lang['ec_disc_btn_add'] . '" name="drl_e_add"></form>';
		echo implode("\n", $lines);
	}

	function drl_entries_add() {
		global $modx;
		
		$listid = intval($_POST['listid']);
		$minqty = floatval($_POST['minqty']);
		$discount = floatval($_POST['discount']);

		$qry = 'insert into ' . $modx->getFullTableName("site_ec_discount_entries") .' (list_id, minqty, discount) values (' .
				$listid . ',' . $minqty . ',' . $discount . ')';
		mysql_query($qry);
		drl_entries();
	}		

	function drl_entries_delete() {
		global $modx;
		
		$id = intval($_POST['id']);
		
		mysql_query('delete from ' . $modx->getFullTableName("site_ec_discount_entries") .' where id=' . $id);
		drl_entries();
	}		

	$oktxns[] = 'drl_add';
	$oktxns[] = 'drl_gridload';
	$oktxns[] = 'drl_delete';
	$oktxns[] = 'drl_properties';
	$oktxns[] = 'drl_update';
	$oktxns[] = 'drl_entries';
	$oktxns[] = 'drl_entries_add';
	$oktxns[] = 'drl_entries_delete';
?>