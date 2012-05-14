<?php
// initiate a new document parser
if (isset($_GET['rid'])) {
	$rid = mysql_escape_string($_GET['rid']);
	$sql = "SELECT * FROM " . $modx->getFullTableName("site_ec_cities") . " WHERE rid= '$rid' order by listindex, name";
	$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
	$lines = array();		
	if ($rs && mysql_num_rows($rs)>0) {
		$lines[] = '<select class="reg_field" name="state">';
		while ($row = mysql_fetch_assoc($rs)) {			
			$lines[] = '<option value="'.$row['id'].'"  >'.$row['name'].'</option>';
		}		
		$lines[] = '</select>';	
	} else {
		$lines[] = '<b>'.$_lang['ec_select_region'].'</b>';
	}	
	echo implode("\n", $lines);
	exit;
}
?>