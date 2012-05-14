<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (isset($_POST['sel_items']) && sizeof($_POST['sel_items'])>0) {
	
	$sel_ids = $_POST['sel_items'];
	$sel_ids = implode(',', $sel_ids);
	
	switch (intval($_REQUEST['cmd'])) {
		case '5015':
			//group delete	
			if(!$modx->hasPermission('ec_delete_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET deleted=1, deletedby=".$modx->getLoginUserID().", deletedon=$deltime WHERE id IN ($sel_ids) AND deleted=0;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to set the document to deleted status...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}			
		break;	
		case '5016':
			//group undelete
			if(!$modx->hasPermission('ec_delete_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}	
			$deltime = time();
			
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET deleted=0, deletedby=0, deletedon=0 WHERE id IN ($sel_ids) AND deleted=1;";
			
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to set the document to deleted status...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}			
		break;	
		case '5017':
			//publish	
			if(!$modx->hasPermission('ec_publish_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET published=1,  editedby=".$modx->getLoginUserID().", editedon=".time().", publishedby=".$modx->getLoginUserID().", publishedon=".time()." WHERE id IN ($sel_ids) AND published=0;";
			$rs = mysql_query($sql);
			
			if(!$rs) {
				echo "Something went wrong while trying to set the document to deleted status...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}			
		break;	
		case '5018':
			//unpublish	
			if(!$modx->hasPermission('ec_publish_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET published=0, editedby=".$modx->getLoginUserID().", editedon=".time().", publishedby=0, publishedon=0 WHERE id IN ($sel_ids) AND published=1;";
			$rs = mysql_query($sql);
			
			if(!$rs) {
				echo "Something went wrong while trying to set the document to deleted status...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}			
		break;	
		case '5019':
			//remove	
			if(!$modx->hasPermission('ec_remove_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "DELETE FROM $dbase.`".$table_prefix."site_ec_items` WHERE deleted=1 AND id IN ($sel_ids);";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to set the document to deleted status...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}			
		break;	
		case '5020':
			
			//sort	
			if (!isset($_POST['menuindexes']) || !count($_POST['menuindexes'])) {
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}			
			if(!$modx->hasPermission('ec_sort_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			
			foreach ($_POST['sel_items'] as $k => $v) {				
				$item_id = intval(mysql_escape_string($v));
				$menuindex = intval(mysql_escape_string($_POST['menuindexes'][$k]));
				
				$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET menuindex=$menuindex, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id = $item_id";
				$rs = mysql_query($sql);
				//echo $sql;				
			}
			if(!$rs) {
				echo "Something went wrong while trying to set the document to deleted status...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				//header($header);
			}			
		break;	
		
		case '5029':
			
			//sort	
			if (!isset($_POST['parents']) || !count($_POST['parents'])) {
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}			
			
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			
			$sql = '';
			$i = 0;
			
			$tv_id = 56;
			$folder_codes = array();
			$sql = "SELECT contentid,value FROM ".$modx->getFullTableName('site_tmplvar_contentvalues');
			$sql.= " WHERE tmplvarid=$tv_id";					
			$rs = mysql_query($sql);
			while ($row = mysql_fetch_assoc($rs)) {				    
				$folder_codes[$row['contentid']] = trim($row['value']);
			}        				
			foreach ($_POST['sel_items'] as $k => $v) {				
				$item_id = intval(mysql_escape_string($v));
				$parent = intval(mysql_escape_string($_POST['parents'][$k]));	
				$folder_code = isset($folder_codes[$parent]) ? $folder_codes[$parent] : '';			
				$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET folder_code = '$folder_code', parent=$parent, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id = $item_id;";
				$rs = mysql_query($sql);															
			}
			//echo $sql;
			//exit;
			if (!empty($sql)) $rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong ...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				//header($header);
			}			
		break;	
		
		case '5030':
			//Popular
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET popular=1, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND popular=0;";
			
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to set the document to deleted status...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}			
		break;	
		case '5031':
			//Not popular	
			//Popular
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET popular=0, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND popular=1;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		case '5032':
			//Recommended	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET recommended=1, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND recommended=0;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		case '5033':
			//Not Recommended	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET recommended=0, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND recommended=1;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		case '5034':
			//By order	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET byorder=1, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND byorder=0;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		case '5035':
			//By order	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET byorder=0, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND byorder=1;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		
		case '5036':
			//New	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET new=1, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND new=0;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		
		case '5037':
			// Not new	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET new=0, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND new=1;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		
		case '5038':
			// Sell	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET sell=1, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND sell=0;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		
		case '5039':
			// Not sell	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET sell=0, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND sell=1;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		
		case '5040':
			// Soon	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET soon=1, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND soon=0;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		
		case '5040':
			// Not Soon	
			if(!$modx->hasPermission('ec_edit_item')) {	
				$e->setError(3);
				$e->dumpError();	
			}
			$deltime = time();
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET soon=0, editedby=".$modx->getLoginUserID().", editedon=".time()." WHERE id IN ($sel_ids) AND soon=1;";
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to edit the document...";
				exit;
			} else {
				//event place
				$header="Location: index.php?r=1&a=5000";	
				header($header);
			}		
		break;	
		
	}
} 

$header="Location: index.php?r=1&a=5000";	
header($header);	
?>
