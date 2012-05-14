<?php
// initiate a new document parser
if ($_REQUEST['ajaxcmd']) $cmd = $_REQUEST['ajaxcmd']; else $cmd = '';

switch ($cmd) {	
	
	case 'getitemlist':{
		if ($_REQUEST['parent']) $parent = intval($_REQUEST['parent']);
		$sql  =  "SELECT * FROM ".$modx->getFullTableName("site_ec_items")." ";
		$sql .=  "WHERE parent = $parent AND published='1' ORDER BY menuindex,pagetitle ";
		$rs = mysql_query($sql);
		$result_size = mysql_num_rows($rs);
		$output_begin = '<select id="itemselectorlist" ondblclick="addItemTo(this)" multiple style="height:250px;width:400px;">';		
		$output = '';
		if ($result_size > 0) {
			while ($row = mysql_fetch_assoc($rs)) {
				$output .='<option value="'.$row['id'].'">'.$row['pagetitle'].' ('.$row['acc_id'].')</option>';
			}
			echo $output_begin.$output.'</select>';				
		} else {
			echo $output_begin.'<option value="0">'.$_lang['no_items'].'</option></select>';
		}
		
	};break;
	
	case '':{
		
	};break;
	
}
?>
