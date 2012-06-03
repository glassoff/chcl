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
	
	var $defaultRegion = 18825;
	
	function eCart() {
		global $modx;
		$this->user = $modx->getWebUserInfo($modx->getLoginUserID('web'));			
	}
	
  	function init() {
		global $modx;					
		$this->setTVList();		
		$this->loadConfig();
		##
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
	
	##
	function replaceEmptyInOrder($order){
		foreach($order as $k => $v){
			if (!$v)
				$order[$k] = 'не указан';
		}
		return $order;
	}
	
	function sendOrderDetails($confirm, $order_id) {
		global $modx,$base_path;

			
		if (!$this->user) return $this->lang[5];		
		
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
			$order['payment_name'] = '';
			$order['delivery_amount'] = '';							
		}
		
		$confirm_key = $order['confirm_key'];		
		$confirm_link = $modx->makeUrl($confirmorderhomeid).'?user_order_id='.$order_id.'&confirm='.$confirm_key;		
		$output = str_replace('[+confirm_link+]', $confirm_link, $output);
		
		$checkout_link = $modx->makeUrl($checkouthomeid).'?user_order_id='.$order_id;		
		$output = str_replace('[+checkout_link+]', $checkout_link, $output);
		
		$order['cart_amount'] = $order['amount'] - $order['delivery_amount'];
		$order['order_date'] = datetime($order['order_date']);
		
		##
		$order = $this->replaceEmptyInOrder($order);
		
		foreach($order as $k => $v) {
			$output = str_replace('[+'.$k.'+]', $v, $output);
		} 		
		
		$order_items = $this->getOrderItemsInfo($order_id);
		$items_list = "<ul>";
		
		foreach($order_items as $k => $item) {
			$price = money1($item['price']).' '.$this->lang['currency'];
			$quantity = quantity1($item['quantity'], $item);//.' '.$this->lang['quantity'];
			$items_list .= "<li><a target=\"_blank\" href=\"".$modx->makeUrl($itemhomeid)."?id=".$item[item_id]."\">".$item['itemtitle']."</a>, ".$item['color_z'].", ".$item['size_z']." - ".$price." - ".$quantity."</li>";
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

		## only opt
		$username = $this->user['type'] . " " . $this->user['company']; 
		
		$output = str_replace('[+uname+]', $username, $output);		
		
		$order = $this->getOrderInfo($order_id);//print_r($order);die();
		
		
		if ($order['payment_type'] == 8) {
			$bank_account = $this->config['ec_email_bank_account'];
			$output = str_replace('[+bank_account+]', $bank_account, $output);				
		} else $output = str_replace('[+bank_account+]', '', $output);
		
		if ($order['delivery_type'] == 'outsea') {
			$order['payment_name'] = '';
			$order['delivery_amount'] = '';							
		}
		
		
		$order['cart_amount'] = $order['amount'] - $order['delivery_amount'];
		$order['order_date'] = datetime($order['order_date']);
		
		##
		$order = $this->replaceEmptyInOrder($order);
		foreach($order as $k => $v) {
			$output = str_replace('[+'.$k.'+]', $v, $output);
		} 		
		
		##
		foreach($this->user as $k => $v) {
			if($k=='clientfile' && $v)
				$v = '<a href="' . $modx->config['site_url'] . $v . '">Посмотреть</a>';
			$output = str_replace('[+user.'.$k.'+]', $v, $output);
		}

		$order_items = $this->getOrderItemsInfo($order_id);
		//print_r($order_items);die();
		$items_list = "<ul>";
		
		foreach($order_items as $k => $item) {
			$price = money1($item['price']).' '.$this->lang['currency'];
			$quantity = quantity1($item['quantity'], $item);//.' '.$this->lang['quantity'];
			$items_list .= "<li>".$item['acc_id']." - <a target=\"_blank\" href=\"".$modx->makeUrl($itemhomeid)."?id=".$item[item_id]."\">".$item['itemtitle']."</a>, ".$item['color_z'].", ".$item['size_z']." - ".$price." - ".$quantity."</li>";
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
		
		$email = $this->config['email_sender'];	
		##
		$emails = explode(",", $email);
		
		$fullname = $this->user['fname'].' '. $this->user['sname'].' '. $this->user['lname'];
		foreach($emails as $email_item){
			$Confirm->AddAddress($email_item, $fullname);
		} 
				 
		$Confirm->IsHTML(true);		
		$modx->logEvent(29, 2, 'Send notification to admin (order #'.$order_id.')', 'ORDER #'.$order_id);
		if ($Confirm->Send()) {
			return true;			 
		} else {
			$modx->logEvent(29, 2, 'Send email to admin ERROR', 'ORDER #'.$order_id);
			return $this->lang[5];
		}
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
			$quantity = quantity1($item['quantity'], $item);//.' '.$this->lang['quantity'];
			$items_list .= "<li><a target=\"_blank\" href=\"".$modx->makeUrl($itemhomeid)."?id=".$item[item_id]."\">".$item['itemtitle']."</a>, ".$item['color_z'].", ".$item['size_z']." - ".$price." - ".$quantity."</li>";
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
		    ##
			/*if (!$modx->getLoginUserID('web')) {
				$_SESSION['AFTER_LOGIN_GO_URL'] = $modx->config['server_protocol'].'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];					
				$url = $modx->makeUrl($this->params['mustloginpageid']);				
				//session_write_close();
                $modx->sendRedirect($url,0,'REDIRECT_HEADER'); 
                return;
			} */	
			
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
				<blockquote><b><center>
				<a href="'.MODX_SITE_URL.'/cabinet/confirmorder?user_order_id='.$order_id.'&confirm='.$order1['confirm_key'].'">
				<< Также можно подтвердить  свой заказ здесь. Переход по этой ссылке означает автоматическое подтверждение заказа >>> </a>
				<br><br> << В этом случае подтверждать заказ через емейл не нужно >> </center> 
				
				<br><br><br><br>
				<a href="http://www.chcl.ru">&#8592; Вернуться на главную страницу</a>
				</b>
				<blockquote>';
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
	
	
	function getRegion_rate_zone($id)
	{
		global $modx;		
		$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_regions") . " WHERE id='$id' ";
		$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
		if ($rs && mysql_num_rows($rs) == 1) {
			$row = mysql_fetch_assoc($rs);
			return $row['rate_zone'];
		} else return 0;	
	}
	
	function getRegionRate($region_id){
		global $modx;		
		$postcode = $this->getRegion($region_id);
		$zone = $this->getRegion_rate_zone($region_id);
		
	if ($zone<1) $zone = 1;
		
	/*		$sql = "SELECT rate FROM ".$modx->getFullTableName("site_ec_cities")."sc ";
		$sql.= "INNER JOIN ".$modx->getFullTableName("site_ec_shipping_rates")."shr ON shr.zone = sc.rate_zone ";
	    $sql.= "WHERE sc.id = {$city_id} LIMIT 1;";  */
		
	    $sql = "SELECT rate FROM ".$modx->getFullTableName("site_ec_shipping_rates")." ";
	    $sql.= "WHERE zone = $zone LIMIT 1;";
		
	    $rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
		if ($rs && mysql_num_rows($rs) == 1) {
			$row = mysql_fetch_assoc($rs);
			return $row['rate'];
		} else return 0;	
			
	}
	
	function addItemToCart() {
		global $modx;		
		if ($this->user == false) {
			$this->addItemToTempCart();	
			return;
		}		
		//die('notemp');
		$user_id = $this->user['id'];
		$items = array();
		//$items[] = $_REQUEST['item'];
		
		$type = $_REQUEST['type'];
		
		foreach($_REQUEST['items'] as $item){
			if ($item['quantity'] > 0)
				$items[] = $item;
		}
				
		if (isset($_REQUEST['accessories'])) {			
			$accessories = $_REQUEST['accessories'];
			foreach($accessories as $accessorie) {
				if (isset($accessorie['checked']) && intval($accessorie['checked']) === 1) 
				$items[] = $accessorie;
			}			
		}		
		//print_r($items);die();
		foreach($items as $item) {		
			$quantity = intval($item['quantity']);
			$item_id = intval($item['id']);
			
            $color_z= iconv("UTF-8", "WINDOWS-1251", $item['color_z']);	
			$size_z= iconv("UTF-8", "WINDOWS-1251", $item['size_z']);

			// check exist
			$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_shopping_cart")." ";
			$sql.= "WHERE customer_id = {$user_id} AND item_id = '{$item_id}' AND color_z = '{$color_z}' AND size_z = '{$size_z}' AND type = '{$type}' LIMIT 1;";
			//die($sql);
			$result = $modx->dbQuery($sql);	        
			$numResults = @$modx->recordCount($result);	
			//die('hello');		
			if ($numResults == 1) {
				//die('exist');
				$sql = "UPDATE ".$modx->getFullTableName("site_ec_shopping_cart")." ";
				$sql.= "SET quantity = quantity + {$quantity} WHERE customer_id = {$user_id} AND item_id = {$item_id} AND color_z = '{$color_z}' AND size_z = '{$size_z}' LIMIT 1;";
			} else {
				//die('no exist');
				$sql = "INSERT INTO ".$modx->getFullTableName("site_ec_shopping_cart")."(customer_id,item_id,quantity,color_z,size_z,type) ";
				$sql.= "VALUES($user_id,$item_id,$quantity,'$color_z','$size_z','$type');";					
			}		
			$rs = mysql_query($sql);
			if(!$rs) {
				echo "error";
			}
			//die($sql);	
			//echo $sql;
			//exit;
		}	
		$this->cart = $this->getCartData();
		//print_r($this->cart);die();		
		return;		 
	}
	
	function addItemToTempCart() {
		global $modx;		
		$items = array();
		//$items[] = $_REQUEST['item'];
		
		$type = $_REQUEST['type'];
		
		foreach($_REQUEST['items'] as $item){
			if ($item['quantity'] > 0)
				$items[] = $item;
		}
		//print_r($items);die();
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
		
            $color_z= iconv("UTF-8", "WINDOWS-1251", $item['color_z']);	
			$size_z= iconv("UTF-8", "WINDOWS-1251", $item['size_z']);			
			$exists = false;
			if (count($_SESSION['temp_cart'])>0)
			foreach($_SESSION['temp_cart'] as $k => $item) {
				##
				if ($item['item_id'] == $item_id && $item['color_z'] == $color_z && $item['size_z'] == $size_z && $item['type'] == $type) {	 
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
				##
				$_SESSION['temp_cart'][] = array(item_id => $item_id, quantity => $quantity, size_z=>$size_z, color_z => $color_z, type => $type); 
			
			}
			$this->temp_cart = $this->getTempCartData();
		}	
			
		return;		 
	}
	
	function moveTempToCart() {
		global $modx;
		$temp_cart_ = array();
		$user_id = $this->user['id'];	

		##
		//clean exist cart
		if($_POST['service']=='loginAndOrder' && $user_id){
			$sql = "DELETE FROM ".$modx->getFullTableName("site_ec_shopping_cart")." WHERE customer_id = {$user_id}";
			$result = $modx->dbQuery($sql);
		}
		
		
		$temp_cart_session = $_SESSION['temp_cart'];
		if (count($temp_cart_session) > 0 && !empty($user_id)) {	
			//print_r($temp_cart_session);die();							
			foreach($temp_cart_session as $item) {		
				$quantity = intval($item['quantity']);
				$item_id = intval($item['item_id']);	
			
			     $color_z= $item['color_z'];	
		         	$size_z= $item['size_z'];
		         	
		         $type = $item['type'];
		         	
		        // check exist
				$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_shopping_cart")." ";
				$sql.= "WHERE customer_id = {$user_id} AND item_id = {$item_id} AND color_z = '{$color_z}' AND size_z = '{$size_z}' AND type = '{$type}' LIMIT 1;";
				$result = $modx->dbQuery($sql);	        
				$numResults = @$modx->recordCount($result);			
				if ($numResults == 1) {
					$sql = "UPDATE ".$modx->getFullTableName("site_ec_shopping_cart")." ";
					$sql.= "SET quantity = quantity + {$quantity} WHERE customer_id = {$user_id} AND item_id = {$item_id} AND color_z = '{$color_z}' AND size_z = '{$size_z}' LIMIT 1;";
				} else {
					$sql = "INSERT INTO ".$modx->getFullTableName("site_ec_shopping_cart")."(customer_id,item_id,quantity, size_z, color_z, type) ";
					$sql.= "VALUES($user_id,$item_id,$quantity,'$size_z', '$color_z', '$type');";		
					
                 	
					$temp_cart_[$item_id] = array(item_id => $item_id, quantity => $quantity, size_z=>'$size_z', color_z => $color_z, type => $type);
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
		##
			
	}
	
	function updateCart() {
		global $modx;
		$temp_cart_ = array();
		$user_id = $this->user['id'];		
		if (isset($_POST['cart']) && $user_id) {			
			$items = $_POST['cart'];			
			foreach($items as $incart_id => $item) {					
				$item_id = intval($item['id']);
				$quantity = intval($item['quantity']);
				$color_z= iconv("UTF-8", "WINDOWS-1251", $item['color_z']);	
			   $size_z= iconv("UTF-8", "WINDOWS-1251", $item['size_z']);								
				if ($item['remove'] == '1') {					
					$sql = "DELETE FROM ".$modx->getFullTableName("site_ec_shopping_cart")." "; 
					$sql.= "WHERE customer_id = {$user_id} AND item_id = {$item_id} AND id = {$incart_id} LIMIT 1;";												
				} else {
					$quantity = intval($item['quantity']);
					if ($quantity == 0) {
						$sql = "DELETE FROM ".$modx->getFullTableName("site_ec_shopping_cart")." "; 
						$sql.= "WHERE customer_id = {$user_id} AND item_id = {$item_id} AND id = {$incart_id} LIMIT 1;";		
					} else {
						$sql = "UPDATE ".$modx->getFullTableName("site_ec_shopping_cart")." ";
						$sql.= "SET quantity = {$quantity}, color_z = '{$color_z}', size_z = '{$size_z}' WHERE customer_id = {$user_id} AND item_id = {$item_id} AND id = {$incart_id} LIMIT 1;";						}
				}
				//die($sql);				
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

		if (isset($_POST['cart'])) {
			$items = $_POST['cart'];
			//print_r($items);			
			foreach($items as $id => $item) {	
				$item_id = (int)$item['id'];
				$quantity = (int)$item['quantity'];
				$color_z= iconv("UTF-8", "WINDOWS-1251", $item['color_z']);	
			   $size_z= iconv("UTF-8", "WINDOWS-1251", $item['size_z']);					
				if ($item['remove'] != '1' && $quantity != 0) {	
									
					$_SESSION['temp_cart'][$id]["item_id"] = $item_id;
					$_SESSION['temp_cart'][$id]["quantity"] = $quantity;
					$_SESSION['temp_cart'][$id]["color_z"] = $color_z;
					$_SESSION['temp_cart'][$id]["size_z"] = $size_z;
					
					//array("item_id" => $item_id, "quantity" => $quantity/*, "size_z"=>$size_z, "color_z" => $color_z*/);					
				}	
				else{
					unset($_SESSION['temp_cart'][$id]);
				}			
			}
			
			//reindex
			$_SESSION['temp_cart'] = array_values($_SESSION['temp_cart']);
			
			$this->temp_cart = $this->getTempCartData();				
		}	
	}
	
	function price($item,$quantity,$type) {
			$price = $item['retail_price'];
			
			//$opt = $this->user['opt'];	
			
		if ($type=='opt' || $type=='package') {
			$price = $item['price_opt'];
			##
			if($item['package_items'] > 0 && $item['package_price'] > 0){
				$price = $item['package_price'];	
			}			
		}  else {					
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
				$price = $this->price($item,$item['quantity'],$item['type']);
				$total_amount += $item['quantity']*$price;
				$total_quantity += $item['quantity']; 			
			}				 	
		}					
		
		$modx->setPlaceholder('ec.totalquantity', $total_quantity);
		$modx->setPlaceholder('ec.totalamount', $total_amount);
		$modx->setPlaceholder('ec.discount', $discount);
		return $sideBarCartTpl;
	}
	
	function getDiscount($cart_amount){
		if ($cart_amount>=50000 && $cart_amount<200000) 
			return 3;
		elseif($cart_amount>=200000 && $cart_amount<500000) 
			return 6;
		elseif($cart_amount>=500000 && $cart_amount<1000000)
			return 10;
		elseif($cart_amount>=1000000)  
			return 15;
		return 0;
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
					$price = $this->price($item,$item['quantity'],$item['type']);
					$cart_amount += $item['quantity']*$price;
					$weight += $item['quantity']*($item['pack_weight']);
					$quantity += $item['quantity'];	
				}		
							
				// discount process						
				$discount = $user_info['bonus'];
				##
				$discount = $this->getDiscount($cart_amount);
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
					$price = $this->price($item,$item['quantity'],$item['type']);
					$cart_amount += $item['quantity']*$price;					
					$quantity += $item['quantity'];	
				}					
				// discount process
				$discount = 0;
				##
				$discount = $this->getDiscount($cart_amount); 
				$dcart_amount = $cart_amount-($cart_amount*$discount/100);
				$amount = $shipping_amount+$dcart_amount;	
				return array(amount => $amount, 
							 discount => $discount,
							 cart_amount => $cart_amount,
							 dcart_amount => $dcart_amount,				         
					         quantity => $quantity					        
				            );							
			} else return false;	
		}			 
	}

	function getUserRegion()
	{
		global $modx;
		
		$user_id = $this->user['id'];
		
		$sql44 = "SELECT modx_site_ec_regions.id, modx_site_ec_regions.name, modx_web_user_attributes.internalKey, 
			modx_web_user_attributes.region  
				FROM modx_web_user_attributes, modx_site_ec_regions 
			WHERE internalKey='$user_id' and  modx_site_ec_regions.name=modx_web_user_attributes.region LIMIT 1";	
			
		$result = $modx->db->query($sql44);
		
		return $modx->db->getRow($result);
	}
	
	/**
	 * Данные для формы заказа по умолчанию (при открытии формы)
	 */
	function getDefaultOrderData()
	{
		$order = array();

		$user_id = $this->user['id'];
		$user_info = $this->user;
		
		$last_order = $this->getLastOrder($user_id);
		
		if ($last_order) 
		{
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
			$order['phone'] = $last_order['customer_phone'];
			
			$order['region'] = $this->getRegionIdByName($last_order['customer_region']);
			
		} else {
			//default values
			$order['to'] = 'russia';
			
			$userRegion = $this->getUserRegion();
			if($userRegion){
				$order['region'] = $userRegion['id'];
			}
			else{
				$order['region'] = $this->defaultRegion;
			}
			
			$order['state'] = $user_info['town'];
			$order['postcode1'] = $user_info['postcode1'];
			$order['street'] = $user_info['street'];
			$order['dom'] = $user_info['house'];
			$order['korpus'] = $user_info['korpus'];
			$order['kvartira'] = $user_info['kvartira'];
			$order['phone'] = $user_info['phone'];
							
		}

		$order['sname'] = $user_info['sname'];
						
		return $order;
	}
	
	/**
	 * Order page
	 */
	function buildOrderPage() 
	{
		global $modx;
		$output = '';
		$item = array();

		$required_fields = false;
		$required_formcode = false;
		$wrong_formcode = false;	
		
		$required_field_phone = false;
		$required_field_zak = false;
		
		$user_id = $this->user['id'];
		
		$order = array();
		
		$errorFields = array();
		
		//XXX
		if($_SERVER['HTTP_X_REQUESTED_WITH']=='XMLHttpRequest' && is_array($_POST['order'])){
			foreach($_POST['order'] as $k => $v){
				$_POST['order'][$k] = $this->fromUTF($v);	
			}
		}
		
		//оформление заказа
		if (isset($_POST['placeorder']) && isset($_POST['order'])) 
		{
			$order = $_POST['order'];
			
			if($order['region']==18825) $order['state'] = 'Москва';
			
			$required = array('region' => 'Регион', 'state' => 'Город', 'shipping' => 'Способ доставки', 'postcode1' => 'Почтовый индекс', 'street' => 'Улица', 'dom' => 'Дом'/*, 'kvartira' => 'Квартира'*/, 'phone' => 'Контактный телефон', 'sname' => 'Контактное лицо');
			
			$order_datas = array_merge($this->getOrderDatas(), $order);
			//получаем доступные способы доставки и текущий способ
			$shippings = $this->getShippings($order_datas);
			$order_datas['shipping_info'] = $shippings['selected'];

			//убираем ненужные обязательные поля для выбранного способа доставки
			if(isset($order_datas['shipping_info']['params']['fields']['exclude']))
			{
				$excludes = (array)$order_datas['shipping_info']['params']['fields']['exclude'];
				foreach($excludes as $exclude)
				{
					unset($required[$exclude]);
				}
			} 
							
			foreach($required as $fieldName => $fieldDescription)
			{
				if(!$order[$fieldName])
				{
					$errorFields[$fieldName] = $fieldDescription;
				}	
			}	
			
			if (!count($errorFields)) {
		    	//$region_id = $order['region']; 
				//$rate = $this->getRegionRate($region_id);	
				//$km=$order['km'];		
	    		//$shipping = 0;					
	    		//$delivery = $order['shipping'];	
	    			    		
    			$payment_method = $this->getPaymentType($order['payment_method_id']);
				$payment_mode = $payment_method['mode'];
				
    			$deliveryCostInfo = $this->getOrderShippingAmount($order_datas);
				$delivery_amount = $deliveryCostInfo['cost']; 
				$total_amount = $order_datas['dcart_amount'] + $delivery_amount;			  		
				$order_datas['delivery_amount'] = $delivery_amount;
				$order_datas['total_amount'] = $total_amount;			
	    			    			
	    		$this->placeOrder($order_datas);	
			} else {
				$required_fields = true;					
			}										
		}
		
		$messageTpl = $this->getTemplate($this->templates['message1Tpl']);		
		$outerTpl = $this->getTemplate($this->templates['cartOrderOuterTpl']);
		$addressTpl = $this->getTemplate($this->templates['cartAddressTpl']);
		$cart = $this->cart;
		
		$pricelimit = false;

		if (is_array($cart) && sizeof($cart) > 0) 
		{
			//что делать после отправки заказа, если нет товаров (не используется)
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
			//
			
			//берем данные для отображения в форме заказа
			if (isset($_POST['order'])) 
			{
				if($_POST['order']['_region']!=-1){
					$_POST['order']['region'] = $_POST['order']['_region'];
				}
				$order = $_POST['order'];
				$to = $order['to'];
				
				$payment_method = $this->getPaymentType($order['payment_method_id']);
				$payment_mode = $payment_method['mode'];					

			} else {
				$order = $this->getDefaultOrderData();
			}

			foreach($order as $k => $v) {
				$modx->setPlaceholder('order.'.$k, $v);
			}
			
			$delivery = $order['shipping'];

			$order_datas = array_merge($this->getOrderDatas(), $order);

			//получаем доступные способы доставки и текущий способ
			$shippings = $this->getShippings($order_datas);
			
			$order_datas['shipping_info'] = $shippings['selected'];
			
			$modx->setPlaceholder('regions.list', $this->getRegionsList($order['region'], array(
				'onchange' => "if(\$('#_region-'+\$(this).val()).length){\$('#_region-'+\$(this).val()).atrr('checked', 'checked')}else{\$('#_region-no').attr('checked', 'checked');}"
			)));	
			$modx->setPlaceholder('shipping.list', $this->getShippingList($shippings['list']));
			
			//$rate = $this->getRegionRate($order['region']);
		    $shipping =1;
			
			if ($shipping == false) {
				$over_weight = '0';
			} else $over_weight = '0';	
			
		    $deliveryCostInfo = $this->getOrderShippingAmount($order_datas); 
			$delivery_amount = $deliveryCostInfo['cost'];
			$total_amount = $order_datas['dcart_amount'] + $delivery_amount;	
						
			foreach($deliveryCostInfo as $k => $v){
				$modx->setPlaceholder('shipping.cost.'.$k, $v);
			}
			
			$output = str_replace('[+ec.cartbody+]',$this->getCartBody(), $outerTpl);
			
			$modx->setPlaceholder('ec.cartquantity', $order_datas['quantity']);
			$modx->setPlaceholder('ec.cartdiscount', $order_datas['discount']);
			//$modx->setPlaceholder('ec.overweight', $over_weight);
			$modx->setPlaceholder('ec.cartamount', $order_datas['cart_amount']);
			//$modx->setPlaceholder('ec.limit', $pricelimit ? $this->getOrderLimit() : 0);
			$modx->setPlaceholder('ec.dcartamount', $order_datas['dcart_amount']);
			$modx->setPlaceholder('ec.shippingamount', money1($delivery_amount));
			$modx->setPlaceholder('ec.totalamount', $total_amount);
			
	
			if (isset($order['payment_method_id'])) {
				$order_payment_id = $order['payment_method_id'];
			} else {
				$order_payment_id = 0;	
			}
		
			
			//$output = str_replace('[+ec.payment.types+]',$this->buildPaymentTypes($order_payment_id), $output);	
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
				$fieldList = implode(',', $errorFields);
				$message = str_replace('[+message+]','<span>Вы оставили незаполненными обязательные поля:</span> ' . '<span <span style="color:red;">'.$fieldList.'</span>', $messageTpl);
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

        foreach($errorFields as $fieldName => $fieldDescription){
        	$output = preg_replace('#<(input|select|textarea)([^>]*)?name="order\['.$fieldName.'\]"([^>]*)?>#si', 
            	'<$1$2 class="invalidField" name="order['.$fieldName.']" $3>', $output);
        }

		return $output;
	}

	/**
	 * Страница оплаты заказа
	 */
	function buildCheckoutPage($order_id)
	{
		global $modx;
		
		$output = '';
		
		$modx->setPlaceholder('order.id', $order_id);
		
		if($_SESSION['just_ordered']==$order_id){
			$modx->setPlaceholder('just_ordered', 1);
			unset($_SESSION['just_ordered']);	
		}
		
		$order = $this->getOrderInfo($order_id);

		if($_REQUEST['payment']){
			$payment_id = $_REQUEST['payment']['id'];
			if($payment_id)
			{
				$item = $this->getPaymentType($payment_id);
				
				$class = $item['class'];
				
				$classname = $class.'Payment';
				$classFile = $classname . '.class.php';
									
				require_once($modx->config['base_path'].'/assets/snippets/ecart/payments/'.$classFile);
				
				$Payment = new $classname($item, $order);	
				
				if($Payment->validateForm($_REQUEST['payment'])){
					$output .= $Payment->postForm($_REQUEST['payment']);
					return $output;	
				}
			} 		
		}
		
		$paymentsList = $this->buildPaymentTypes($payment_id, $order);
		
		$output .= $paymentsList;
		
		return $output;
	}
	
	function getRegionsList($act, $attrs = array())
	{
		global $modx;
		$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_regions") . ' order by listindex, name';
		$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
		$lines = array();
		
		$attrstr = '';
		foreach($attrs as $name => $value){
			$attrstr .= ''.$name.'="'.$value.'" ';
		}
		
		$lines[] = '<select '.$attrstr.' class="reg_field" name="order[region]">';	
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

	/**
	 * Доступные способы доставки
	 */
	function getShippings($order_datas = array())
	{
		global $modx;
		
		//print_r($order_datas);die();
		
		$list = array();
		$shippings = array();
		
		$sql = "SELECT * FROM modx_site_ec_delivery_types WHERE (active = 1) ORDER BY listindex";
		$result = $modx->db->query($sql);
		
		$selected = false;
		while($row = $modx->db->getRow($result))
		{
			$params = json_decode($this->toUTF($row['params']), true);
			
			if(!$params){
				echo ('Ошибка обработки способа доставки '.$row['name']);
			}
			
			$row['params'] = $params;
			
			//print_r($params);die();
			
			if(!$this->filterShipping($params, $order_datas)){
				continue;
			}
			
			if($order_datas['shipping']==$row['id']){
				$row['selected'] = true;
				$selected = $row;
			}
			$shippings[] = $row;
		}
		
		if(!$selected){
			$shippings[0]['selected'] = true;
			$selected = $shippings[0];
		}
		
		return array(
			'list' => $shippings,
			'selected' => $selected 
		);
	}
	
	/**
	 * <Select> доступных способов доставки
	 */
	function getShippingList($shippings = array())
	{
		for($i = 0; $i < count($shippings); $i++)
		{
			$shipping = $shippings[$i];
			$checked = '';
			if($shipping['selected']){
				$checked = 'checked';
			}
			$list[] = '<div class="radio-select"><label><input '.$checked.' type="radio" name="order[shipping]" value="'.$shipping['id'].'" /> '.$shipping['name'].'</label></div>';
		}
		
		return implode("\n", $list);
	}
	
	/**
	 * Фильтрация способов доставки для указанных параметров заказа
	 */
	function filterShipping($shippingParams = array(), $order_datas = array())
	{
		$region = $order_datas['region'];
		$params = $shippingParams['apply'];
		
		//filter regions
		if($params['regions'] && $region){
			if(is_array($params['regions'])){
				if(!in_array($region, $params['regions'])){
					return false;
				}
			}
		}
		if($params['!regions'] && $region){//exclude regions
			if(is_array($params['!regions'])){
				if(in_array($region, $params['!regions'])){
					return false;
				}
			}
		}		
		
		//filter cart amount
		if($params['max_amount'] && $params['max_amount'] < $order_datas['dcart_amount']){
			return false;
		}
		if($params['min_amount'] && $params['min_amount'] > $order_datas['dcart_amount']){
			return false;
		}		
		
		return true;		
	}
	
	/**
	 * Стоимость доставки
	 */
	function getOrderShippingAmount($order_datas = array()) 
	{
		global $modx;
				
		$cost = 0;
		$comment = array();
				
		$shipping = $order_datas['shipping_info'];
		$params = $shipping['params']['cost'];
		
		$baseCost = $params['base'] ? $params['base'] : 0;//базовая цена
		$cost = $baseCost;	
		
		if($params['comment']){
			$comment[] = $params['comment'];
		}
		
		//если стоимость доставки зависит от региона
		$region = $order_datas['region'];
		if($params['regions'] && $region){
			if(is_array($params['regions'])){
				
				$regionCost = $params['regions']['all'];//для всех регионов
				
				if($params['regions'][$region]){
					$regionCost = $params['regions'][$region];
					if(isset($regionCost['add'])){
						$cost += $regionCost['add'];
					}
					if(isset($regionCost['comment'])){
						$comment[] = $regionCost['comment'];
					}
				}
				
				//если стоимость доставки зависит от стоимости заказа
				$amount = $order_datas['dcart_amount'];
				if($regionCost['amount'] && $amount){
					if($regionCost['amount']['>='] && is_array($regionCost['amount']['>='])){
						foreach($regionCost['amount']['>='] as $k => $v){
							if($amount >= $k){
								$cost += $v['add'];
							}
						}
					}
				}
			}
		}

		return array(
			'cost'=>$cost, 
			'comment'=>$this->fromUTF(implode('<br><br>', $comment))
		);
	}	
	
	function fromUTF($s)
	{
		return iconv('utf-8', 'cp1251', $s);
	}
	
	function toUTF($s)
	{
		return iconv('cp1251', 'utf-8', $s);
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
	
	function sortCartData($cart)
	{
		if(!is_array($cart))
		{
			return $cart;	
		}
		
		if(!function_exists(_sortCart))
		{
			function _sortCart($a, $b)
			{
				if($a['type']==$b['type'])
				{
					return 0;
				}
				return $a['type'] == 'retail' ? -1 : 1;		
			}			
		}
		
		uasort($cart, '_sortCart');
		
		return $cart;
	}
	
	/*Страница корзины (без оформления заказа)*/
	function buildPageCart() {
		global $modx;	

		$output = '';
		$messageTpl = $this->getTemplate($this->templates['message1Tpl']);		
		$outerTpl = $this->getTemplate($this->templates['cartOuterTpl']);
		
		$pricelimit = false;
		
		if ($this->cart) $cart = $this->cart;
		else $cart = $this->temp_cart;

		if (count($cart) > 0) {
			
			$output = str_replace('[+ec.cartbody+]',$this->getCartBody(), $outerTpl);	

		
		} else {			
			$message = str_replace('[+message+]',$this->lang[0], $messageTpl);		
			$output = $message;
		}
		return $output;
	}

	/* Таблица содержимого корзины */
	function getCartBody()
	{
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
		//$messageTpl = $this->getTemplate($this->templates['message1Tpl']);		
		$outerTpl = $this->getTemplate('eCartBody'/*$this->templates['cartOuterTpl']*/);//FIXME передавать шаблон через параметры
		$rowTpl = $this->getTemplate($this->templates['cartRowTpl']);
		
		$minAmount = $this->getOrderLimit('retail');
		$pricelimit = 1;
		
		if ($this->cart) $cart = $this->cart;
		else $cart = $this->temp_cart;

		if (count($cart) > 0) {
			$retail_block = 0;
			$opt_block = 0;						
			foreach($cart as $row_id => $item) {
			    $num++;
			    $price = $this->price($item,$item['quantity'], $item['type']);
			    ##
			    $cost = $item['quantity']*$price;
				$amount += $item['quantity']*$price;
				$quantity += $item['quantity'];             				
				$_rowTpl = $rowTpl;
				
				if($item['type']=='retail')
				{
					$retail_block++;
					$opt_block = 0;	
				}
				elseif($item['type']!='retail')
				{
					$opt_block++;	
					$retail_block = 0;
				}			
				
				$_rowTpl = str_replace("[+retail_block+]", $retail_block, $_rowTpl);
				$_rowTpl = str_replace("[+opt_block+]", $opt_block, $_rowTpl);
				
				$_rowTpl = str_replace("[+num+]", $num, $_rowTpl);		
				$_rowTpl = str_replace("[+itemhomeid+]", $this->params['itemhomeid'], $_rowTpl);			
				$_rowTpl = str_replace("[+subtotal+]", money1($item['quantity']*$price), $_rowTpl);	
				$_rowTpl = str_replace("[+price+]", money1($price), $_rowTpl);	
				##
				$_rowTpl = str_replace("[+cost+]", money1($cost), $_rowTpl);	
				$_rowTpl = str_replace("[+row_id+]", $row_id, $_rowTpl);
				
				$color = $item['color'];
				$colors = $color->get();
				
				$c = '<div class="color-item nocolor" title="Выбор цвета недоступен" >Выбор цвета недоступен</div>';
				if(count($colors)){
					$c='<select name="cart['.$row_id.'][color_z]">'; 
	               	foreach ($colors as $colorItem){
	               		$checked = $colorItem['name']==$item['color_z'] ? "selected" : "";
	               		$c.='<option '.$checked.' value="'.$colorItem['name'].'" style="background-color: #'.$colorItem['code'].'">'.$colorItem['name'].'</option>'; 
	               	}
					$c.='</select>';					
				}

				$_rowTpl = str_replace("[+color+]", $c, $_rowTpl);	

				$size= $item['type']=='retail' ? $item['retail_size'] : $item['size'];
				$size= array_map('trim', explode (",", $size));
				$count_size = count($size);
				$s='<select name="cart['.$row_id.'][size_z]">';
               	for ($i=0; $i<$count_size; $i++){
               		$checked = $size[$i]==trim($item['size_z']) ? "selected" : "";
               		$s.='<option '.$checked.' value="'.$size[$i].'">'.$size[$i].'</option>'; 
               	}
				$s.='</select>';	
				$_rowTpl = str_replace("[+size+]", $s, $_rowTpl);			
				
				foreach($item as $_k => $_v) {
					$_rowTpl = str_replace('[+'.$_k.'+]', $_v, $_rowTpl);
				} 
				$cart_rows .= $_rowTpl;	

				if($item['type']=='package' || $item['type']=='opt'){
					$pricelimit = 1;
					$minAmount = $this->getOrderLimit('opt');
					
					$modx->setPlaceholder('ec.limit_opt', 1);
				}
			}		

			$order_datas = $this->getOrderDatas();			
			$modx->setPlaceholder('ec.cartquantity', $order_datas['quantity']);
			$modx->setPlaceholder('ec.cartdiscount', $order_datas['discount']);
			$modx->setPlaceholder('ec.cartamount', $order_datas['cart_amount']);
			$modx->setPlaceholder('ec.dcartamount', $order_datas['dcart_amount']);
			$modx->setPlaceholder('ec.limit', $pricelimit ? $minAmount : 0);
			$modx->setPlaceholder('pricelimit', $pricelimit);
			
			$modx->setPlaceholder('ec.shippingamount', 0);
			$total_amount = $order_datas['dcart_amount'];
			$modx->setPlaceholder('ec.totalamount', money1($total_amount));			
			
			$output = str_replace('[+ec.wrapper+]',$cart_rows, $outerTpl);	
			

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
		
		$sql = "SELECT o.*,pt.name as payment_type_name,os.name as status_name, dt.name as delivery_name FROM ".$modx->getFullTableName("site_ec_orders")." o ";
		$sql.= "LEFT JOIN ". $modx->getFullTableName("site_ec_payment_methods")." pt ON o.payment_type = pt.id "; 
		$sql.= "LEFT JOIN ". $modx->getFullTableName("ec_order_status")." os ON o.status = os.id ";
		$sql.= "LEFT JOIN ". $modx->getFullTableName("site_ec_delivery_types")." dt ON o.delivery_type = dt.id ";  
	    $sql.= "WHERE o.id = '{$id}' LIMIT 1;";
		$result = $modx->dbQuery($sql);	        
		$numResults = @$modx->recordCount($result);					
		if ($numResults == 1) {
			$row = $modx->fetchRow($result);
			$row['items'] = $this->getOrderItemsInfo($row['id']);
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
			$modx->setPlaceholder('ec.total_amount', $amount);		
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
		//print_r($order);die();		
		if ($order) {						
			foreach($order['items'] as $item) {	
				##
				$sql = "SELECT * FROM ". $modx->getFullTableName("site_ec_items")." WHERE (id='".$item['item_id']."')";	
				$result = $modx->dbQuery($sql);	
				$item_info = $modx->fetchRow($result);
				foreach($item_info as $item_param_name => $item_param){
					$item[$item_param_name] = $item_param;
				}
				
			    $num++;
				$total_price = floatval($item['price'])*intval($item['quantity']);	
				$cost = $item['quantity']*$item['price'];
				$cart_amount += $total_price;	
				$total_price = $total_price;			            				
				$_rowTpl = $rowTpl;	
				$_rowTpl = str_replace("[+num+]", $num, $_rowTpl);	
				$_rowTpl = str_replace("[+itemhomeid+]", $this->params['itemhomeid'], $_rowTpl);
				$_rowTpl = str_replace("[+total_price+]", $total_price, $_rowTpl);
				$_rowTpl = str_replace("[+cost+]", $cost, $_rowTpl);										
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
			$modx->setPlaceholder('ec.shippingamount', $order['delivery_amount']);				
			$modx->setPlaceholder('ec.cartamount', $cart_amount);
			$modx->setPlaceholder('ec.limit', $this->getOrderLimit());
			$modx->setPlaceholder('ec.dcartamount', $dcart_amount);
			$modx->setPlaceholder('ec.orderamount', $order['amount']);					
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
		$sql.= "LEFT JOIN ". $modx->getFullTableName("site_ec_payment_methods")." pt ON o.payment_type = pt.id "; 
		$sql.= "LEFT JOIN ". $modx->getFullTableName("ec_order_status")." os ON o.status = os.id "; 
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
		$sql = "SELECT oi.*,i.pagetitle as itemtitle,i.parent as itemparent, i.package_items, i.acc_id ";
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
		if (!$user_id)
			$user_id = 0;		
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
		$sname = mysql_escape_string($order_post['sname']);
		$phone = mysql_escape_string($order_post['phone']);
		$opt = mysql_escape_string($order_post['opt']);
			$km = mysql_escape_string($order_post['km']);
		
		if ($order_post['to'] == 'russia' && isset($order_post['metro'])) 
		$metro = mysql_escape_string($order_post['metro']);
		else $metro = ''; 
		
		
		
		$delivery = mysql_escape_string($order_post['shipping']);		
		$discount = mysql_escape_string($order['discount']);
		$quantity = mysql_escape_string($order['quantity']);
		
		$delivery_amount = mysql_escape_string($order['delivery_amount']);
		if(!$delivery_amount){
			$delivery_amount = 0;
		}
		
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
		
		$bonuscode = mysql_escape_string($order_post['bonuscode']);
		$infosource = mysql_escape_string($order_post['bonuscode']);
		
	
			$payment_method_id = intval($order_post['payment_method_id']);
		    $payment_method = $this->getPaymentType($payment_method_id); 
			$pm_params = serialize($payment_method['params']);
		
		
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
	 
		 if ($row_attr['region']!=$region or $row_attr['town']!=$state or $row_attr['street']!=$street or $row_attr['house']!=$dom or $row_attr['postcode1']!=$postcode1  or $row_attr['kvartira']!=$kvartira or $row_attr['korpus']!=$korpus or $row_attr['sname']!=$sname) 
		 {
		 	$query57 = "UPDATE modx_web_user_attributes SET region='$region', town = '$state', street ='$street', house='$dom', postcode1='$postcode1', kvartira='$kvartira', korpus='$korpus', sname='$sname'  WHERE internalKey='$user_id'";
	   		$result57 = @mysql_query($query57);
		 
		 	$user_info = $modx->getWebUserInfo($user_id);
		 }
			
		if ($quantity==0) {
			$header="Location: ".MODX_SITE_URL."/cabinet/placeorder";
			header($header);
			break;
		}
		
		if ($delivery=='outsea') $confirmed=1;
			
		$sql = "INSERT INTO ".$modx->getFullTableName("site_ec_orders").
		$sql.= "(id,informcust,delivery_type,confirmed,params,bonus_code,bonus,bonus_state,customer_postcode1,confirm_key,status,order_date, payment_type, discount,delivery_amount,quantity,amount,";
		$sql.= " customer_id,customer_ip,customer_fname,customer_sname,customer_lname,customer_postcode, customer_country,customer_region,customer_state,";
		$sql.= " customer_street,customer_korpus,customer_dom,customer_kvartira,customer_metro,";
		$sql.= " customer_email,customer_phone,customer_comment,customer_sku_comment,customer_sku_comment1, customer_company, customer_type, bonuscode, infosource, km)";
		$sql.= " VALUES('$order_id','$informcust','$delivery','$confirmed','$pm_params','$bonus_code','$bonus','0','$postcode1','$confirm_key','$status','$order_date', '$payment_method_id', '$discount', '$delivery_amount',"; 
		$sql.= " '$quantity','$total_amount',";
		$sql.= " '$user_id', '$_SERVER[REMOTE_ADDR]', '$user_info[fname]', '$user_info[sname]', '$user_info[lname]', '$user_info[postcode]','$country',"; 
		$sql.= " '$region','$state','$street','$korpus','$dom','$kvartira','$metro','$user_info[email]','$phone','$comment','$skuwait','$skudo', '$user_info[company]', '$user_info[type]', '$user_info[bonuscode]', '$user_info[infosource]', '".(int)$km."'); ";
		//die($sql);					
		$rs = $modx->dbQuery($sql);		
		
	/*	if (($order_post['to'] == 'russia' or $order_post['to'] == 'outsea') && $confirm == '1') {			
			if ($order['bonus'] > $this->user['bonus']) {
				$bonus_state = '1';
				$sql = "UPDATE ".$modx->getFullTableName("web_user_attributes")." SET bonus='$order[bonus]' WHERE internalKey = '$user_id' LIMIT 1";
				$modx->dbQuery($sql);
				
			} else $bonus_state = '2';
			
			$sql = "UPDATE ".$modx->getFullTableName("site_ec_orders")." SET bonus_state='$bonus_state' WHERE id = '$order[id]' LIMIT 1";
			$modx->dbQuery($sql);
		}	
		
		*/
		
		if ($rs) {
			if ($user_id)
				$cart =  $this->getCartData();
			else
				$cart =  $this->getTempCartData();
			foreach($cart as $item) {			
				$price = $this->price($item,$item[quantity],$item['type']);
				$color_z= $item['color_z'];	
			    $size_z= $item['size_z'];
			    $type = $item['type'];
			    $sql = "INSERT INTO ".$modx->getFullTableName("site_ec_order_items")."(order_id,item_id,quantity,price,size_z,color_z,type)";		    
			    $sql.= "VALUES('$order_id', '$item[item_id]', '$item[quantity]', '$price', '$size_z', '$color_z', '$type'); ";				
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
			
			if ($payment_method['confirm'] == 1 ) {
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
				$_SESSION['just_ordered'] = $order_id;//заказ был только что оформлен
				session_write_close();
				$url = $modx->makeURL($this->params['checkouthomeid']).'?user_order_id='.$order_id;
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
	
function buildPaymentTypes($id, $order) {
		global $modx;
		
		$outerTpl = $this->getTemplate($this->templates['cartPaymentOuterTpl']);
		$rowTpl = $this->getTemplate($this->templates['cartPaymentRowTpl']);
		$formTpl = $this->getTemplate('eCartPaymentFormTpl');
		
		$payment_types = $this->getPaymentTypes();		
		$_rows = '';
		$_forms = '';	
		$i = 0; 
		$first = false;
		
		if (count($payment_types) > 0) {
			if(!$id){
				$id = $payment_types[0]['id'];	
			}	
								
			foreach($payment_types as $v => $item) {	
				$_rowTpl = $rowTpl;			
				$i++;
				if ($id == $item['id']) {
					$_rowTpl = str_replace('[+checked+]','checked',$_rowTpl);
				} /*elseif ($i == 1) {
					$modx->setPlaceholder('ec.default.payment.auto', $item['auto']);	
					$_rowTpl = str_replace('[+checked+]','checked',$_rowTpl);
					$first = true;
				}*/
				
				foreach($item as $_k => $_v) {		
					$_rowTpl = str_replace('[+'.$_k.'+]', $_v, $_rowTpl);
				}				
				$_rows .= $_rowTpl;	
				
				//forms
				$_formTpl = $formTpl;
				$class = $item['class'];
				
				$classname = $class.'Payment';
				$classFile = $classname . '.class.php';
									
				require_once($modx->config['base_path'].'/assets/snippets/ecart/payments/'.$classFile);
				
				$Payment = new $classname($item, $order);
				
				$form = $Payment->showForm();
				
				if ($id == $item['id']){
					$_formTpl = str_replace('[+active+]', true, $_formTpl);
				}
				$_formTpl = str_replace('[+content+]', $form, $_formTpl);
				foreach($item as $k => $v){
					$_formTpl = str_replace("[+payment.$k+]", $v, $_formTpl);
				}				
				foreach($order as $k => $v){
					$_formTpl = str_replace("[+order.$k+]", $v, $_formTpl);
				}
				
				$_forms .= $_formTpl;

			}
			$output = str_replace('[+ec.list+]',$_rows, $outerTpl);
			$output = str_replace('[+ec.forms+]',$_forms, $output);
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
		$fields = "shc.id as incart_id,shc.*,eci.*";		
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
			
			$sql = "SELECT {$fields} FROM ".$modx->getFullTableName("site_ec_shopping_cart")." shc LEFT JOIN 
					".$modx->getFullTableName("site_ec_items")." eci	ON shc.item_id = eci.id 
					WHERE (shc.customer_id = {$user_id} AND published=1 AND deleted=0)";
					
				// build query
		    //$sql = "SELECT {$fields} FROM ".$modx->getFullTableName("site_ec_shopping_cart")." shc INNER JOIN ";
		    //$sql.= $modx->getFullTableName("site_ec_items")." eci  ON shc.item_id = eci.id  "; 
		    
		    //$sql.= "WHERE shc.customer_id = {$user_id} AND published=1 AND deleted=0 "; 
		    //$sql.= "GROUP BY eci.id ".(!empty($sort) ? "ORDER BY {$sort} {$dir}" : " ");	
		    	
		    //die($sql);
			/*if (isset($_SESSION['rozn'])&&$_SESSION['rozn']==1){
				$type="retail";
			}
			else{
				$type="opt";
			}*/
					    
			$result = $modx->dbQuery($sql);	        
			$numResults = @$modx->recordCount($result);
			$resultIds = array();
			//loop through the results
			while($tempDocInfo = $modx->fetchRow($result)) {
				
				$type = $tempDocInfo['type'];
				//$_item = array();
				//print_r($tempDocInfo);die();
				
				##
				if($tempDocInfo['package_items'] > 0 && $type=="opt"){
					$type = "package";
				}
				
				$item_id = $tempDocInfo['id'];
				$prices = getPrices($item_id, $type);
				if($prices){
					$size_z = $tempDocInfo['size_z'];
					if($prices[$size_z] > 0){
						if($type=="retail")
							$tempDocInfo['retail_price'] = $prices[$size_z];
						elseif($type=="opt") 
							$tempDocInfo['price_opt'] = $prices[$size_z];
						elseif($type=="package") 
							$tempDocInfo['package_price'] = $prices[$size_z];							
					}
				}
								
				$tempDocInfo['fretail_price'] = money1($tempDocInfo['retail_price']); 
				$tempDocInfo['fmdealer_price'] = money1($tempDocInfo['mdealer_price']);
				$tempDocInfo['fdealer_price'] = money1($tempDocInfo['dealer_price']);
				$tempDocInfo['fsku'] = quantity($tempDocInfo['sku']);
				//$resultIds[] = $tempDocInfo['id'];			

				$tempDocInfo['item_id'] = $tempDocInfo['id'];
				
				$tempDocInfo['quantity'] = $tempDocInfo['quantity'];
				$tempDocInfo['color_z'] = $tempDocInfo['color_z'];
				$tempDocInfo['size_z'] = $tempDocInfo['size_z'];
				
				$tempDocInfo['type'] = $tempDocInfo['type']=='opt' ? 'package' : 'retail';
								
				$tempResults[$tempDocInfo['incart_id']] = $tempDocInfo;				
		    }
		    //print_r($tempResults);die();		   
			//Process the tvs			
			//$resourceArray = $this->appendTVs($tempResults,$resultIds);
			$resourceArray = $this->appendTVs($tempResults);
			$resourceArray = $this->appendColors($resourceArray);
		} else 	$resourceArray = false;
		//return final docs
		
		$resourceArray = $this->sortCartData($resourceArray);	
		
        return $resourceArray;
	}	
	
	function getTempCartData() {
		global $modx;
		$resourceArray = array();
		$tempResults =  array();
		$fields = "*";
		$temp_cart_session = @$_SESSION['temp_cart'];		
		//print_r($temp_cart_session);die();
		
		if (count($temp_cart_session)>0) {
			##
			/*if (isset($_SESSION['rozn'])&&$_SESSION['rozn']==1){
				$type="retail";
			}
			else{
				$type="opt";
			}*/			
			foreach($temp_cart_session as $item){
				$type = $item['type'];
				
				$item_id = $item['item_id'];
				$sql = "SELECT {$fields} FROM ".$modx->getFullTableName("site_ec_items")." WHERE id='$item_id' AND published=1 AND deleted=0";
				$result = $modx->dbQuery($sql);	

				$tempDocInfo = $modx->fetchRow($result);
				
				##
				if($tempDocInfo['package_items'] > 0 && $type=="opt"){
					$type = "package";
				}
								
				$prices = getPrices($item_id, $type);
				if($prices){
					$size_z = $item['size_z'];
					if($prices[$size_z] > 0){
						if($type=="retail")
							$tempDocInfo['retail_price'] = $prices[$size_z];
						elseif($type=="opt") 
							$tempDocInfo['price_opt'] = $prices[$size_z];
						elseif($type=="package") 
							$tempDocInfo['package_price'] = $prices[$size_z];							
					}
				}
								
				$tempDocInfo['fretail_price'] = money1($tempDocInfo['retail_price']); 
				$tempDocInfo['fdealer_price'] = money1($tempDocInfo['dealer_price']);
				$tempDocInfo['fsku'] = quantity($tempDocInfo['sku']);
				$tempDocInfo['item_id'] = $tempDocInfo['id'];
				
				$tempDocInfo['quantity'] = $item['quantity'];
				$tempDocInfo['color_z'] = $item['color_z'];
				$tempDocInfo['size_z'] = $item['size_z'];
				//$tempDocInfo[''] = $item['color_z'];
				
				$tempDocInfo['type'] = $type;
				
				$tempResults[] = $tempDocInfo;
			}
			
			/*foreach ($temp_cart_session as $item) {
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
		    $sql.= "WHERE id IN ({$itemids}) AND published=1 AND deleted=0"; //die($sql);
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
				//$tempResults[$tempDocInfo['id']] = $tempDocInfo;
				##
				$tempResults[] = $tempDocInfo;				
		    }*/
		    //print_r($tempResults);
			//Process the tvs			
			//print_r($resultIds);
			//exit;
			##
			//$resourceArray = $this->appendTVs($tempResults,$resultIds);
			$resourceArray = $this->appendTVs($tempResults);
			$resourceArray = $this->appendColors($resourceArray);
			//$resourceArray = $tempResults;
			//print_r($resourceArray);
			//die('yes1');
		} 
		//return final docs
		//print_r($resourceArray);die();
		
		$resourceArray = $this->sortCartData($resourceArray);
		
        return $resourceArray;
	}	
		
	function appendTVs($tempResults/*,$docIDs*/){
		global $modx;		
		
		//if (implode($docIDs,",") == '') return $tempResults;
		$baspath= $modx->config["base_path"] . "manager/includes";
	    include_once $baspath . "/tmplvars.format.inc.php";
	    include_once $baspath . "/tmplvars.commands.inc.php";
		$tb1 = $modx->getFullTableName("site_tmplvar_ec_itemvalues");		
		$tb2 = $modx->getFullTableName("site_tmplvars");

		$tvlist = $this->getTVList();
		
		foreach($tempResults as $id => $item){
			$item_id = $item['item_id'];
			
			$query = "SELECT stv.name,stc.tmplvarid,stc.itemid,stv.type,stv.display,stv.display_params,stc.value";
			$query .= " FROM ".$tb1." stc LEFT JOIN ".$tb2." stv ON stv.id=stc.tmplvarid  ";
			$query .= " WHERE stc.itemid='$item_id'";

			$rs = $modx->db->query($query);
			
			foreach ($tvlist as $tv) {
				$tempResults[$id][$tv] = '';
			}

			while($row = @$modx->fetchRow($rs)){
			
				if (!empty($row['value']))
				$tempResults[$id][$row['name']] = getTVDisplayFormat($row['name'], $row['value'], $row['display'], $row['display_params'], $row['type'],$row['itemid']);   
				else 
				$tempResults[$id][$row['name']] = getTVDisplayFormat($row['name'], $row['default_text'], $row['display'], $row['display_params'], $row['type'],$row['itemid']);	
				//$tv_names[$row['itemid']][] = $row['name'];	
			}			
			
		}
				
		return $tempResults;
	}	
	
	function appendColors($tempResults){
		global $modx;
		
		foreach($tempResults as $id => $item){
			$item['color'] = new Colors($item['id']);
			$results[$id] = $item;
		}
		unset($tempResults);
		
		return $results;
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
		//$tvs = $modx->db->select("name", $table, );
		$sql = "SELECT tv.name FROM $table tv
			LEFT JOIN modx_site_tmplvar_templates tp ON (tv.id=tp.tmplvarid)
			WHERE (tp.templateid='17')
		";
		$tvs = $modx->db->query($sql);
		// TODO: make it so that it only pulls those that apply to the current template 17
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
			$dittoID = '';//($dittoIdentifier !== false) ? $dittoIdentifier : $dittoID;
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
	
	##
	function buildNotUserOrderPage() {
		global $modx;

		$output = "";
		//$chunkArr = array();
		//$output = $modx->parseChunk('order_not_user', $chunkArr, '[+', '+]'); 
		
		$output = $modx->runSnippet(
		        "eForm",
		        array(
		            'formid'=>'order_form',
		        	'tpl'=>'order_not_user_form',
		        	'gotoid'=>'34',
		        	'vericode'=>'1',
		        	//'eFormOnMailSent'=>'processOrder',
		        	'noemail'=>'1'
		        )
		);		
			//die(stristr($output, '<div class="errors">'));	
		if (!stristr($output, '<div class="errors">') && $_POST[submit_order]){
			
			$infosource = $_POST['infosource'];
			$infosource_other = $_POST['infosource_other'];
			
			switch($infosource){
				case 'site':
					$infosource = 'Сайт';
					break;
				case 'telephone':
					$infosource = 'Телефонный звонок';
					break;		
				case 'other':
					$infosource = $infosource_other;
					break;	
				default:
					$infosource = "";					
			}
						
			$user_info = array(
				'email' => $_POST['email'],
				'fname' => $_POST['fname'],
				'sname' => $_POST['sname'],
				'infosource' => $infosource,
				'bonuscode' => $_POST['bonuscode']
			);
			
			$this->user = $user_info;
			
			$order_datas = $this->getOrderDatas();
			//print_r($order_datas);die();			
	    	/*$region_id = $order['region']; 
			$rate = $this->getRegionRate($region_id);	
			$km=$order['km'];		
	    	$shipping = 0;					
	    	$delivery = $order['shipping'];		    		
	    	if ($order['to'] == 'russia') { 
	    		$payment_method = $this->getPaymentType($order['payment_method_id']);
				$payment_mode = $payment_method['mode'];
	    		$delivery_amount = $this->getOrderShippingAmount($payment_mode, $delivery, $order_datas['dcart_amount'], $shipping, $rate, $km); 
				$total_amount = $order_datas['dcart_amount'] + $delivery_amount;			  		
				$order_datas['delivery_amount'] = $delivery_amount;
				$order_datas['total_amount'] = $total_amount;			
	    	} else {
	    	$payment_method = $this->getPaymentType($order['payment_method_id']);
	    	$payment_mode = $payment_method['mode'];
	    		$delivery_amount =  floatval($this->config['delivery_zarubezh']);
	    		$total_amount = $order_datas['dcart_amount'] + $delivery_amount;
	    		$order_datas['delivery_amount'] = $delivery_amount;
				$order_datas['total_amount'] = $total_amount;	
	    	}*/		
			$delivery_amount = 0;
			$order_datas['delivery_amount'] = $delivery_amount;  
			$total_amount = $order_datas['dcart_amount'] + $delivery_amount;
			$order_datas['total_amount'] = $total_amount;  

			$_POST['order']['payment_method_id'] = 7;
			$_POST['order']['phone'] = $_POST['phone'];
			$_POST['order']['state'] = $_POST['state'];
			$_POST['order']['comment'] = $_POST['comment'];			
				
	    	$this->placeOrder($order_datas);			
		}
		
		return $output;
	}
	function quickPlaceOrder(){
		if($this->user && is_array($this->cart) && count($this->cart) > 0){
	    	$order_datas = $this->getOrderDatas();			
	    	$region_id = '18825'; 
			$rate = $this->getRegionRate($region_id);	
			//$km=$order['km'];		
	    	$shipping = 0;					
	    	$delivery = 'self';		    		
	    	$order['payment_method_id'] = 8;
	    	$order['to'] = 'russia';
	    	if ($order['to'] == 'russia') { 
	    		$payment_method = $this->getPaymentType($order['payment_method_id']);
				$payment_mode = $payment_method['mode'];
	    		$delivery_amount = $this->getOrderShippingAmount($payment_mode, $delivery, $order_datas['dcart_amount'], $shipping, $rate, $km); 
				$total_amount = $order_datas['dcart_amount'] + $delivery_amount;			  		
				$order_datas['delivery_amount'] = $delivery_amount;
				$order_datas['total_amount'] = $total_amount;			
	    	} else {
	    		$payment_method = $this->getPaymentType($order['payment_method_id']);
	    		$payment_mode = $payment_method['mode'];
	    		$delivery_amount =  floatval($this->config['delivery_zarubezh']);
	    		$total_amount = $order_datas['dcart_amount'] + $delivery_amount;
	    		$order_datas['delivery_amount'] = $delivery_amount;
				$order_datas['total_amount'] = $total_amount;	
	    	}
			//print_r($this->user);die();
	    	$order['comment'] = "";
	    	$order['country'] = "Россия";
	    	$order['dom'] = $this->user['house'];
	    	$order['informcust'] = "1";
	    	$order['korpus'] = $this->user['korpus'];
	    	$order['kvartira'] = $this->user['kvartira'];
	    	$order['phone'] = $this->user['phone'];
	    	$order['postcode1'] = $this->user['postcode1'];
	    	$order['region'] = "18825";
	    	$order['shipping'] = "self";
	    	$order['skudo'] = "";
	    	$order['skuwait'] = "";
	    	$order['state'] = $this->user['town'];
	    	$order['street'] = $this->user['street'];
	    	//$order['town'] = $this->user['town'];
	    	$order['to'] = "russia";
	    	
	    	$_POST['order'] = $order;
	    	$this->placeOrder($order_datas);	
		}	
	}

	/* Минимальная сумма заказа */
	function getOrderLimit($type = 'opt'){
		/*$defaultLimit = 15000;
		if($this->user){
			if($this->user['reg_date'] < 1306872000){
				return 5000;
			}
		}
		return $defaultLimit;*/
		
		$limit = 5000;
		
		switch($type){
			case 'retail':
				$limit = 1000;
				break;
			case 'opt':
				$limit = 5000;
				break;
		}
		return $limit;
	}
}

?>