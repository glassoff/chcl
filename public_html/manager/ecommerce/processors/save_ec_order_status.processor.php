<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if (!$modx->hasPermission('ec_manage_orders')) {
	$e->setError(3);
	$e->dumpError();
}


$id = $order_id = mysql_escape_string($_REQUEST['order_id']);
// get document groups for current user
$sql = " SELECT so.*, IF(paid = 1,'".$_lang['ec_order_paid']."','".$_lang['ec_order_notpaid']."') as 'paid_status'," .
       " os.name as status_name, pt.name as payment_m, so.id as order_id,pt.auto as isauto ".
	   " FROM ".$modx->getFullTableName("site_ec_orders") . " so ".  
	   " INNER JOIN" .$modx->getFullTableName("ec_order_status"). " os ON  os.id = so.status ".
	   " INNER JOIN" .$modx->getFullTableName("site_ec_payment_methods"). " pt ON  so.payment_type = pt.id ". 	    
       " WHERE so.id = '$id'";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);
if($limit>1) {
    echo " Internal System Error...<p>";
    print "More results returned than expected. <p>Aborting.";
    exit;
}

$order = mysql_fetch_assoc($rs);
$cmd = mysql_escape_string(@$_POST['cmd']);

switch ($cmd) {
	
	case 'bonus': {
		
		$bonus_state = intval($_REQUEST['bonus_state']);		
		$order_id = mysql_escape_string($_REQUEST['order_id']);		
		
		$sql = " SELECT * FROM ".$modx->getFullTableName("site_ec_orders") . " WHERE id = '$id' LIMIT 1";
		$rs = mysql_query($sql);
		
		$limit = mysql_num_rows($rs);
		
		if ($limit == 1) {
		    $order = mysql_fetch_assoc($rs);
		} elseif($limit==0){
		    echo " Internal System Error...<p>";
		    print "More results returned than expected. <p>Aborting.";
		    exit;
		}	
		
		if ($bonus_state == 0 && $order['bonus_state'] == 1) {	
			
			$sql = " SELECT max(bonus) as max_bonus FROM ".$modx->getFullTableName("site_ec_orders") . " WHERE customer_id = '$order[customer_id]' AND id <> '$order_id' AND bonus_state='1'";
			
			$rs = mysql_query($sql);
			$limit = mysql_num_rows($rs);
						
			if ($limit == 1)  {
				$order1 = mysql_fetch_assoc($rs);
				if (!empty($order1['max_bonus'])) {
					$old_bonus = $order1['max_bonus'];
				}
				$old_bonus = $old_bonus = 0;
			} else $old_bonus = 0;  			
					
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_orders` SET bonus_state='0' WHERE id = '$order_id' LIMIT 1";
			$rs = mysql_query($sql);
			$sql = "UPDATE $dbase.`".$table_prefix."web_user_attributes` SET bonus='$old_bonus' WHERE internalKey = '$order[customer_id]'  LIMIT 1";
			$rs = mysql_query($sql);
			
		} elseif ($bonus_state == 1) {
			
			$sql = "UPDATE $dbase.`".$table_prefix."site_ec_orders` SET bonus_state='1' WHERE id = '$order_id' LIMIT 1";			
			
			$rs = mysql_query($sql);
			$sql = "UPDATE $dbase.`".$table_prefix."web_user_attributes` SET bonus='$order[bonus]' WHERE bonus <= '$order[bonus]' AND internalKey = '$order[customer_id]'  LIMIT 1";
			
			$rs = mysql_query($sql);
			
		}			
		header("Location: index.php?a=5501&id=$order_id");		
		
	};break;
	
	case 'paid': {
		
		$paid = intval($_POST['order_paid']);		
		$order_id = mysql_escape_string($_POST['order_id']);		
		$customer_id = intval($_POST['customer_id']);	
		$paidin = isset($_POST['paidin']) ? mysql_escape_string($_POST['paidin']) : '';	
		$notifyuser = intval($_POST['notifyuser']);	
		
		if (isset($_POST['paidin'])) 
		$sql = "UPDATE $dbase.`".$table_prefix."site_ec_orders` SET paid='$paid', paidin='$paidin' WHERE id = '$order_id' LIMIT 1";
		else 
		$sql = "UPDATE $dbase.`".$table_prefix."site_ec_orders` SET paid='$paid' WHERE id = '$order_id' LIMIT 1";
		
		$rs = mysql_query($sql);
		
			if ($paid==1) {
					$sql = "SELECT bonus, discount, customer_id FROM ".$modx->getFullTableName("site_ec_orders")."  WHERE  id='$order_id' LIMIT 1";
		$rs1 = $modx->dbQuery($sql);	
		$row = mysql_fetch_assoc($rs1);
		if ($row['bonus'] > $row['discount'] )
		{ 
		$customer_id= $row['customer_id'];
		$bonus= $row['bonus'];
		$sql1 = "UPDATE ".$modx->getFullTableName("web_user_attributes")." SET bonus ='$bonus'  WHERE internalKey='$customer_id' LIMIT 1";
		$rs1 = $modx->dbQuery($sql1);	
		}
			}	
		
		
		
		$mailsent = 0;
		if ($rs) {			
			include_once $modx->config['base_path']."assets/snippets/ecart/ecart.inc.php";
			$ec = new eCart();
			$ec->init();
			$bonus = 0;			
			if ($paid == 1) {		
			
			
				if ($notifyuser == 1) $ec->sendPaymentDoneMessage($customer_id,$order_id);
				$mailsent = 2;			
			}
		}
		header("Location: index.php?a=5501&id=$order_id&mailsent=$mailsent");
		
	};break;
	
		case 'confirmed': {
		
		$confirmed = intval($_POST['order_confirmed']);		
		$order_id = mysql_escape_string($_POST['order_id']);		
		$customer_id = intval($_POST['customer_id']);	
		
		$notifyuser = intval($_POST['notifyuser']);	
		
		 
		
		$sql = "UPDATE $dbase.`".$table_prefix."site_ec_orders` SET confirmed='$confirmed' WHERE id = '$order_id' LIMIT 1";
		
		$rs = mysql_query($sql);
		$mailsent = 0;
		if ($rs) {			
			include_once $modx->config['base_path']."assets/snippets/ecart/ecart.inc.php";
			$ec = new eCart();
			$ec->init();
			$bonus = 0;			
			if ($paid == 1) {		
				if ($notifyuser == 1) $ec->sendPaymentDoneMessage($customer_id,$order_id);
				$mailsent = 2;			
			}
		}
		header("Location: index.php?a=5501&id=$order_id&mailsent=$mailsent");
		
	};break;

	case 'status':{
		
		
		
		$status = intval($_POST['order_status']);			
		$order_id = mysql_escape_string($_POST['order_id']);		
		$customer_id = intval($_POST['customer_id']);	
		$admin_comments = mysql_escape_string($_POST['admin_comments']);
		$notifyuser = intval($_POST['notifyuser']);		
		$sql = "UPDATE $dbase.`".$table_prefix."site_ec_orders` SET status=$status,admin_comments = '$admin_comments' WHERE id = '$order_id' LIMIT 1";
		$rs = mysql_query($sql);
		$mailsent = 0;
		if ($rs) {			
			include_once $modx->config['base_path']."assets/snippets/ecart/ecart.inc.php";
			$ec = new eCart();
			$ec->init();
			$bonus = 0;
			if ($order_id == $ec_settings['order_complate_status'] && $notifyuser == 1) {	
				//$order = $ec->getOrderInfo($order_id);
				$sql = "UPDATE $dbase.`".$table_prefix."web_user_attributes` "; 
				$sql.= "SET bonus=$bonus";
				$sql.= "WHERE id = $customer_id LIMIT 1 ";
				//$rs = mysql_query($sql);
				if ($rs) {	
					if ($notifyuser == 1) $ec->sendOrderDoneMessage($customer_id,$order_id);
					$mailsent = 1;
				}		
			} 	
			
	$sql = "SELECT * FROM ".$modx->getFullTableName('site_ec_orders')." WHERE id = '$order_id' LIMIT 1";	
$rs = mysql_query($sql);
$order = mysql_fetch_assoc($rs);

if ($order['informcust'] == 1 && $status == 6  ) {
					include_once $modx->config['base_path']."assets/snippets/ecart/ecart.inc.php";
					$ec = new eCart();
					$ec->init();
					
					
			$sql5 = "SELECT * FROM ".$modx->getFullTableName('ec_settings')." WHERE setting_name = 'ec_email_order_done_mgs' LIMIT 1";					
			$rt = mysql_query($sql5);	
			$text = mysql_fetch_assoc($rt);
			$message = $text['setting_value'];
		
		
		
		$cust_name = $order['customer_fname'].' '.$order['customer_sname'].' '.$order['customer_lname'];				
		$message = str_replace('[+uname+]', $cust_name, $message);
		
		
		$order['order_date'] = datetime($order['order_date']);	
		$message = str_replace('[+order_fdate+]', $order['order_date'], $message);
		$message = str_replace('[+id+]', $order_id, $message);
	
		$email = $order['customer_email'];
		
$headers  = "Content-type: text/html; charset=windows-1251 \r\n";
$headers .= "From: orders@cddiski.ru\r\n";

mail("$email", "Ваш заказ в  Интернет-магазине CDDISKI отправлен", "$message",  $headers);
		}
		
		}
	

		header("Location: index.php?a=5501&id=$order_id&mailsent=$mailsent");
		
		
		
	};break;	
	
}


?>
