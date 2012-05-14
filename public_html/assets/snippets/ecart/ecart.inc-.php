<?php
/*
::::::::::::::::::::::::::::::::::::::::
 Snippet name: Wayfinder
 Short Desc: builds site navigation
 Version: 2.0
 Authors: 
	Kyle Jaebker (muddydogpaws.com)
	Ryan Thrash (vertexworks.com)
 Date: February 27, 2006
::::::::::::::::::::::::::::::::::::::::
*/
class eCart {
	var $params;	
	var $templates;	
	var $config;
	var $temp_cart = array();
	var $lang = array();
	var $cart;	
	
	function eCart() {
		global $modx;
		$this->user = $modx->getWebUserInfo($modx->getLoginUserID('web'));			
	}
	
  	function init() {
		global $modx;					
		$this->setTVList();		
		$this->loadConfig();
		$this->cart = $this->getCartData();		
		$this->temp_cart = $this->getTempCartData();		
	}
	
	function GenerateOrderId($length = 20)
	{
		global $modx;
        $allowable_characters = "ABCDEFGHJKLMNPQRSTUVWXYZ0123456789";
        $ps_len = strlen($allowable_characters);
        mt_srand((double)microtime()*1000000);
        
        $id = "";
        for($i = 0; $i < $length; $i++) {
            $id .= $allowable_characters[mt_rand(0,$ps_len-1)];
        }        
        
        $sql = "SELECT id FROM ".$modx->getFullTableName("site_ec_orders")."  WHERE  id = '$id' LIMIT 1";
		$result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);	
		if ($numResults == 0) return $id;
        else return $this->GenerateOrderId();       
	}
	
	function GenerateOrderBonusCode($length = 10)
	{
		global $modx;
        $allowable_characters = "ABCDEFGHJKLMNPQRSTUVWXYZ0123456789";
        $ps_len = strlen($allowable_characters);
        mt_srand((double)microtime()*1000000);        
        $code = "";
        for($i = 0; $i < $length; $i++) {
            $code .= $allowable_characters[mt_rand(0,$ps_len-1)];
        }       
        
        $sql = "SELECT id FROM ".$modx->getFullTableName("site_ec_orders")."  WHERE  bonus_code = '$code' LIMIT 1";
		$result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);	
		if ($numResults == 0) return $code;
        else return $this->GenerateOrderBonusCode();       
	}
	
	function sendOrderDetails($confirm, $order_id) {
		global $modx,$base_path;

			
		if (!$this->user) return $this->lang($lang[5]);		
		
		if ($confirm == 0)
		$output = $this->config['ec_email_order_details_msg'];
		else 
		$output = $this->config['ec_email_order_confirm_msg'];
		
		$email_signature = $this->config['ec_webemail_signature'];
		
		$itemhomeid = $this->config['ec_item_home_id'];	
		
		
		$checkouthomeid = $this->params['checkouthomeid'];	
		$confirmorderhomeid = $this->params['confirmorderhomeid'];
		
		$username = $this->user['fname'].' '. $this->user['sname'].' '. $this->user['lname'];		
		$output = str_replace('[+uname+]', $username, $output);		
		
		$order = $this->getOrderInfo($order_id);		
		
		
		if ($order['payment_type'] == 8) {
			$bank_account = $this->config['ec_email_bank_account'];
			$output = str_replace('[+bank_account+]', $bank_account, $output);	
			$username = $this->user['fname'].' '. $this->user['sname'].' '. $this->user['lname'];
			$username=urlencode($username);
			$output = str_replace('[+uname+]', $username, $output);			
		} else $output = str_replace('[+bank_account+]', '', $output);
		
		if ($order['delivery_type'] == 'outsea') {
			$order['payment_name'] = 'Вам будет отправлена информация на е-майл о способы оплаты';
			$order['delivery_amount'] = 'Вам будет отправлена информация на е-майл о точной сумме доставки';							
		}
		
		$confirm_key = $order['confirm_key'];		
		$confirm_link = $modx->makeUrl($confirmorderhomeid).'?user_order_id='.$order_id.'&confirm='.$confirm_key;		
		$output = str_replace('[+confirm_link+]', $confirm_link, $output);
		
		$checkout_link = $modx->makeUrl($checkouthomeid).'?user_order_id='.$order_id;		
		$output = str_replace('[+checkout_link+]', $checkout_link, $output);
		
		$order['cart_amount'] = $order['amount'] - $order['delivery_amount'];
		$order['order_date'] = datetime($order['order_date']);
		
		foreach($order as $k => $v) {
			$output = str_replace('[+'.$k.'+]', $v, $output);
		} 		
		
		$order_items = $this->getOrderItemsInfo($order_id);
		$items_list = "<ul>";
		
		foreach($order_items as $k => $item) {
			$price = money1($item['price']).' '.$this->lang['currency'];
			$quantity = quantity($item['quantity']).' '.$this->lang['quantity'];
			$items_list .= "<li><a target=\"_blank\" href=\"".$modx->makeUrl($itemhomeid)."?id=".$item[item_id]."\">".$item['itemtitle']."</a> - ".$price." - ".$quantity."</li>";
		}
		
		$items_list .= "</ul>";		
		$output = str_replace('[+orderitems+]',$items_list,$output);		
		include_once $modx->config['base_path']."manager/includes/controls/class.phpmailer.php";
		$Confirm = new PHPMailer();
		$Confirm->CharSet="windows-1251";
		$Confirm->From = $modx->config['emailsender'];
		$Confirm->FromName = $modx->config['site_name'];
		
		if ($confirm == 0)
		$Confirm->Subject = $this->config['ec_email_order_details_subject'];
		else 
		$Confirm->Subject = $this->config['ec_email_order_confirm_subject'];
		
		
		$output .= $email_signature;
		$Confirm->Body = $output;		
		
		$email = $this->user['email'];		 
		$fullname = $this->user['fname'].' '. $this->user['sname'].' '. $this->user['lname'];		 
		$Confirm->AddAddress($email, $fullname);
		$Confirm->IsHTML(true);		
		
		if ($Confirm->Send()) {
			return true;			 
		} else return $this->lang[5];
	}
	
	
	function sendOrderDetailsToAdmin($order_id) {
		global $modx,$base_path;

			
		if (!$this->user) return $this->lang($lang[5]);		
		
		$output = $this->config['ec_order_email_text'];
		$itemhomeid = $this->config['ec_item_home_id'];			

		$username = $this->user['fname'].' '. $this->user['sname'].' '. $this->user['lname'];		
		$output = str_replace('[+uname+]', $username, $output);		
		
		$order = $this->getOrderInfo($order_id);		
		
		
		if ($order['payment_type'] == 8) {
			$bank_account = $this->config['ec_email_bank_account'];
			$output = str_replace('[+bank_account+]', $bank_account, $output);				
		} else $output = str_replace('[+bank_account+]', '', $output);
		
		if ($order['delivery_type'] == 'outsea') {
			$order['payment_name'] = '-';
			$order['delivery_amount'] = '-';							
		}
		
		
		$order['cart_amount'] = $order['amount'] - $order['delivery_amount'];
		$order['order_date'] = datetime($order['order_date']);
		
		foreach($order as $k => $v) {
			$output = str_replace('[+'.$k.'+]', $v, $output);
		} 		
		
		$order_items = $this->getOrderItemsInfo($order_id);
		$items_list = "<ul>";
		
		foreach($order_items as $k => $item) {
			$price = money1($item['price']).' '.$this->lang['currency'];
			$quantity = quantity($item['quantity']).' '.$this->lang['quantity'];
			$items_list .= "<li><a target=\"_blank\" href=\"".$modx->makeUrl($itemhomeid)."?id=".$item[item_id]."\">".$item['itemtitle']."</a> - ".$price." - ".$quantity."</li>";
		}
		
		$items_list .= "</ul>";		
		$output = str_replace('[+orderitems+]',$items_list,$output);		
		include_once $modx->config['base_path']."manager/includes/controls/class.phpmailer.php";
		$Confirm = new PHPMailer();
		$Confirm->CharSet="windows-1251";
		$Confirm->From = $modx->config['emailsender'];
		$Confirm->FromName = $modx->config['site_name'];
		
		$Confirm->Subject = $this->config['ec_order_email_subject'];
		
		
		$output .= $email_signature;
		$Confirm->Body = $output;		
		
		$email = $this->config['ec_order_admin_email'];	 
		$fullname = $this->user['fname'].' '. $this->user['sname'].' '. $this->user['lname'];		 
		$Confirm->AddAddress($email, $fullname);
		$Confirm->IsHTML(true);		
		
		if ($Confirm->Send()) {
			return true;			 
		} else return $this->lang[5];
	}
	
	function sendOrderDoneMessage($customer_id,$order_id) {
		global $modx,$base_path;
		$customer = $modx->getWebUserInfo($customer_id);
		$output = $this->config['ec_email_order_done_mgs'];
		$email_signature = $this->config['ec_webemail_signature'];
		$itemhomeid = $this->config['ec_item_home_id'];
		$username = $customer['fname'].' '. $customer['sname'].' '. $customer['lname'];		
		$output = str_replace('[+uname+]', $username, $output);
		$order = $this->getOrderInfo($order_id);
		$order['order_date'] = datetime($order['order_date']);		
		
		foreach($order as $k => $v) {
			$output = str_replace('[+'.$k.'+]', $v, $output);
		} 		
		
		$order_items = $this->getOrderItemsInfo($order_id);
		$items_list = "<ul>";
		
		foreach($order_items as $k => $item) {
			$price = money1($item['price']).' '.$this->lang['currency'];
			$quantity = quantity($item['quantity']).' '.$this->lang['quantity'];
			$items_list .= "<li><a target=\"_blank\" href=\"".$modx->makeUrl($itemhomeid)."?id=".$item[item_id]."\">".$item['itemtitle']."</a> - ".$price." - ".$quantity."</li>";
		}
		
		$items_list .= "</ul>";		
		$output = str_replace('[+orderitems+]',$items_list,$output);		
		include_once $modx->config['base_path']."manager/includes/controls/class.phpmailer.php";
		$Confirm = new PHPMailer();
		$Confirm->CharSet="windows-1251";
		$Confirm->From = $modx->config['emailsender'];
		$Confirm->FromName = $modx->config['site_name'];
		$Confirm->Subject = $this->config['ec_email_order_done_subject'];
		$output .= $email_signature;
		$Confirm->Body = $output;
		$Confirm->IsHTML(true);	
		$email = $customer['email'];
		$fullname = $customer['fname'].' '. $customer['sname'].' '. $customer['lname'];		 
		$Confirm->AddAddress($email, $fullname);	
		if ($Confirm->Send()) {
			return true;			 
		} else return $this->lang[5];
	}
	
	
	
	function sendOrderSentMessage($order) {
		global $modx,$base_path;
		$output = $this->config['ec_email_order_done_mgs'];
		$email_signature = $this->config['ec_webemail_signature'];
		$itemhomeid = $this->config['ec_item_home_id'];
		$cust_name = $order['customer_fname'].''.$order['customer_sname'].''.$order['customer_lname'];				
		$output = str_replace('[+uname+]', $cust_name, $output);
		$order = $this->getOrderInfo($order_id);
		
		$order['order_date'] = datetime($order['order_date']);	
		$output = str_replace('[+order_fdate+]', $order['order_date'], $output);
		$output = str_replace('[+id+]', $order_id, $output);
		
		

		include_once $modx->config['base_path']."manager/includes/controls/class.phpmailer.php";
		$Confirm = new PHPMailer();
		$Confirm->CharSet="windows-1251";
		$Confirm->From = $modx->config['emailsender'];
		$Confirm->FromName = $modx->config['site_name'];
		$Confirm->Subject = $this->config['ec_email_order_done_subject'];
		$output .= $email_signature;
		$Confirm->Body = $output;
		$Confirm->IsHTML(true);	
		$email = $customer['email'];
		$fullname = $customer['fname'].' '. $customer['sname'].' '. $customer['lname'];		 
		$Confirm->AddAddress($email, $fullname);	
		if ($Confirm->Send()) {
			return true;			 
		} else return $this->lang[5];
	}
	
	
	function sendPaymentDoneMessage($customer_id,$order_id) {
		global $modx;
		$customer = $modx->getWebUserInfo($customer_id);
		$output = $this->config['ec_email_payment_done_msg'];
		$email_signature = $this->config['ec_webemail_signature'];
		$username = $customer['fname'].' '. $customer['sname'].' '. $customer['lname'];		
		$output = str_replace('[+uname+]', $username, $output);
		$order = $this->getOrderInfo($order_id);
		
		$order['order_date'] = datetime($order['order_date']);
		foreach($order as $k => $v) {
			$output = str_replace('[+'.$k.'+]', $v, $output);
		} 		
		include_once $modx->config['base_path']."manager/includes/controls/class.phpmailer.php";
		$Confirm = new PHPMailer();
		$Confirm->IsHTML(true);	
		$Confirm->CharSet="windows-1251";
		$Confirm->From = $modx->config['emailsender'];
		$Confirm->FromName = $modx->config['site_name'];
		$Confirm->Subject = $this->config['ec_email_payment_done_subject'];
		$output .= $email_signature;
		$Confirm->Body = $output;
		$email = $customer['email'];		
		$fullname = $customer['fname'].' '. $customer['sname'].' '. $customer['lname'];		 
		$Confirm->AddAddress($email, $fullname);		
		if ($Confirm->Send()) {
			return true;			 
		} else return $this->lang[5];
	}

	function confirmOrder() {
		global $modx;		
		$output = $this->getTemplate($this->templates['cartConfirmOrderTpl']);	
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);	
		
		if (isset($_REQUEST['confirm']) && !empty($_REQUEST['confirm']) && $this->config['order_confirmed_status']) {
		    
			if (!$modx->getLoginUserID('web')) {
				$_SESSION['AFTER_LOGIN_GO_URL'] = $modx->config['server_protocol'].'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];					
				$url = $modx->makeUrl($this->params['mustloginpageid']);				
				//session_write_close();
                $modx->sendRedirect($url,0,'REDIRECT_HEADER'); 
                return;
			} 	
			
			$confirm_key = mysql_escape_string(trim($_REQUEST['confirm']));
			
			if (isset($_SESSION['user_order_id']) && !empty($_SESSION['user_order_id'])) {
                $order_id = mysql_escape_string($_SESSION['user_order_id']);
            } elseif (isset($_REQUEST['user_order_id']) && !empty($_REQUEST['user_order_id'])) {
                $order_id = mysql_escape_string($_REQUEST['user_order_id']);
                $_SESSION['user_order_id'] = $order_id;          
            } else {
                $url = $modx->makeUrl($modx->config['site_start']);
                $modx->sendRedirect($url,0,'REDIRECT_HEADER');     
                return;     
            }			
            
            $sql = "SELECT id FROM ".$modx->getFullTableName("site_ec_orders")."  WHERE id = '$order_id' AND confirm_key = '$confirm_key' AND confirmed<>1  LIMIT 1;";
		    
             $result = $modx->dbQuery($sql);
            
         		
	
            
			$numResults = @$modx->recordCount($result);					
			if ($numResults === 1) {
				$row = $modx->fetchRow($result);						
				$sql = "UPDATE ".$modx->getFullTableName("site_ec_orders")." SET confirmed='1', confirm_key='' WHERE id = '$order_id' LIMIT 1";
				$modx->dbQuery($sql);				
				$url = $modx->makeUrl($this->params['checkouthomeid']);				 		 
				$modx->sendRedirect($url,0,'REDIRECT_HEADER');
				return;
			} else {
				$message = str_replace('[+message+]',  $this->lang['7'], $messageTpl);
				$output = str_replace('[+ec.message+]', $message, $output);
				
				
				return $output;			
			}			
		} elseif (isset($_SESSION['user_order_id'])) {						
			$message = str_replace('[+message+]',  $this->lang['6'], $messageTpl);
			$output = str_replace('[+ec.message+]', $message, $output);
			
			$order_id=$_SESSION['user_order_id'];
			
		 $sql = "SELECT  confirm_key FROM ".$modx->getFullTableName("site_ec_orders")."  WHERE id = '$order_id'   LIMIT 1;";
		    $result1 = $modx->dbQuery($sql);	  
            $order1 = mysql_fetch_assoc($result1);    
			
			$confirm_link = $order1['confirm_key'];
			
			

$output_add='<br><br><br>
				<blockquote><b>
				<a href="http://www.cddiski.ru/cabinet/confirmorder?user_order_id='.$order_id.'&confirm='.$order1['confirm_key'].'">
				Также можно подтвердить  свой заказ здесь. Переход по этой ссылке означает автоматическое подтверждение заказа.</a>
				<br>В этом случае подтверждать заказ через емейл не нужно. 
				</b><blockquote>';
				$output=$output.$output_add;
	

			
			return $output;
		} else {
			$url = $modx->makeUrl($modx->config['site_start']);
			$modx->sendRedirect($url,0,'REDIRECT_HEADER'); 
			return;          
		}	
	}	
	
	function loadConfig() {
		global $modx;
		$ec_settings = array();
		$sql = "SELECT setting_name, setting_value FROM ".$modx->getFullTableName("ec_settings");
		$rs = mysql_query($sql);
		$number_of_ec_settings = mysql_num_rows($rs);
		while ($row = mysql_fetch_assoc($rs)) {
			$this->config[$row['setting_name']] = $row['setting_value'];
		}			
	}
	
	function getShippingAmount($rate, $weight){
		global $modx;
		if ($rate) {
			foreach($rate as $k => $v) {
				$min = floatval($v[0]);
				$max = floatval($v[1]);
				$price = floatval($v[2]);
				$pack = floatval($v[3]);
				if ($weight >= $min && $weight <= $max) return $v;								
			}			
		} 
		return false;
	}
	
	
	function getRegion($id)
	{
		global $modx;		
		$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_regions") . " WHERE id='$id' ";
		$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
		if ($rs && mysql_num_rows($rs) == 1) {
			$row = mysql_fetch_assoc($rs);
			return $row['postcode'];
		} else return 0;	
	}
	
	function getRegionRate($region_id){
		global $modx;		
		$postcode = $this->getRegion($region_id);
		$zone = intval($postcode{0});
		
		if ($zone>5) $zone = 5;
		
		$sql = "SELECT rate FROM ".$modx->getFullTableName("site_ec_cities")."sc ";
		$sql.= "INNER JOIN ".$modx->getFullTableName("site_ec_shipping_rates")."shr ON shr.zone = sc.rate_zone ";
	    $sql.= "WHERE sc.id = {$city_id} LIMIT 1;";
		
	    $sql = "SELECT rate FROM ".$modx->getFullTableName("site_ec_shipping_rates")." ";
	    $sql.= "WHERE zone = $zone LIMIT 1;";
		
	    
	    $result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);						
		if ($numResults == 1) {			
			$row = $modx->fetchRow($result);
			$rate = $this->parseProperties($row['rate']);			
			if (sizeof($rate)>0)		
			return $rate;
			else return 0;
		} else return false;		
	}
	
	function addItemToCart() {
		global $modx;		
		
		if ($this->user == false) { 
		
			$this->addItemToTempCart();	
			return;
		}		
		
		$user_id = $this->user['id'];
		$items = array();
		$items[] = $_REQUEST['item'];
		if (isset($_REQUEST['accessories'])) {			
			$accessories = $_REQUEST['accessories'];
			foreach($accessories as $accessorie) {
				if (isset($accessorie['checked']) && intval($accessorie['checked']) === 1) 
				$items[] = $accessorie;
			}			
		}		
		foreach($items as $item) {		
			$quantity = intval($item['quantity']);
			$item_id = intval($item['id']);	
			$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_shopping_cart")." ";
			$sql.= "WHERE customer_id = {$user_id} AND item_id = {$item_id} LIMIT 1;";
			$result = $modx->dbQuery($sql);	        
			$numResults = @$modx->recordCount($result);			
			if ($numResults == 1) {
				$sql = "UPDATE ".$modx->getFullTableName("site_ec_shopping_cart")." ";
				$sql.= "SET quantity = quantity + {$quantity} WHERE customer_id = {$user_id} AND item_id = {$item_id} LIMIT 1;";
			} else {
				$sql = "INSERT INTO ".$modx->getFullTableName("site_ec_shopping_cart")."(customer_id,item_id,quantity) ";
				$sql.= "VALUES($user_id,$item_id,$quantity);";					
			}		
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "Something went wrong while trying to remove deleted documents!";
			}	
			//echo $sql;
			//exit;
		}	
		$this->cart = $this->getCartData();		
		return;		 
	}
	
	function addItemToTempCart() {
		global $modx;		
		$items = array();
		$items[] = $_REQUEST['item'];
		$temp_cart_session = $_SESSION['temp_cart'];
		if (isset($_REQUEST['accessories'])) {			
			$accessories = $_REQUEST['accessories'];
			foreach($accessories as $accessorie) {
				if (isset($accessorie['checked']) && intval($accessorie['checked']) === 1) 
				$items[] = $accessorie;
			}			
		}		
		 
		//var_dump($items);
		//exit;
		
		foreach($items as $item) {		
			$quantity = intval($item['quantity']);
			$item_id = intval($item['id']);			
			$exists = false;			
			if (count($_SESSION['temp_cart'])>0)
			foreach($_SESSION['temp_cart'] as $k => $item) {
				if ($item['item_id'] == $item_id) {	
					$exists = true;
					$index = $k;
					break;			
				} 
			}		
			if ($exists) {
				//echo $index;
				//var_dump($_SESSION['temp_cart']);
				//exit;
				//$temp_cart_session[$index]['quantity'] += $quantity; 
				$_SESSION['temp_cart'][$index]['quantity'] += $quantity; 
			} else {				
				//$temp_cart_session[$item_id] = array(item_id => $item_id, quantity => $quantity);
				$_SESSION['temp_cart'][$item_id] = array(item_id => $item_id, quantity => $quantity); 
			}			
			$this->temp_cart = $this->getTempCartData();
		}	
			
		return;		 
	}
	
	function moveTempToCart() {
		global $modx;
		$temp_cart_ = array();
		$user_id = $this->user['id'];		
		$temp_cart_session = $_SESSION['temp_cart'];
		if (count($temp_cart_session) > 0 && !empty($user_id)) {								
			foreach($temp_cart_session as $item) {		
				$quantity = intval($item['quantity']);
				$item_id = intval($item['item_id']);	
				$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_shopping_cart")." ";
				$sql.= "WHERE customer_id = {$user_id} AND item_id = {$item_id} LIMIT 1;";
				$result = $modx->dbQuery($sql);	        
				$numResults = @$modx->recordCount($result);			
				if ($numResults == 1) {
					$sql = "UPDATE ".$modx->getFullTableName("site_ec_shopping_cart")." ";
					$sql.= "SET quantity = quantity + {$quantity} WHERE customer_id = {$user_id} AND item_id = {$item_id} LIMIT 1;";
				} else {
					$sql = "INSERT INTO ".$modx->getFullTableName("site_ec_shopping_cart")."(customer_id,item_id,quantity) ";
					$sql.= "VALUES($user_id,$item_id,$quantity);";					
					$temp_cart_[$item_id] = array(item_id => $item_id, quantity => $quantity);
				}		
				$rs = mysql_query($sql);
				if(!$rs) {
					echo "Something went wrong while trying to remove deleted documents!";
				}	
			//echo $sql;
			//exit;
			}	
			unset($_SESSION['temp_cart']);				
		}		
	}
	
	function updateCart() {
		global $modx;
		$temp_cart_ = array();
		$user_id = $this->user['id'];		
		if (isset($_POST['cart']) && $user_id) {			
			$items = $_POST['cart'];			
			foreach($items as $item) {					
				$item_id = intval($item['id']);
				$quantity = intval($item['quantity']);				
				if ($item['remove'] == '1') {					
					$sql = "DELETE FROM ".$modx->getFullTableName("site_ec_shopping_cart")." "; 
					$sql.= "WHERE customer_id = {$user_id} AND item_id = {$item_id} LIMIT 1;";												
				} else {
					$quantity = intval($item['quantity']);
					if ($quantity == 0) {
						$sql = "DELETE FROM ".$modx->getFullTableName("site_ec_shopping_cart")." "; 
						$sql.= "WHERE customer_id = {$user_id} AND item_id = {$item_id} LIMIT 1;";		
					} else {
						$sql = "UPDATE ".$modx->getFullTableName("site_ec_shopping_cart")." ";
						$sql.= "SET quantity = {$quantity} WHERE customer_id = {$user_id} AND item_id = {$item_id} LIMIT 1;";						}
				}				
				//echo $sql."<br>";				
				$rs = mysql_query($sql);					
				if(!$rs) {
									
				}  	
			}
			$this->cart = $this->getCartData();				
		} else {
			$this->updateTempCart();
		}
	}	
	
	function updateTempCart() {
		global $modx;
		$temp_cart_ = array();
		if (isset($_POST['cart'])) {
			$items = $_POST['cart'];
						
			foreach($items as $id => $item) {			
				$item_id = intval($items[$id]['id']);
				$quantity = intval($items[$id]['quantity']);				
				if ($items[$id]['remove'] != '1' && $quantity != 0) {					
					$temp_cart_[$id] = array(item_id => $item_id, quantity => $quantity);					
				}				
			}
			$_SESSION['temp_cart'] = $temp_cart_;
			$this->temp_cart = $this->getTempCartData();				
		}	
	}
	
	function price($item,$quantity) {
			
		if ($quantity >= $item['dealer_cnt'] && $this->config['is_dealer_price_active'] == '1' && $item['dealer_price'] > 0) {
			$price = $item['dealer_price'];	
		} elseif ($quantity >= $item['mdealer_cnt'] && $this->config['is_mdealer_price_active'] == '1' && $item['mdealer_price'] > 0) {
			$price = $item['mdealer_price'];			
		} else {					
			$price = $item['retail_price'];
		}
		return $price;		
	}
	
	function buildSideBarCart() {
		global $modx;		
		$output = '';
		$total_amount = 0;
		$total_quantity = 0;			
		$sideBarCartTpl = $this->getTemplate($this->templates['cartSideBarTpl']);		
		if ($this->user) {
			$items = $this->cart;			
			$user_id = $this->user['id'];
			$discount = $this->user['bonus'];
			$order_datas = $this->getOrderDatas();
			if ($order_datas) {
				$total_amount = $order_datas['cart_amount'];
				$total_quantity = $order_datas['quantity'];		
			}	
		} else {
			$items = $this->temp_cart;
			$discount = 0;
			if (sizeof($items) > 0) 
			foreach($items as $item) {		
			    $k = $item['id'];	      
				$price = $this->price($item,$item['quantity']);
				$total_amount += $item['quantity']*$price;
				$total_quantity += $item['quantity']; 			
			}				 	
		}					
		
		$modx->setPlaceholder('ec.totalquantity', $total_quantity);
		$modx->setPlaceholder('ec.totalamount', $total_amount);
		$modx->setPlaceholder('ec.discount', $discount);
		return $sideBarCartTpl;
	}
	
	function getOrderShippingAmount($payment_mode, $delivery, $dcart_amount, $shipping) {
		global $modx;
				
		$multi = 0.04;
		$delivery_online_payment = floatval($this->config['delivery_online_payment']);		
			
		if (empty($delivery_online_payment) || $delivery_online_payment == 0) {
			$shipping_amount_on = $shipping[2];				
		} else { 			
			$shipping_amount_on = $delivery_online_payment;
		}
				
		$shipping_amount_off = $shipping[2] + $shipping[3] + $multi*($dcart_amount+$shipping[2]);	
		
		switch ($delivery) {
			
			case "outsea" : {
				return 0;
			};break;
			
			case "curer_za_mkad" : {
			
			
			$za_mkad = floatval($this->config['delivery_curer_za_mkad_price']);
			
					if ($dcart_amount<1000) {
			
				$v_mkad = floatval($this->config['delivery_curer_v_mkad_price']);
				$result = $v_mkad + $za_mkad;
				
				}
			else if  ($dcart_amount<1500&&$dcart_amount>=1000) {
			
				$v_mkad = floatval($this->config['delivery_curer_v_mkad_price2']);
				$result = $v_mkad + $za_mkad;
				
				}
				else if  ($dcart_amount<2500&&$dcart_amount>=1500) {
			
				$v_mkad = floatval($this->config['delivery_curer_v_mkad_price3']);
				$result = $v_mkad + $za_mkad; 
				
				}
				else if  ($dcart_amount<3500&&$dcart_amount>=2500) {
			
				$v_mkad = floatval($this->config['delivery_curer_v_mkad_price4']);
				$result = $v_mkad + $za_mkad;
				
				}
				else if  ($dcart_amount>=3500) {
			
				$result = $za_mkad;
				
				}
				return $result;
			
			
			};break;
			
				
				
			
			case "vstrecha" : {
			
				$result = floatval($this->config['delivery_vstrecha']);
				return $result;
			
			};break;
			
			case "samovivoz" : {
			
				$result = floatval($this->config['delivery_samovivoz']);
				return $result;
			
			};break;
			
			
			case "curer_v_mkad" : {
			
			if ($dcart_amount<=1000) {
			
				$result = floatval($this->config['delivery_curer_v_mkad_price']);
				
				}
			else if  ($dcart_amount<1500&&$dcart_amount>=1000) {
			
				$result = floatval($this->config['delivery_curer_v_mkad_price2']);
				
				}
				else if  ($dcart_amount<2500&&$dcart_amount>=1500) {
			
				$result = floatval($this->config['delivery_curer_v_mkad_price3']);
				
				}
				else if  ($dcart_amount<3500&&$dcart_amount>=2500) {
			
				$result = floatval($this->config['delivery_curer_v_mkad_price4']);
				
				}
				else if  ($dcart_amount>=3500) {
			
				$result = 0;
				
				}
				return $result;
			
			
			};break;
			
			case "1class" : {
				
				if ($payment_mode == 'online') {
					$delivery_on_1c_payment = floatval($this->config['delivery_1class_online_price']);			
					$result = $shipping_amount_on + $delivery_on_1c_payment;		
		
				} else {
			    	$delivery_off_1c_payment = floatval($this->config['delivery_1class_offline_price']);
			    	$result = $shipping_amount_off + $delivery_off_1c_payment;		
				}
				
				return $result;
			
			};break;
			
			case "basic" : {		
				
				if ($payment_mode == 'online') {
					$result = $shipping_amount_on;
				} else {
			    	$result = $shipping_amount_off;
				}			
				return $result;
			};break;			
			
		}	
	}
	
	function getOrderDatas() {
		global $modx;
		$user_id = $this->user['id'];
		$user_info = $this->user;
						 		
		if ($this->cart) {
			$cart = $this->cart;
			$quantity = 0;	
			$weight = 0;
			if (count($cart) > 0) {			
							
				foreach($cart as $item) {				
					$price = $this->price($item,$item['quantity']);
					$cart_amount += $item['quantity']*$price;
					$weight += $item['quantity']*($item['pack_weight']);
					$quantity += $item['quantity'];	
				}		
							
				// discount process						
				$discount = $user_info['bonus'];
				$dcart_amount = $cart_amount-($cart_amount*$discount/100);			
				
				
				return array(amount => $amount,
						 	 weight => $weight,
							 discount => $discount,
							 cart_amount => $cart_amount,
							 dcart_amount => $dcart_amount,		
							 over_weight => $over_weight,		         
					         quantity => $quantity
				            );					
				            		
			 } else return false;
			
			
		} else {
			$cart = $this->temp_cart;	
			$quantity = 0;	
			$weight = 0;
			if (count($cart) > 0) {						
				foreach($cart as $item) {				
					$price = $this->price($item,$item['quantity']);
					$cart_amount += $item['quantity']*$price;					
					$quantity += $item['quantity'];	
				}					
				// discount process
				$discount = 0;
				$dcart_amount = $cart_amount-($cart_amount*$discount/100);
				$amount = $shipping_amount+$dcart_amount;	
				return array(amount => $amount, 
							 discount => $discount,
							 cart_amount => money1($cart_amount),
							 dcart_amount => money1($dcart_amount),				         
					         quantity => $quantity					        
				            );							
			} else return false;	
		}			 
	}

	function buildOrderPage() {
		global $modx;
		$output = '';
		$item = array();
		$keys = array();
		$values = array();
		$_keys = array();	
		$key = '';		
		$cart_rows = '';
		$amount = 0;
		$quantity = 0;
		$_k ='';		
		$num = 0;

		$required_fields = false;
		$required_formcode = false;
		$wrong_formcode = false;	
		
		$required_field_phone = false;
		$required_field_zak = false;
		
		$user_id = $modx->getLoginUserID('web');
		
		if (!isset($_POST['order'])) {
			$last_order = $this->getLastOrder($user_id);			
		} else $last_order = false;
		
		
		if (isset($_POST['placeorder']) && isset($_POST['order'])) {	
			
			$order = $_POST['order'];						
			if (!empty($order['region']) && 
			    !empty($order['state']) &&
			    !empty($order['street']) &&
			    
			    !empty($order['formcode']) &&
			    !empty($order['dom'])  
			    
			    ) {		
				
				if (($order[shipping]=='curer_za_mkad' or $order[shipping]=='curer_v_mkad' or $order[shipping]=='vstrecha') and $order[phone]=='' ) {$required_field_phone = true;	}
			
			
			    else if ($order['formcode'] == $_SESSION['veriword']) {	
			    	$order_datas = $this->getOrderDatas();			
			    	$region_id = $order['region']; 
					$rate = $this->getRegionRate($region_id);			
		    		$shipping = $this->getShippingAmount($rate, $order_datas['weight']);					
		    		$delivery = $order['shipping'];		    		
		    		if ($order['to'] == 'russia') { 
		    			$payment_method = $this->getPaymentType($order['payment_method_id']);
						$payment_mode = $payment_method['mode'];
		    			$delivery_amount = $this->getOrderShippingAmount($payment_mode, $delivery, $order_datas['dcart_amount'], $shipping); 
						$total_amount = $order_datas['dcart_amount'] + $delivery_amount;			  		
						$order_datas['delivery_amount'] = $delivery_amount;
						$order_datas['total_amount'] = $total_amount;			
		    		} else {
		    			$delivery_amount = 0;
		    			$total_amount = $order_datas['dcart_amount'] + $delivery_amount;
		    			$order_datas['delivery_amount'] = $delivery_amount;
						$order_datas['total_amount'] = $total_amount;	
		    		}		    			
		    		$this->placeOrder($order_datas);
			    } else {
			    	$wrong_formcode = true;
			    }	
				
				
				
				
						    	 	
			} else {
				//if (empty($order['formcode'])) $required_formcode = true;
				$required_fields = true;					
			}										
		}
		
		$messageTpl = $this->getTemplate($this->templates['message1Tpl']);		
		$outerTpl = $this->getTemplate($this->templates['cartOrderOuterTpl']);
		$rowTpl = $this->getTemplate($this->templates['cartRowTpl']);
		$addressTpl = $this->getTemplate($this->templates['cartAddressTpl']);
		$cart = $this->cart;		
		if (is_array($cart) && sizeof($cart) > 0) {						
			foreach(@$cart as $item) {				
			    $num++;
			    $price = $this->price($item,$item['quantity']);
				$amount += $item['quantity']*$price;
				$quantity += $item['quantity'];             				
				$_rowTpl = $rowTpl;	
				$_rowTpl = str_replace("[+num+]", $num, $_rowTpl);		
				$_rowTpl = str_replace("[+itemhomeid+]", $this->params['itemhomeid'], $_rowTpl);			
				$_rowTpl = str_replace("[+subtotal+]", money1($item['quantity']*$price), $_rowTpl);	
				$_rowTpl = str_replace("[+price+]", money1($price), $_rowTpl);							
				foreach($item as $_k => $_v) {
					$_rowTpl = str_replace('[+'.$_k.'+]', $_v, $_rowTpl);
				} 
				$cart_rows .= $_rowTpl;	
			}		
			//echo $quantity;
			$wait = $this->config['incomplete_order_do'];
			$do = $this->config['incomplete_order_wait'];			
			$wait = explode(',',$wait);
			$do = explode(',',$do);	
			$wait_list = '';	
			foreach($wait as $item) {
				$wait_list.= "<option value=\"$item\">$item</option>"; 
			}
			$do_list = '';		
			foreach($do as $item) {
				$do_list.= "<option value=\"$item\">$item</option>"; 
			}
			if (!empty($wait_list)) $wait_list = "<select name=\"order[skuwait]\">$wait_list</select>";
			if (!empty($do_list)) $do_list = "<select name=\"order[skudo]\">$do_list</select>";		
				
			$modx->setPlaceholder('ec.sku.wait', $wait_list);
			$modx->setPlaceholder('ec.sku.do', $do_list);			
			
			
			$user_id = $this->user['id'];	
		$sql44 = "SELECT modx_site_ec_regions.id, modx_site_ec_regions.name, modx_web_user_attributes.internalKey, modx_web_user_attributes.region  FROM modx_web_user_attributes, modx_site_ec_regions WHERE internalKey='$user_id' and  modx_site_ec_regions.name=modx_web_user_attributes.region LIMIT 1";
		$rest44 = mysql_query($sql44);
		if (!$rest44) {$regi='18825'; }
		else {
		$row_at = mysql_fetch_assoc($rest44); $regi=$row_at['id'];
		}
		if (!$regi) $regi='18825';
			
			if (!isset($_POST['order'])) {
				$region_id = $regi;	
				
				if ($last_order && $last_order['delivery_type'] != 'outsea') {
					$order['region'] = $this->getRegionIdByName($last_order['customer_region']);
					//echo $order['region'];
					$region_id = $order['region']; 
				}
				
				
				
			} else {
				$region_id = $_POST['from'];
				if ($region_id == 'to.russia')
				$region_id = '18825';	
				else $region_id = $_POST['order']['region']; 
			}
			
		    $modx->setPlaceholder('regi', $regi);
			$modx->setPlaceholder('regions.list', $this->getRegionsList($region_id));
			
			if (isset($_POST['order'])) {
		
				$order = $_POST['order'];
				$to = $order['to'];
				$from = $_POST['from'];
				
				if ($to == 'other')  $order['shipping'] = 'outsea';				
				
				if ($region_id != '18825' && $from == 'region' ) {
					$order['shipping'] = 'basic';
				} elseif ($region_id == '18825' && $from == 'region') {
					$order['shipping'] = 'curer_v_mkad';				
				}				 
				
				if ($from == 'to.russia') {
					$order['to'] = 'russia';
					$order['region'] = '18825';
					$order['shipping'] = 'curer_v_mkad';	
				}			
				
				foreach($order as $k => $v) {
					$modx->setPlaceholder('order.'.$k, $v);
				}
				
				$delivery = $order['shipping'];
				$payment_method = $this->getPaymentType($order['payment_method_id']);
				$payment_mode = $payment_method['mode'];					
				//exit;		
			} else {
				
				if ($last_order) {
					
					if ($last_order['delivery_type'] == 'outsea') {
						$order['to'] = 'other';
					} else {
						$order['to'] = 'russia';									
					}
			
					$order['state'] = $last_order['customer_state'];
					$order['postcode1'] = $last_order['customer_postcode1'];
					$order['metro'] = $last_order['customer_metro'];
					$order['street'] = $last_order['customer_street'];
					$order['dom'] = $last_order['customer_dom'];
					$order['korpus'] = $last_order['customer_korpus'];
					$order['kvartira'] = $last_order['customer_kvartira'];
					$order['shipping'] = $last_order['delivery_type'];
					$order['payment_method_id]'] = $last_order['payment_type'];
					
					foreach($order as $k => $v) {
						$modx->setPlaceholder('order.'.$k, $v);
					}
					
					$delivery = $order['shipping'];
					$payment_method = $this->getPaymentType($order['payment_method_id']);
					$payment_mode = $payment_method['mode'];
					
				} else {
					$to = 'russia';
					if ($regi=='18825'){
					$payment_mode = 'online'; 
					$delivery = 'curer_v_mkad';}
					else {$payment_mode = 'offline'; 
					$delivery = 'basic';}
					$modx->setPlaceholder('order.region', '$regi');		
					$modx->setPlaceholder('order.to', $to);
					$modx->setPlaceholder('order.shipping', $delivery);		
				}
			}
			
			
			$order_datas = $this->getOrderDatas();
			$rate = $this->getRegionRate($region_id);
		    $shipping = $this->getShippingAmount($rate, $order_datas['weight']);
			
			if ($shipping == false) {
				$over_weight = '1';
			} else $over_weight = '0';	
			
		    $delivery_amount = $this->getOrderShippingAmount($payment_mode, $delivery, $order_datas['dcart_amount'], $shipping); 
			$total_amount = $order_datas['dcart_amount'] + $delivery_amount;	
						
			$modx->setPlaceholder('ec.cartquantity', $order_datas['quantity']);
			$modx->setPlaceholder('ec.cartdiscount', $order_datas['discount']);
			//$modx->setPlaceholder('ec.overweight', $over_weight);
			$modx->setPlaceholder('ec.cartamount', money1($order_datas['cart_amount']));
			$modx->setPlaceholder('ec.dcartamount', money1($order_datas['dcart_amount']));
			$modx->setPlaceholder('ec.shippingamount', money1($delivery_amount));
			$modx->setPlaceholder('ec.totalamount', money1($total_amount));
			
			$modx->setPlaceholder('form.captcha', 'manager/includes/veriword.php');					
			$output = str_replace('[+ec.wrapper+]',$cart_rows, $outerTpl);			
			
	
			if (isset($order['payment_method_id'])) {
				$order_payment_id = $order['payment_method_id'];
			} else {
				$order_payment_id = 0;	
			}
		
			
			
			$output = str_replace('[+ec.payment.types+]',$this->buildPaymentTypes($order_payment_id), $output);	
			$user_id = $this->user['id'];
			$user_info = $this->user;
			
			foreach($user_info as $k => $v) {
				$output = str_replace('[+'.$k.'+]', $v, $output);
			}		
			
			
			
			if ($wrong_formcode) {			
				$message = str_replace('[+message+]',$this->lang[200], $messageTpl);
				$output = str_replace('[+message+]',$message, $output);				
			}		
			
			if ($required_formcode) {			
				$message = str_replace('[+message+]',$this->lang[201], $messageTpl);
				$output = str_replace('[+message+]',$message, $output);				
			} elseif ($required_fields) {
				$message = str_replace('[+message+]',$this->lang[202], $messageTpl);
				$output = str_replace('[+message+]',$message, $output);
			}		
			elseif ($required_field_phone) {
				$message = str_replace('[+message+]',$this->lang[203], $messageTpl);
				$output = str_replace('[+message+]',$message, $output);
			}
			elseif ($required_field_zak) {
				$message = str_replace('[+message+]',$this->lang[204], $messageTpl);
				$output = str_replace('[+message+]',$message, $output);
			}
			
			if ($over_weight == '1') {			
				$message = str_replace('[+message+]',$this->config['ec_over_weight_message'], $messageTpl);
				$output = str_replace('[+message+]',$message, $output);	
				$modx->setPlaceholder('ec.overweight', '1');							
			} else $modx->setPlaceholder('ec.overweight', '0');		
							
		} else {			
			$message = str_replace('[+message+]',$this->lang[0], $messageTpl);		
			$output = $message;
		}
		return $output;
	}
	
	function getRegionsList($act)
	{
		global $modx;
		$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_regions") . ' order by listindex, name';
		$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
		$lines = array();
		$lines[] = '<select class="reg_field" name="order[region]" onchange="submitForm(\'region\')">';	
		if ($rs && mysql_num_rows($rs)>0) {
			while ($row = mysql_fetch_assoc($rs)) {
				
				if (intval($row['id']) == intval($act))  
				$lines[] = '<option value="'.$row['id'].'" selected >'.$row['name'].'</option>';
				else  $lines[] = '<option value="'.$row['id'].'"  >'.$row['name'].'</option>';					 
					
			}		
		}
		$lines[] = '</select>';	
				
		return implode("\n", $lines);
	}
	
	
	function getRegionNameById($id)
	{
		global $modx;
		$name = false;
		$sql = "SELECT * FROM " . $modx->getFullTableName("site_ec_regions") . " WHERE id = '$id' LIMIT 1";
		$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
		if ($rs && mysql_num_rows($rs) == 1) {
			$row = mysql_fetch_assoc($rs);
			$name = $row['name'];					
		}				
		return $name;
	}
	
	function getRegionIdByName($name)
	{
		global $modx;
		$id = false;
		$sql = "SELECT * FROM " . $modx->getFullTableName("site_ec_regions") . " WHERE name = '$name' LIMIT 1";
		$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
		if ($rs && mysql_num_rows($rs) == 1) {
			$row = mysql_fetch_assoc($rs);
			$id = $row['id'];					
		}				
		return $id;
	}	
	
	
	function getStatesList()
	{
		global $modx;
		
		if (isset($_POST['region'])) $region = intval($_POST['region']); 
		else $region = '18825';	

		$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_cities") . ' WHERE rid = '.$region.' order by listindex,name';
		$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
		$lines = array();
		$lines[] = '<select class="reg_field" name="order[state]">';				

		if ($rs && mysql_num_rows($rs)>0) {
			while ($row = mysql_fetch_assoc($rs)) {			
				if (isset($_POST['state']) && $_POST['state'] == $row['id'])
				$lines[] = '<option value="'.$row['id'].'"  selected>'.$row['name'].'</option>';
				else $lines[] = '<option value="'.$row['id'].'"  >'.$row['name'].'</option>';
			}		
		} else {
			return "<strong>".$this->LanguageArray[44]."</strong>";
		}
		$lines[] = '</select>';		
		return implode("\n", $lines);
			
		
	}
	
	
	function buildPageCart() {
		global $modx;	

		$output = '';
		$item = array();
		$keys = array();
		$values = array();
		$_keys = array();	
		$key = '';		
		$cart_rows = '';
		$amount = 0;
		$quantity = 0;
		$_k ='';		
		$num = 0;		
		$messageTpl = $this->getTemplate($this->templates['message1Tpl']);		
		$outerTpl = $this->getTemplate($this->templates['cartOuterTpl']);
		$rowTpl = $this->getTemplate($this->templates['cartRowTpl']);
		
		if ($this->cart) $cart = $this->cart;
		else $cart = $this->temp_cart;			
		
	
		if (count($cart) > 0) {						
			foreach($cart as $item) {				
			    $num++;
			    $price = $this->price($item,$item['quantity']);
				$amount += $item['quantity']*$price;
				$quantity += $item['quantity'];             				
				$_rowTpl = $rowTpl;	
				$_rowTpl = str_replace("[+num+]", $num, $_rowTpl);		
				$_rowTpl = str_replace("[+itemhomeid+]", $this->params['itemhomeid'], $_rowTpl);			
				$_rowTpl = str_replace("[+subtotal+]", money1($item['quantity']*$price), $_rowTpl);	
				$_rowTpl = str_replace("[+price+]", money1($price), $_rowTpl);		
				foreach($item as $_k => $_v) {
					$_rowTpl = str_replace('[+'.$_k.'+]', $_v, $_rowTpl);
				} 
				$cart_rows .= $_rowTpl;	
				//echo 	$_rowTpl;
				//exit;						
			}		
			//echo $quantity;			
			$order_datas = $this->getOrderDatas();			
			$modx->setPlaceholder('ec.cartquantity', $order_datas['quantity']);
			$modx->setPlaceholder('ec.cartdiscount', $order_datas['discount']);
			$modx->setPlaceholder('ec.cartamount', money1($order_datas['cart_amount']));
			$modx->setPlaceholder('ec.dcartamount', money1($order_datas['dcart_amount']));
			$output = str_replace('[+ec.wrapper+]',$cart_rows, $outerTpl);	
			

		
		} else {			
			$message = str_replace('[+message+]',$this->lang[0], $messageTpl);		
			$output = $message;
		}
		return $output;
	}
	
	function getOrderDiscount($cart_amount) {
		global $modx;
		$discount = 0;		
		
		$discounts_list = $this->getDiscounts();	
		
					
		if ($discounts_list != false) {			
			foreach($discounts_list as $dis) {
			//if (!empty($dis['groupids'])) {
				//$groupids = implode(',',$dis['groupids']);
				//if (in_array($user_info['group_id'], $groupids) && !empty($dis['code']) ) {
						$rule = $this->parseProperties($dis['rule']); 
						$i=0;						
						foreach($rule as $k => $v) { 
						    $i++;
							$min = floatval($v[0]);
							$max = floatval($v[1]);
							$value = floatval($v[2]);									
						    if ($i == count($rule) && $cart_amount > $max) {
								$discount += $value;
								break;							
							}							
							if ($cart_amount >= $min && $cart_amount <= $max) {
								$discount += $value;
								break;							
							} 	
						}    					
				//}
			}		
		}	
		
		return $discount;			
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
			$row['items'] = $this->getOrderItemsInfo($row['id']);
			$row['delivery_name'] = $ar[$row['delivery_type']];
			return $row;
		}
		else return false;
	}
	
	function getLastOrder($user_id) {
		global $modx;
	  	$sql = "SELECT o.*,pt.name as payment_name,os.name as status_name FROM ".$modx->getFullTableName("site_ec_orders")." o ";
		$sql.= "LEFT JOIN ". $modx->getFullTableName("site_ec_payment_methods")." pt ON o.payment_type = pt.id "; 
		$sql.= "LEFT JOIN ". $modx->getFullTableName("ec_order_status")." os ON o.status = os.id "; 
	    $sql.= " WHERE customer_id = '$user_id' ORDER by order_date DESC ";
		$result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);					
		if ($numResults >= 1) {
			$row = $modx->fetchRow($result);
			return $row;
		}
		else return false;
	}
	
	function buildOrdersList($start = 0,$stop = 0) {
		global $modx;			
		$output = '';				
		$amount = 0;
		$quantity = 0;			
		$num = 0;	
		$_rows = '';		
		
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);		
		$outerTpl = $this->getTemplate($this->templates['cartOrdersOuterTpl']);		
		$rowTpl = $this->getTemplate($this->templates['cartOrdersRowTpl']);	
		$orders = $this->getOrdersInfo($this->user['id'], $start, $stop);	
		$pagerTpl = $this->getTemplate($this->templates['pagerTpl']);
					
		if (is_array($orders) && count($orders) > 0) {						
			foreach($orders as $order) {				
			    $num++;
				$amount += $order['amount'];
				$quantity += $order['quantity'];             				
				$_rowTpl = $rowTpl;	
				$_rowTpl = str_replace("[+num+]", $num, $_rowTpl);							
				foreach($order as $_k => $_v) {
					$_rowTpl = str_replace('[+'.$_k.'+]', $_v, $_rowTpl);
				}				
				$_rows .= $_rowTpl;									
			}		
			$modx->setPlaceholder('ec.total_ordered_items', $quantity);
			$modx->setPlaceholder('ec.total_amount', money1($amount));		
			$modx->setPlaceholder('ec.order_die_days', $this->config['order_die_days']);						
			$output = str_replace('[+ec.wrapper+]', $_rows, $outerTpl);	
			$output = str_replace('[+orderdetailshomeid+]', $this->params['orderdetailshomeid'], $output);				
		} else {	
			$output = $outerTpl;
			$message = str_replace('[+message+]',$this->lang[4], $messageTpl);			 		
			$output = str_replace('[+ec.message+]',$message, $output);			
		}
		
		if (sizeof($this->pager)>0) {
			foreach($this->pager as $k => $v) {						
				$pagerTpl = str_replace('[+'.$k.'+]', $v, $pagerTpl);
			} 
			$output = str_replace('[+ec.pager+]',$pagerTpl, $output);
		}		
		
		return $output;
	}
	
	function buildOrderDetails_($order_id) {
		global $modx;		
		$output = '';		
		$amount = 0;
		$quantity = 0;			
		$num = 0;	
		$_rows = '';	
		$outerTpl = $this->getTemplate($this->templates['cartUserOrderOuterTpl']);
		$rowTpl = $this->getTemplate($this->templates['cartUserOrderRowTpl']);				
		$order = $this->getOrderInfo($order_id);		
		if ($order) {						
			foreach($order['items'] as $item) {				
			    $num++;
				$total_price = floatval($item['price'])*intval($item['quantity']);	
				$cart_amount += $total_price;	
				$total_price = money1($total_price);			            				
				$_rowTpl = $rowTpl;	
				$_rowTpl = str_replace("[+num+]", $num, $_rowTpl);	
				$_rowTpl = str_replace("[+itemhomeid+]", $this->params['itemhomeid'], $_rowTpl);
				$_rowTpl = str_replace("[+total_price+]", $total_price, $_rowTpl);										
				foreach($item as $_k => $_v) {
					$_rowTpl = str_replace('[+'.$_k.'+]', $_v, $_rowTpl);
				}				
				$_rows .= $_rowTpl;	
											
			}				
			
			foreach($order as $_k => $_v) {
					$outerTpl = str_replace('[+'.$_k.'+]', $_v, $outerTpl);
			}							
			
			$modx->setPlaceholder('ec.order_die_days', $this->config['order_die_days']);
			$output = str_replace('[+ec.wrapper+]',$_rows, $outerTpl);			
			$dcart_amount = $cart_amount-($cart_amount*$order['discount']/100);			
			$modx->setPlaceholder('ec.cartquantity', $order['quantity']);
			$modx->setPlaceholder('ec.cartdiscount', $order['discount']);
			$modx->setPlaceholder('ec.shippingamount', money1($order['delivery_amount']));				
			$modx->setPlaceholder('ec.cartamount', money1($cart_amount));
			$modx->setPlaceholder('ec.dcartamount', money1($dcart_amount));
			$modx->setPlaceholder('ec.orderamount', money1($order['amount']));					
			return $output;
		}  else return false;		
	}
	
	function buildOrderDetails($order_id){
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);		
		$output = $this->buildOrderDetails_($order_id);
		if ($output == false) {			
			$message = str_replace('[+message+]',$this->lang[4], $messageTpl);		
			$output = $message;
		}	
		return $output;
	}
	
	function buildOrderStatus($order_id) {
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);
		if (empty($order_id)) {
			 $output = $this->getTemplate($this->templates['cartUserOrderStatusTpl']);				
		} else {
			$output = $this->buildOrderDetails_($order_id);
			if ($output == false) {
				$orderStatusformTpl = $this->getTemplate($this->templates['cartUserOrderStatusTpl']);				
				$message = str_replace('[+message+]',$this->lang[9], $messageTpl);
				$message = str_replace('[+orderid+]',$order_id, $message);		
				$output = str_replace('[+ec.message+]',$message, $orderStatusformTpl);			
			}
		}
		return $output;
	}
	
	function buildOrderBonus($bonuscode) {
		global $modx;
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);
		$output = $this->getTemplate($this->templates['cartOrderBonusTpl']);
		if (!empty($bonuscode)) {
			$order = $this->confirmOrderBonus($bonuscode);
			if ($order == 'HAS_BEEN_GIVEN') {				
				$message = str_replace('[+message+]',$this->lang[14], $messageTpl);	
				$message = str_replace('[+bonuscode+]',$bonuscode, $message);											
			} elseif ($order == 'NO_ORDER') {				
				$message = str_replace('[+message+]',$this->lang[15], $messageTpl);
				$message = str_replace('[+bonuscode+]',$bonuscode, $message);	
			} elseif($order) {
				$bonus = $this->sendBonus($order['id']);
				$user  = $this->user;
				if ($bonus == 1) {
					$message = str_replace('[+message+]',$this->lang[13], $messageTpl);					
					$message = str_replace('[+discount+]',$user['bonus'], $message);
					$message = str_replace('[+orderid+]',$order['id'], $message);			
					$orderlink = $modx->makeUrl($this->params['orderdetailshomeid'],$order['id']);
					$message = str_replace('[+orderlink+]',$orderlink, $message);
							
				} elseif ($bonus == 2) {
					$message = str_replace('[+message+]',$this->lang[17], $messageTpl);					
					$message = str_replace('[+bonus+]',$user['bonus'], $message);
					$message = str_replace('[+newbonus+]',$order['bonus'], $message);
					$message = str_replace('[+orderid+]',$order['id'], $message);			
					$orderlink = $modx->makeUrl($this->params['orderdetailshomeid'],$order['id']);
					$message = str_replace('[+orderlink+]',$orderlink, $message);
				}
				
					
			}
			$output = str_replace('[+ec.message+]',$message, $output);		
			
		}
		return $output;
	}
	
	function confirmOrderBonus($bonuscode) {
		global $modx;
		$user_id = $this->user['id'];
		$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_orders")."  WHERE bonus_code = '$bonuscode' AND customer_id = '$user_id'  LIMIT 1;";
		$result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);		
		//echo $sql;
		
		if ($numResults == 1) {
			$order = $modx->fetchRow($result);
		} else {
			return 'NO_ORDER';
		}
		
		if ($order['bonus_state'] == '0') {			
			return $order;
		} else {
			return 'HAS_BEEN_GIVEN';
		} 
	}
	
	function sendBonus($order_id) {
		global $modx;
		$order = $this->getOrderInfo($order_id);
		$user = $modx->getWebUserInfo($order['customer_id']);
		if (!$order) return false;
		if ($order['bonus'] > $user['bonus']) {
			$bonus_state = '1';
			$sql = "UPDATE ".$modx->getFullTableName("web_user_attributes")." SET bonus='$order[bonus]' WHERE internalKey = '$user[id]' LIMIT 1";
			$modx->dbQuery($sql);
		} else $bonus_state = '2';
			
		$sql = "UPDATE ".$modx->getFullTableName("site_ec_orders")." SET bonus_state='$bonus_state' WHERE id = '$order[id]' LIMIT 1";		$modx->dbQuery($sql);
		
		$this->user = $modx->getWebUserInfo($modx->getLoginUserID('web'));
		return $bonus_state;
	}

	
	function getOrdersInfo($user_id,$start = 0,$stop = 0) {
		global $modx;		
		if (strtolower($this->params['osort']) == 'random') {
			$sort = 'rand()';
			$dir = '';
		} else {
			// modify field names to use  table reference
			$sort = $this->params['osort'];
			$dir = $this->params['odir'];
		}	
		if (!($start >= 0 && $stop >= 0)) return array();
		$filter_sql =str_replace(':','=',$this->params['ofilter']);
		$sql = "SELECT o.*,pt.name as payment_type_name,os.name as status_name FROM ".$modx->getFullTableName("site_ec_orders")." o ";
		$sql.= "INNER JOIN ". $modx->getFullTableName("site_ec_payment_methods")." pt ON o.payment_type = pt.id "; 
		$sql.= "INNER JOIN ". $modx->getFullTableName("ec_order_status")." os ON o.status = os.id "; 
	    $sql.= "WHERE o.customer_id = {$user_id} ";
	    $sql.= !empty($filter_sql) ? " AND {$filter_sql} " : " ";
	    $sql.= (!empty($sort) ? "ORDER BY {$sort} " : " o.order_date DESC ");
		$sql.= ($start == 0 && $stop == 0) ? " " : " LIMIT {$start}, {$stop};";   
	 	$result = $modx->dbQuery($sql);		
	 	
	 	
		
		$numResults = @$modx->recordCount($result);					
		if ($numResults > 0) {		
			for($i=0;$i<$numResults;$i++)  {
				$rows[] = $modx->fetchRow($result);	
			}		
			return $rows;
		} else return false;
	}
	
	function getOrdersCount($user_id) {
		global $modx;
		$filter_sql = str_replace(':','=',$this->params['ofilter']);
		$sql = "SELECT count(id) as cnt FROM ".$modx->getFullTableName("site_ec_orders")."o ";
		$sql.= "WHERE o.customer_id = $user_id ";	 
		$sql.= !empty($filter_sql) ? " AND {$filter_sql} " : " ";    
	    $result = $modx->dbQuery($sql);		
		$numResults = @$modx->recordCount($result);					
		if ($numResults == 1) {		
			$rows = $modx->fetchRow($result);	
			return $rows['cnt'];
		} else return false;
	}
	
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
	
	function placeOrder($order) {
		global $modx;	
		  
		$user_id = $this->user['id'];		
		$user_info = $this->user;
				
		$order_date = time();
		$order_post = $_POST['order']; 
		
		$country = mysql_escape_string($order_post['country']);
		$region_id = intval($order_post['region']);
		$region = $this->getRegionNameById($region_id);
		$state = mysql_escape_string($order_post['state']);
		$postcode1 = mysql_escape_string($order_post['postcode1']);
		$street = mysql_escape_string($order_post['street']);
		$korpus = mysql_escape_string($order_post['korpus']);
		$dom = mysql_escape_string($order_post['dom']);
		$kvartira = mysql_escape_string($order_post['kvartira']);
		$phone = mysql_escape_string($order_post['phone']);
		
		if ($order_post['to'] == 'russia' && isset($order_post['metro'])) 
		$metro = mysql_escape_string($order_post['metro']);
		else $metro = ''; 
		
		
		
		$delivery = mysql_escape_string($order_post['shipping']);		
		$discount = mysql_escape_string($order['discount']);
		$quantity = mysql_escape_string($order['quantity']);
		
		if ($order_post['to'] == 'russia') 
		$delivery_amount = mysql_escape_string($order['delivery_amount']);
		else $delivery_amount = 0;
		
		$cart_amount = mysql_escape_string($order['cart_amount']);
		$total_amount = mysql_escape_string($order['total_amount']);
		
		
		$comment = htmlspecialchars($order_post['comment']);

		$comment = str_replace("\r", " ", $comment);
        $comment = str_replace("\n", " ", $comment);      

		$skuwait = mysql_escape_string($order_post['skuwait']);
		$skudo = mysql_escape_string($order_post['skudo']);
		
		
		$confirm_key = uniqid(rand(),true);
		$order_id = $this->GenerateOrderId();
		
		$bonus_code = $this->GenerateOrderBonusCode();
		
		if ($order_post['to'] == 'russia') {
			$payment_method_id = intval($order_post['payment_method_id']);
		    $payment_method = $this->getPaymentType($payment_method_id); 
			$pm_params = serialize($payment_method['params']);
		} else { 
			$pm_params = '';
		    $payment_method_id = '0';
		}	
		
		$status = intval($this->config['order_def_status']);	
		
		if ($payment_method['auto'] == '1') $confirmed = 0;
		else $confirmed = 0;
		
	    $bonus = $this->getOrderDiscount($cart_amount);
		
		if (isset($order_post['informcust'])) $informcust = 1; 
		else $informcust = 0;		
		
				if ($postcode1==''){
		
$query55= "select postcode from modx_site_ec_regions where name='$region' LIMIT 1";
		  $result55 = mysql_query($query55);
		  
	 $row_reg = mysql_fetch_assoc($result55);
		
		$postcode1 = $row_reg['postcode'];
		}
		
		
		$query56= "select * from modx_web_user_attributes  where internalKey='$user_id' LIMIT 1";
		  $result56 = mysql_query($query56);
	 $row_attr = mysql_fetch_assoc($result56);
	 
	 if ($row_attr['region']=='0' or $row_attr['region']=='' or $row_attr['town']=='' or $row_attr['street']=='' or $row_attr['house']=='' or $row_attr['postcode1']==''  or $row_attr['kvartira']=='' or $row_attr['korpus']=='') 
	 {
	 $query57 = "UPDATE modx_web_user_attributes SET region='$region', town = '$state', street ='$street', house='$dom', postcode1='$postcode1', kvartira='$kvartira', korpus='$korpus'  WHERE internalKey='$user_id'";
   $result57 = @mysql_query($query57);
	 
	 }
		
		if ($quantity==0) {
		$header="Location: http://www.cddiski.ru/cabinet/placeorder";
header($header);
break;
		}
			
		$sql = "INSERT INTO ".$modx->getFullTableName("site_ec_orders").
		$sql.= "(id,informcust,delivery_type,confirmed,params,bonus_code,bonus,bonus_state,customer_postcode1,confirm_key,status,order_date, payment_type, discount,delivery_amount,quantity,amount,";
		$sql.= " customer_id,customer_ip,customer_fname,customer_sname,customer_lname,customer_postcode, customer_country,customer_region,customer_state,";
		$sql.= " customer_street,customer_korpus,customer_dom,customer_kvartira,customer_metro,";
		$sql.= " customer_email,customer_phone,customer_comment,customer_sku_comment,customer_sku_comment1)";
		$sql.= " VALUES('$order_id','$informcust','$delivery','$confirmed','$pm_params','$bonus_code','$bonus','0','$postcode1','$confirm_key','$status','$order_date', '$payment_method_id', '$discount', '$delivery_amount',"; 
		$sql.= " '$quantity','$total_amount',";
		$sql.= " '$user_id', '$_SERVER[REMOTE_ADDR]', '$user_info[fname]', '$user_info[sname]', '$user_info[lname]', '$user_info[postcode]','$country',"; 
		$sql.= " '$region','$state','$street','$korpus','$dom','$kvartira','$metro','$user_info[email]','$phone','$comment','$skuwait','$skudo'); ";					
		$rs = $modx->dbQuery($sql);		
		
		if ($order_post['to'] == 'russia' && $confirm == '1') {			
			if ($order['bonus'] > $this->user['bonus']) {
				$bonus_state = '1';
				$sql = "UPDATE ".$modx->getFullTableName("web_user_attributes")." SET bonus='$order[bonus]' WHERE internalKey = '$user_id' LIMIT 1";
				$modx->dbQuery($sql);
				
			} else $bonus_state = '2';
			
			$sql = "UPDATE ".$modx->getFullTableName("site_ec_orders")." SET bonus_state='$bonus_state' WHERE id = '$order[id]' LIMIT 1";
			$modx->dbQuery($sql);
		}	
		
		
		
		if ($rs) {
			$cart =  $this->getCartData();
			foreach($cart as $item) {			
				$price = $this->price($item,$item[quantity]);
			    $sql = "INSERT INTO ".$modx->getFullTableName("site_ec_order_items")."(order_id,item_id,quantity,price)";		    
			    $sql.= "VALUES('$order_id', '$item[item_id]', '$item[quantity]', '$price'); ";				
				//echo $sql;		
				$result = $modx->dbQuery($sql);							
			}		
			$sql = "DELETE FROM ".$modx->getFullTableName("site_ec_shopping_cart")." "; 
			$sql.= "WHERE customer_id = {$user_id}";					
			$delete = $modx->dbQuery($sql);	
			if ($delete) {
				$this->temp_cart = array();
				$_SESSION['temp_cart'] = array();
				$this->cart = array();
			}			
			
			$_SESSION['user_order_id'] = $order_id;
			
			if (@$payment_method['confirm'] == 1 || $delivery == 'outsea') {
				$confirm = 1;
				$order_confirm = $this->sendOrderDetails($confirm,$order_id);	
				$this->sendOrderDetailsToAdmin($order_id);
				if ($order_confirm) {
					$url = $modx->makeURL($this->params['confirmorderhomeid']);
           			$modx->sendRedirect($url,0,'REDIRECT_HEADER');
				} else {
					$output = str_replace('[+ec.message+]', $this->lang['5'], $messageTpl);
            		return $output;
				}				
			} else {
				$confirm = 0;
				$order_details = $this->sendOrderDetails($confirm,$order_id);	
				if ($order_details) {
					$this->sendOrderDetailsToAdmin($order_id);
					$_SESSION['EC_ORDER_DETAILS_EMAILED'] = true;
				}
				session_write_close();
				$url = $modx->makeURL($this->params['checkouthomeid']);
           		$modx->sendRedirect($url,0,'REDIRECT_HEADER');
           		return;
			} 			
			
		} else {
			$output = str_replace('[+ec.message+]', $this->lang['7'], $messageTpl);
            return $output;
		}
		
	}
		
	function changeOrderStatus($order_id, $amount) {
		global $modx;	
		$sql = "UPDATE ".$modx->getFullTableName("site_ec_orders")." SET paid=1,confirmed = 1, paidin='$amount' WHERE id='$order_id' LIMIT 1";
		$rs = $modx->dbQuery($sql);		
		if (!$rs) return false;
		else return true;		
	}
	
