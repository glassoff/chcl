<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('ec_settings')) {
	$e->setError(3);
	$e->dumpError();	
}


$pozdr = $_POST['pozdr'];
$ar = array('1' => 'newyear', '2' => 'feb23','3' => 'march8', '4' => 'apr12', '5' => 'may9', '6' => 'sep1',
 '7'=>'nov7');
 
 
$pr = $ar[$pozdr];	

    $sql2 = "select * from $dbase.`".$table_prefix."ec_settings`  WHERE setting_name= '$pr'  ";
	$rs2 = mysql_query($sql2); 
	$row2 = mysql_fetch_assoc($rs2);


$output = $row2['setting_value'];

	$sql1 = "select email, fname, sname, lname,  internalKey from $dbase.`".$table_prefix."web_user_attributes` WHERE 
id=47 or id=1535 or id=1939";
	
	$rs1 = mysql_query($sql1);
    $row1 = mysql_fetch_assoc($rs1);
    
    
  
    
    
    do {
    
    
      $email=$row1['email'];
       $username =$row1['fname'].' '.$row1['sname'].' '.$row1['lname'];		
		$output = str_replace('[+uname+]', $username, $output);	
    

				$headers  = "Content-type: text/html; charset=windows-1251 \r\n";
				$headers .= "From: pochta@cddiski.ru\r\n";

				mail("$email", "С праздником!", "

<br>
$output 

<br>

",  $headers);
  
 } while ($row = mysql_fetch_assoc($rs1));
	 

	  
// empty cache
$header="Location: index.php?a=5200";
header($header);





?>
