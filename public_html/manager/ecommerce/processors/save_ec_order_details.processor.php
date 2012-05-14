<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!$modx->hasPermission('ec_manage_orders')) {
	$e->setError(3);
	$e->dumpError();
}


$id = $order_id = mysql_escape_string($_REQUEST['order_id']);
$town = $_POST['town'];	
$region = $_POST['region'];	
$street = $_POST['street'];	
$house = $_POST['house'];	
$korpus = $_POST['korpus'];	
$kvartira = $_POST['kvartira'];	
$postcode1 = $_POST['postcode1'];	
$phone = $_POST['phone'];	
$email = $_POST['email'];	
		$metro = $_POST['metro'];
		 
		
		$sql = "UPDATE $dbase.`".$table_prefix."site_ec_orders` SET customer_postcode1='$postcode1', customer_region='$region', customer_state='$town',
		customer_street='$street', customer_dom='$house', customer_korpus='$korpus', customer_kvartira='$kvartira', customer_phone='$phone', customer_email='$email', customer_metro='$metro'
		 WHERE id = '$order_id' LIMIT 1";
		
		$rs = mysql_query($sql);
	
		if ($rs) {			
			include_once $modx->config['base_path']."assets/snippets/ecart/ecart.inc.php";
			$ec = new eCart();
			$ec->init();
			
		}
		header("Location: index.php?a=5501&id=$order_id");
		




?>
