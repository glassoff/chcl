
<?php require_once('conn.php');  

		
				
mysql_select_db($database_fp, $fp);
mysql_query("set CHARACTER SET cp1251");



		
		$sql = 'SELECT * FROM modx_site_ec_regions order by listindex, name';
		$rs = mysql_query($sql);
		$lines = array();
		$lines[] = '<select name="region">
<option value="" selected >Выберите регион:</option>



';	
		if ($rs && mysql_num_rows($rs)>0) {
			while ($row = mysql_fetch_assoc($rs)) {
				
				 $lines[] = '<option value="'.$row['name'].'"  >'.$row['name'].'</option>';					 
					
				
		}
		$lines[] = '</select>';	
				
		$a= implode("\n", $lines);
		print $a;
}



?>	