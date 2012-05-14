<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('ec_settings')) {
	$e->setError(3);
	$e->dumpError();	
}

$date24=time()-86400;

	
	
	// reg_date - когда зарегились 
	
	$sql1 = "select email, internalKey, fname, lname, sname from $dbase.`".$table_prefix."web_user_attributes` WHERE 
reg_date < $date24  and internalKey NOT IN
	
	( Select customer_id  From $dbase.`".$table_prefix."site_ec_orders`) and  internalKey NOT IN ( Select user_id  From $dbase.`".$table_prefix."user_nobuy`) ";
	
	$rs1 = mysql_query($sql1);
    $row1 = mysql_fetch_assoc($rs1);	
    
    
   

    $sql2 = "select * from $dbase.`".$table_prefix."ec_settings`  WHERE setting_name='no_buy24'";
	$rs2 = mysql_query($sql2);
	$row2 = mysql_fetch_assoc($rs2);
	
	  
	
	
    $sql3 = "select * from $dbase.`".$table_prefix."ec_settings`  WHERE setting_name='ec_order_admin_email'";
	$rs3 = mysql_query($sql3);
	$row3 = mysql_fetch_assoc($rs3);
	
	 $email_sender=$row3['setting_value']; 
	
	 
    do {

 $message=$row2['setting_value'];
 
    $email=$row1['email'];
    $key = $row1['internalKey'];
    
    $username =$row1['fname'].' '.$row1['sname'].' '.$row1['lname'];		
		$message = str_replace('[+uname+]', $username, $message);	 
	
    		
				$headers  = "Content-type: text/html; charset=windows-1251 \r\n";
				$headers .= "From: ".$email_sender."\r\n";

				mail("$email", "От Администрации сайта CDDISKI", "

<br>
$message
",  $headers);
	
	
	if ($key!=0) {
	 $sql4="INSERT INTO $dbase.`".$table_prefix."user_nobuy` VALUES ('$key') ";
	$rs4 = mysql_query($sql4);
	}
	
	  } while ($row1 = mysql_fetch_assoc($rs1));
	  
	  
	  
	  
	  
	  //для не подтвердивших
	  
	  
	   $sql13 = "select * from $dbase.`".$table_prefix."ec_settings`  WHERE setting_name='email_sender'";
	$rs13 = mysql_query($sql13);
	$row13 = mysql_fetch_assoc($rs13);
	
	 $email_sender=$row13['setting_value']; 

	$sql5 = "select modx_site_ec_orders.id, internalKey, customer_email, customer_fname, customer_sname, customer_lname, confirm_key, customer_phone, customer_kvartira,
	customer_korpus, customer_dom, customer_street, customer_metro, customer_postcode1, customer_region, customer_state, customer_country, discount, order_date, quantity, delivery_type,
	amount, delivery_amount, payment_type
	
 from  $dbase.`".$table_prefix."site_ec_orders`, $dbase.`".$table_prefix."web_user_attributes`  WHERE 
order_date < $date24  and internalKey = customer_id and confirmed =0
 and 

customer_id NOT IN ( Select user_id  From $dbase.`".$table_prefix."user_noconfirm`, $dbase.`".$table_prefix."site_ec_orders` where modx_user_noconfirm.order_id=modx_site_ec_orders.id) 

";
	
	$rs5 = mysql_query($sql5);
    $row5 = mysql_fetch_assoc($rs5); 
    

					
		if ($row5 > 0) {
			
			
    
 $sql6 = "select * from $dbase.`".$table_prefix."ec_settings`  WHERE setting_name='no_confirm24'";
	$rs6 = mysql_query($sql6);
	$row6 = mysql_fetch_assoc($rs6); 
	
	
	
	
	
	$output=$row6['setting_value'];
	 

	  
	 
	
	
do {
  
  
	  
    $email2=$row5['customer_email'];
    $key = $row5['internalKey'];
    $order_id = $row5['id'];
    
   
    $username =$row5['customer_fname'].' '.$row5['customer_sname'].' '.$row5['customer_lname'];		
		$output = str_replace('[+uname+]', $username, $output);	 
	
$confirm_key = $row5['confirm_key'];		
		$confirm_link = 'http://www.cddiski.ru/cabinet/confirmorder?user_order_id='.$order_id.'&confirm='.$confirm_key ;		
		$output = str_replace('[+confirm_link+]', $confirm_link, $output);
		
	$output = str_replace('[+id+]', $order_id, $output);
	$order_date=datetime($row5['order_date']);
		$output = str_replace('[+order_date+]', $order_date, $output);
		
		$quantity=$row5['quantity'];
		$output = str_replace('[+quantity+]', $quantity, $output);
    
    $discount=$row5['discount'];
    $output = str_replace('[+discount+]', $discount, $output);
    
    $customer_country=$row5['customer_country'];
     $output = str_replace('[+customer_country+]', $customer_country, $output);
     
     $customer_region=$row5['customer_region'];
     $output = str_replace('[+customer_region+]', $customer_region, $output);
      
      $customer_state=$row5['customer_state'];
     $output = str_replace('[+customer_state+]', $customer_state, $output);
     
     $customer_postcode1=$row5['customer_postcode1'];
     $output = str_replace('[+customer_postcode1+]', $customer_postcode1, $output);
      
      $customer_metro=$row5['customer_metro'];
     $output = str_replace('[+customer_metro+]', $customer_metro, $output);
     
     $customer_street=$row5['customer_street'];
     $output = str_replace('[+customer_street+]', $customer_street, $output);
     
     $customer_dom=$row5['customer_dom'];
     $output = str_replace('[+customer_dom+]', $customer_dom, $output);
     
     $customer_korpus=$row5['customer_korpus'];
     $output = str_replace('[+customer_korpus+]', $customer_korpus, $output);
     
     $customer_kvartira=$row5['customer_kvartira'];
     $output = str_replace('[+customer_kvartira+]', $customer_kvartira, $output);
     
     $customer_phone=$row5['customer_phone'];
     $output = str_replace('[+customer_phone+]', $customer_phone, $output);
     
     $amount=$row5['amount'];
     $output = str_replace('[+amount+]', $amount, $output);
     
     	$order_items = getOrderItemsInfo($order_id);
		$items_list = "<ul>";
		
		foreach($order_items as $k => $item) {
			$price = money1($item['price']).' руб.';
			$quantity = quantity($item['quantity']).' руб.';
			$items_list .= "<li><a target=\"_blank\" href=\"http://www.cddiski.ru/catalog/?id=".$item[item_id]."\">".$item['itemtitle']."</a> - ".$price." - ".$quantity."</li>";
		}
		
		$items_list .= "</ul>";		
		$output = str_replace('[+orderitems+]',$items_list,$output);
		
		$order = getOrderInfo($order_id);
		
	 $output = str_replace('[+bank_account+]', '', $output);

		if ($order['delivery_type'] == 'outsea') {
			$order['payment_name'] = 'Вам будет отправлена информация на е-майл о способы оплаты';
			$order['delivery_amount'] = 'Вам будет отправлена информация на е-майл о точной сумме доставки';							
		}
    
    		foreach($order as $k => $v) {
			$output = str_replace('[+'.$k.'+]', $v, $output);
		} 	
    		
				$headers  = "Content-type: text/html; charset=windows-1251 \r\n";
				$headers .= "From: ".$email_sender."\r\n";

				mail("$email2", "О подтверждении заказа CDDISKI", "

<br>
$output 

<br><br>


",  $headers);
  
  if ($key!=0) {
   $sql7="INSERT INTO $dbase.`".$table_prefix."user_noconfirm` VALUES ('$key', '$order_id') ";
	$rs7 = mysql_query($sql7);
}	
  
   } while ($row5 = mysql_fetch_assoc($rs5));
	 
	 
	 
}	


$time = time();
    $sql_send = "UPDATE $dbase.`".$table_prefix."ec_settings` SET setting_value='$time' Where setting_name='timesend_nobuy'";
	$rs_send = mysql_query($sql_send);

	  
	  
	  
// empty cache
$header="Location: index.php?a=5200";
header($header);


function getOrderItemsInfo($id) {
		global $modx;
		$sql = "SELECT oi.*,i.pagetitle as itemtitle,i.parent as itemparent ";
		$sql.= "FROM ".$modx->getFullTableName("site_ec_order_items")." oi ";
	    $sql.= "INNER JOIN ". $modx->getFullTableName("site_ec_items")." i ON oi.item_id = i.id ";  
		$sql.= "WHERE oi.order_id = '{$id}';";
	    $result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);					
		if ($numResults > 0) {
			for($i=0;$i<$numResults;$i++)  {
				$rows[] = $modx->fetchRow($result);	
			}
			return $rows;
		} else return false;
	}

function getOrderInfo($id) {
		global $modx;
		$ar = array('curer_v_mkad' => 'Курьер по москве в пределах МКАД', 'vstrecha' => 'Встреча в метро','samovivoz' => 'Самовывоз', 'curer_za_mkad' => 'Курьер по москве за пределы МКАД', '1class' => 'Отправление "1 класса"', 'basic' => 'Наземная почта', 'outsea'=>'Доставка в Ближнее и Дальнее зарубежье');
		
		$sql = "SELECT o.*,pt.name as payment_type_name,os.name as status_name FROM ".$modx->getFullTableName("site_ec_orders")." o ";
		$sql.= "LEFT JOIN ". $modx->getFullTableName("site_ec_payment_methods")." pt ON o.payment_type = pt.id "; 
		$sql.= "LEFT JOIN ". $modx->getFullTableName("ec_order_status")." os ON o.status = os.id "; 
	    $sql.= "WHERE o.id = '{$id}' LIMIT 1;";
		$result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);					
		if ($numResults == 1) {
			$row = $modx->fetchRow($result);
			$row['items'] = getOrderItemsInfo($row['id']);
			$row['delivery_name'] = $ar[$row['delivery_type']];
			return $row;
		}
		else return false;
	}


?>