function buildPaymentTypes($id) {
		global $modx;
		
		$outerTpl = $this->getTemplate($this->templates['cartPaymentOuterTpl']);
		$rowTpl = $this->getTemplate($this->templates['cartPaymentRowTpl']);
		$payment_types = $this->getPaymentTypes();		
		$_rows = '';	
		$i = 0; 
		$first = false;
		
		if (count($payment_types) > 0) {						
			foreach($payment_types as $v => $item) {	
				if ((isset($_POST['order']) && $_POST['order']['region'] != '18825') && $item['id'] == 7) {
					if ($first == false) $i = 0;
					continue;
				}
				$_rowTpl = $rowTpl;			
				$i++;
				if ($id == $item['id']) {
					$_rowTpl = str_replace('[+checked+]','checked',$_rowTpl);
				} elseif ($i == 1) {
					$modx->setPlaceholder('ec.default.payment.auto', $item['auto']);	
					$_rowTpl = str_replace('[+checked+]','checked',$_rowTpl);
					$first = true;
				}
				
				foreach($item as $_k => $_v) {		
					$_rowTpl = str_replace('[+'.$_k.'+]', $_v, $_rowTpl);
				}				
				$_rows .= $_rowTpl;	
				//echo 	$_rowTpl;
				//exit;						
			}		
			//echo $quantity;
			$output = str_replace('[+ec.wrapper+]',$_rows, $outerTpl);		
		} else {			
			$output = $this->lang[0];
		}
		
		return $output;
	}	
	
	function getPaymentTypes() {
		global $modx;
		$resourceArray = array();$tempResults =  array();
		$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_payment_methods")." WHERE active = 1 ";
	    $sql.= "GROUP BY id ORDER BY listindex,id; ";
		//run the query
		//echo $sql;		
		$result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);
		$rows = false;
		for($i=0;$i<$numResults;$i++)  {
			$rows[] = $modx->fetchRow($result);			
		}		
		return $rows;
	}
	
	function getDiscounts() {
		global $modx;
		$tempResults =  array();
		$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_discounts")." WHERE active = 1 ";
	    $sql.= "ORDER BY id; ";				
		$result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);
		$rows = false;
		for($i=0;$i<$numResults;$i++)  {
			$rows[] = $modx->fetchRow($result);			
		}				
		return $rows;
		
	}
	
	function getPaymentType($id) {
		global $modx;
		$resourceArray = array();$tempResults =  array();
		$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_payment_methods")." ";
	    $sql.= "WHERE id = '$id' LIMIT 1;";	    
		//run the query
		//echo $sql;		
		$rs = mysql_query($sql);
		$number_rs = mysql_num_rows($rs);
		if ($number_rs == 1) {			
			$row = mysql_fetch_assoc($rs);			
			$row['params'] = $this->parseProperties1($row['params']);
			return $row;
		}
		else return false;
	}
	
	
	function parseProperties($propertyString) {
        $parameter = array();
        $parameter_ = array();        
        if (!empty ($propertyString)) {
        	$rows= explode("#", $propertyString);
        	if (!empty($rows))         	
        	foreach ($rows as $k => $row) {
        		if (empty($row)) continue; 
	            $tmpParams= explode("&", $row);
	            for ($x= 0; $x < count($tmpParams); $x++) {
	                if (strpos($tmpParams[$x], '=', 0)) {
	                    $pTmp= explode("=", $tmpParams[$x]);
	                    $pvTmp= explode(";", trim($pTmp[1]));
	                    if ($pvTmp[1] == 'list' && $pvTmp[3] != "")
	                        $parameter[]= $pvTmp[3]; //list default
	                    else
	                        if ($pvTmp[1] != 'list' && $pvTmp[2] != "")
	                            $parameter[]= $pvTmp[2];
	                }
	            }	            
	            $parameter_[] = $parameter; 
	            $parameter = array();
        	}
        }        
        return $parameter_;
    }
    
    function parseProperties1($propertyString) {
        $parameter= array ();
        if (!empty ($propertyString)) {
            $tmpParams= explode("&", $propertyString);
            for ($x= 0; $x < count($tmpParams); $x++) {
                if (strpos($tmpParams[$x], '=', 0)) {
                    $pTmp= explode("=", $tmpParams[$x]);
                    $pvTmp= explode(";", trim($pTmp[1]));
                    if ($pvTmp[1] == 'list' && $pvTmp[3] != "")
                        $parameter[trim($pTmp[0])]= $pvTmp[3]; //list default
                    else
                        if ($pvTmp[1] != 'list' && $pvTmp[2] != "")
                            $parameter[trim($pTmp[0])]= $pvTmp[2];
                }
            }
        }
        return $parameter;
    }
    
	// ---------------------------------------------------
	// Function: appendTV taken from Ditto (thanks Mark)
	// Apeend a TV to the documents array
	// ---------------------------------------------------	
	//Get all of the documents from the database
	function getCartData() {
		global $modx;
		$user_id = $this->user['id'];
		$resourceArray = array();
		$tempResults =  array();		
		$fields = "shc.*,eci.*,sp.weight as pack_weight";		
		if ($user_id) {
		    //Get the table names
		    if (strtolower($this->config['sort']) == 'random') {
				$sort = 'rand()';
				$dir = '';
			} else {
				// modify field names to use  table reference
				$sort = $this->params['sort'];				
				$dir = $this->params['dir'];
			}
				// build query
		    $sql = "SELECT {$fields} FROM ".$modx->getFullTableName("site_ec_shopping_cart")." shc INNER JOIN ";
		    $sql.= $modx->getFullTableName("site_ec_items")." eci  ON shc.item_id = eci.id INNER JOIN "; 
		    $sql.= $modx->getFullTableName("site_ec_packs")." sp ON eci.pack_id = sp.id ";
		    $sql.= "WHERE shc.customer_id = {$user_id} AND published=1 AND deleted=0 "; 
		    $sql.= "GROUP BY eci.id ".(!empty($sort) ? "ORDER BY {$sort} {$dir}" : " ");	
		    	
			$result = $modx->dbQuery($sql);	        
			$numResults = @$modx->recordCount($result);
			$resultIds = array();
			//loop through the results
			for($i=0;$i<$numResults;$i++)  {
				$_item = array();
				$tempDocInfo = $modx->fetchRow($result);
				$tempDocInfo['fretail_price'] = money1($tempDocInfo['retail_price']); 
				$tempDocInfo['fmdealer_price'] = money1($tempDocInfo['mdealer_price']);
				$tempDocInfo['fdealer_price'] = money1($tempDocInfo['dealer_price']);
				$tempDocInfo['fsku'] = quantity($tempDocInfo['sku']);
				$resultIds[] = $tempDocInfo['id'];							
				$tempResults[$tempDocInfo['id']] = $tempDocInfo;				
		    }		   
			//Process the tvs			
			$resourceArray = $this->appendTVs($tempResults,$resultIds);
		} else 	$resourceArray = false;
		//return final docs
        return $resourceArray;
	}	
	
	function getTempCartData() {
		global $modx;
		$resourceArray = array();$tempResults =  array();
		$fields = "*";
		$temp_cart_session = @$_SESSION['temp_cart'];			
		//var_dump($temp_cart_session);
		//exit;
		if (count($temp_cart_session)>0) {
			foreach ($temp_cart_session as $item) {
				$itemids[] = $item['item_id']; 
			}
			$itemids = implode(',',$itemids);
		    //Get the table names
		    if (strtolower($this->config['sort']) == 'random') {
				$sort = 'rand()';
				$dir = '';
			} else {
				// modify field names to use  table reference
				$sort = $this->params['sort'];				
				$dir = $this->params['dir'];
			}
				// build query
		    $sql = "SELECT {$fields} FROM ".$modx->getFullTableName("site_ec_items")." "; 
		    $sql.= "WHERE id IN ({$itemids}) AND published=1 AND deleted=0"; 
		    $result = $modx->dbQuery($sql);	        
			$numResults = @$modx->recordCount($result);
			$resultIds = array();
			//loop through the results
			for($i=0;$i<$numResults;$i++)  {
				$_item = array();
				$tempDocInfo = $modx->fetchRow($result);
				$tempDocInfo['fretail_price'] = money1($tempDocInfo['retail_price']); 
				$tempDocInfo['fdealer_price'] = money1($tempDocInfo['dealer_price']);
				$tempDocInfo['fsku'] = quantity($tempDocInfo['sku']);
				$tempDocInfo['item_id'] = $tempDocInfo['id'];
				$tempDocInfo['quantity'] = $temp_cart_session[$tempDocInfo['id']]['quantity'];				
				$resultIds[] = $tempDocInfo['id'];							
				$tempResults[$tempDocInfo['id']] = $tempDocInfo;				
		    }
		    //var_dump($tempResults);
			//Process the tvs			
			//var_dump($resultIds);
			//exit;
			$resourceArray = $this->appendTVs($tempResults,$resultIds);
		} 
		//return final docs
        return $resourceArray;
	}	
		
	function appendTVs($tempResults,$docIDs){
		global $modx;		
		if (implode($docIDs,",") == '') return $tempResults;
		$baspath= $modx->config["base_path"] . "manager/includes";
	    include_once $baspath . "/tmplvars.format.inc.php";
	    include_once $baspath . "/tmplvars.commands.inc.php";
		$tb1 = $modx->getFullTableName("site_tmplvar_ec_itemvalues");		
		$tb2 = $modx->getFullTableName("site_tmplvars");		
		$query = "SELECT stv.name,stc.tmplvarid,stc.itemid,stv.type,stv.display,stv.display_params,stc.value";
		$query .= " FROM ".$tb1." stc LEFT JOIN ".$tb2." stv ON stv.id=stc.tmplvarid  ";
		$query .= " WHERE stc.itemid IN (".implode($docIDs,",").")";
		$rs = $modx->db->query($query);
		$tot = $modx->db->getRecordCount($rs);
		$tvlist = $this->getTVList();		
		foreach ($tvlist as $tv) {
			foreach ($tempResults as $id => $item) {
				$tempResults[$id][$tv] = '';
			}
		}		
		$resourceArray = $tempResults;		
		for($i=0;$i<$tot;$i++)  {			
			$row = @$modx->fetchRow($rs);
			if (!empty($row['value']))
			$resourceArray[$row['itemid']][$row['name']] = getTVDisplayFormat($row['name'], $row['value'], $row['display'], $row['display_params'], $row['type'],$row['itemid']);   
			else 
			$resourceArray[$row['itemid']][$row['name']] = getTVDisplayFormat($row['name'], $row['default_text'], $row['display'], $row['display_params'], $row['type'],$row['itemid']);	
			$tv_names[$row['itemid']][] = $row['name'];				
		}		
		
		return $resourceArray;
	}	
	// ---------------------------------------------------
	// Get a list of all available TVs
	// ---------------------------------------------------		
	function getTVListOf() {
		global $modx;
		$table = $modx->getFullTableName("site_tmplvars");
		$tvs = $modx->db->select("name", $table);
			// TODO: make it so that it only pulls those that apply to the current template
		$dbfields = array();
		while ($dbfield = $modx->db->getRow($tvs))
			$dbfields[] = $dbfield['name'];
		return $dbfields;
	}	
	//debugging to check for valid chunks
    function getTemplate($v) {
        global $modx;		
		$template = $this->fetch($v);
        return $template; 				
    }
	//if (!empty($nonZCartFields)) {
	//	$nonZCartFields = array_unique($nonZCartFields);
	function setTVList() {
		$allTvars = array();
		$allTvars = $this->getTVList();
		foreach ($allTvars as $field) {
			$this->placeHolders['tvs'][] = "[+{$field}+]";
			$this->tvList[] = $field;
		}		
    }
	// ---------------------------------------------------
	// Function: getTVList
	// Get a list of all available TVs
	// ---------------------------------------------------		
	function getTVList() {
		global $modx;
		$table = $modx->getFullTableName("site_tmplvars");
		$tvs = $modx->db->select("name", $table);
		// TODO: make it so that it only pulls those that apply to the current template
		$dbfields = array();
		while ($dbfield = $modx->db->getRow($tvs))
			$dbfields[] = $dbfield['name'];
		return $dbfields;
	}
	function fetch($tpl){
		// based on version by Doze at http://modxcms.com/forums/index.php/topic,5344.msg41096.html#msg41096
		global $modx;
		$template = "";
		if ($modx->getChunk($tpl) != "") {
			$template = $modx->getChunk($tpl);
		} else if(substr($tpl, 0, 6) == "@FILE:") {
			$template = $this->get_file_contents(substr($tpl, 6));
		} else if(substr($tpl, 0, 6) == "@CODE:") {
			$template = substr($tpl, 6);
		} else {
			$template = FALSE;
		}
		return $template;
	}

	function get_file_contents($filename) {
		// Function written at http://www.nutt.net/2006/07/08/file_get_contents-function-for-php-4/#more-210
		// Returns the contents of file name passed
		if (!function_exists('file_get_contents')) {
			$fhandle = fopen($filename, "r");
			$fcontents = fread($fhandle, filesize($filename));
			fclose($fhandle);
		} else	{
			$fcontents = file_get_contents($filename);
		}
		return $fcontents;
	}
	
	function findTemplateVars($tpl) {
		preg_match_all('~\[\+(.*?)\+\]~', $tpl, $matches);
		$cnt = count($matches[1]);
				
		$tvnames = array ();
		for ($i = 0; $i < $cnt; $i++) {
			if (strpos($matches[1][$i], "ec.") === FALSE) {
				$tvnames[] =  $matches[1][$i];
			}
		}

		if (count($tvnames) >= 1) {
			return array_unique($tvnames);
		} else {
			return false;
		}
	}
	
	function paginate($start, $stop, $total, $pagerlinkcount, $summarize, $paginateAlwaysShowLinks, $tplPaginateNext, $tplPaginatePrevious,$paginateSplitterCharacter) {
		global $modx;
		if ($stop == 0 || $total == 0 || $summarize==0) {
			return false;
		}
		
		$next = $start + $summarize;
		$nextlink = "<a href='".$this->buildURL("start=$next")."'>" . $tplPaginateNext . "</a>";
		$previous = $start - $summarize;
		$previouslink = "<a href='".$this->buildURL("start=$previous")."'>" . $tplPaginatePrevious . "</a>";
		$limten = $summarize + $start;
		if ($paginateAlwaysShowLinks == 1) {
			$previousplaceholder = "<span class='ditto_off'>" . $tplPaginatePrevious . "</span>";
			$nextplaceholder = "<span class='ditto_off'>" . $tplPaginateNext . "</span>";
		} else {
			$previousplaceholder = "";
			$nextplaceholder = "";
		}
		$split = "";
		if ($previous > -1 && $next < $total)
			$split = $paginateSplitterCharacter;
		if ($previous > -1)
			$previousplaceholder = $previouslink;
		if ($next < $total)
			$nextplaceholder = $nextlink;
		if ($start < $total)
			$stop = $limten;
		if ($limten > $total) {
			$limiter = $total;
		} else {
			$limiter = $limten;
		}
				
		$totalpages = ceil($total / $summarize);
		
		if ($pagerlinkcount >= $totalpages) {
			for ($x = 0; $x <= $totalpages -1; $x++) {
				$inc = $x * $summarize;
				$display = $x +1;
				if ($inc != $start) {
					$pages .= "<a class=\"ditto_page\" href='".$this->buildURL("start=$inc")."'>$display</a>";
				} else {
					$modx->setPlaceholder($dittoID."currentPage", $display);
					$pages .= "<span class=\"ditto_currentpage\">$display</span>";
				}
			}
		} else {
			
			$side = ($pagerlinkcount-1)/2;
			$curpage = ceil($start / $summarize)+1;	 
			
			
					
			if (($curpage + $side) <= $totalpages && ($curpage - $side) >= 1) {
				$from = $curpage-$side-1;
				$till = $curpage+$side-1;
			} elseif (($curpage + $side) > $totalpages) {
				$from = $curpage-$side-(($curpage + $side)-($totalpages - 1));
				$till = $totalpages - 1;
			} else {
				$from = 0;
				$till = $pagerlinkcount-1;
			}
			
			for ($x = $from; $x <= $till; $x++) {
				$inc = $x * $summarize;
				$display = $x +1;
				if ($inc != $start) {
					$pages .= "<a class=\"ditto_page\" href='".$this->buildURL("start=$inc")."'>$display</a>";
				} else {
					$modx->setPlaceholder($dittoID."currentPage", $display);
					$pages .= "<span class=\"ditto_currentpage\">$display</span>";
				}
			}			
		}	
		
		$pager["next"] = $nextplaceholder;
		$pager["previous"] = $previousplaceholder;
		$pager["splitter"] = $split;
		$pager["start"] = $start + 1;
		$pager["urlStart"] = $start;
		$pager["stop"] = $limiter;
		$pager["total"] = $total;
		$pager["pages"] = $pages;
		$pager["perPage"] = $summarize;
		$pager["totalPages"] = $totalpages;
		$this->pager = $pager;
	}
	
	function buildURL($args,$id=false,$dittoIdentifier=false) {
		global $modx, $dittoID;
			$dittoID = ($dittoIdentifier !== false) ? $dittoIdentifier : $dittoID;
			$query = array();
			foreach ($_GET as $param=>$value) {
				if ($param != 'id' && $param != 'q') {
					$query[htmlspecialchars($param, ENT_QUOTES)] = htmlspecialchars($value, ENT_QUOTES);					
				}
			}
			if (!is_array($args)) {
				$args = explode("&",$args);
				foreach ($args as $arg) {
					$arg = explode("=",$arg);
					$query[$dittoID.$arg[0]] = urlencode(trim($arg[1]));
				}
			} else {
				foreach ($args as $name=>$value) {
					$query[$dittoID.$name] = urlencode(trim($value));
				}
			}
			$queryString = "";
			foreach ($query as $param=>$value) {
				$queryString .= '&'.$param.'='.(is_array($value) ? implode(",",$value) : $value);
			}
			$cID = ($id !== false) ? $id : $modx->documentObject['id'];
			$url = $modx->makeURL(trim($cID), '', $queryString);
			return str_replace("&","&amp;",$url);
	}
	
	function relToAbs($text, $base) {
		return preg_replace('#(href|src)="([^:"]*)(?:")#','$1="'.$base.'$2"',$text);
	}
	
	
}

?>