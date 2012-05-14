<?php
	
class Payment {

	var $params;
	var $paymentFormTpl = 'WebMoneyPaymentFormTpl';
	var $pm;
	var $order = array();
	var $config = array();
 	
	function Payment($id,$ec){
		$this->ec = $ec;	
		$this->loadConfig();	
		$pm = $this->ec->getPaymentType($id);
		if ($pm) {
			$this->pm = $this->ec->getPaymentType($id);
		} else {
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
	
	function buildPaymentForm($order_id){
		global $modx;		
		$status = $this->config['order_notconfirmed_status'];		
		if ($this->order['status'] == $status && $this->order['paid'] == 1) 	{		
			$url = $modx->makeURL($modx->config['site_start']);
           	$modx->sendRedirect($url,0,'REDIRECT_HEADER');			
		}	
		$payment_form = $modx->getChunk($this->paymentFormTpl);			
		$wmr_amount = floatval($this->order['amount']);	
		$params = unserialize($this->order['params']);	
		$wmz_amount = $wmr_amount/floatval($params['wmr2wmzrate']);
		$wmz_amount = number_format($wmz_amount,2,'.','');	
		$wmr_amount = number_format($wmr_amount,2,'.','');
			
		$phx = array(wmr_amount => $wmr_amount,
		             wmz_amount => $wmz_amount,
		             wmr_purse => $this->pm['params']['wmr_purse'],
		             wmz_purse => $this->pm['params']['wmz_purse'],
		             user_order_id => $order_id		             					
					);
		
					foreach($phx as $k => $v) {
			$payment_form = str_replace('[+'.$k.'+]', $v, $payment_form);
		}		
		return $payment_form;			
	}
	
	function processPayment() {
		global $ec,$modx;	
		IF($_POST['LMI_PREREQUEST']==1 || $_GET['LMI_PREREQUEST'] == 1) {					
		  // 2) Проверяем, не произошла ли подмена суммы.
		  // Cравниваем стоимость товара в базе данных с той суммой, что передана нам Мерчантом.
		  // Если сумма не совпадает, то выводим ошибку и прерываем работу скрипта.
		  if (isset($_POST['user_order_id'])) {
		  	  $order_id = mysql_escape_string($_POST['user_order_id']);	   	
			  $order = $this->ec->getOrderInfo($order_id);	
			  $status = $this->config['order_confirmed_status'];			 	
			  $wm = strtolower(substr($_POST['LMI_PAYEE_PURSE'],0,1));
			  $params = unserialize($order['params']);	
			  if ($wm == 'z') {
			  	$wmr_amount = $order['amount'];		
				$amount = $wmr_amount/floatval($params['wmr2wmzrate']);
				$amount = number_format($amount,2,'.','');	
			  } else {
			  	$amount = $order['amount'];
			  }	  
			  if($amount != trim($_POST['LMI_PAYMENT_AMOUNT'])) {
			    $err=1;
			    echo "ERROR: НЕВЕРНАЯ СУММА ".$amount.' == '.$_POST['LMI_PAYMENT_AMOUNT'];
			    exit;
			  }
			  // 3) Проверяем, не произошла ли подмена кошелька.
			  // Cравниваем наш настоящий кошелек с тем кошельком, который передан нам Мерчантом.
			  // Если кошельки не совпадают, то выводим ошибку и прерываем работу скрипта.
			  if ($wm == 'z') $purse = $this->pm['params']['wmz_purse'];
			  else $purse = $this->pm['params']['wmr_purse'];
			  
			  if($_POST['LMI_PAYEE_PURSE'] != $purse) {
			    $err=1;
			    echo "ERROR: НЕВЕРНЫЙ КОШЕЛЕК ПОЛУЧАТЕЛЯ ".$purse.'=='.$_POST['LMI_PAYEE_PURSE'];
			    exit;
			  }
			  // 4) Проверяем, указал ли пользователь свой email.
			  // Если ошибок не возникло, то выводим YES
		  	  if(!$err) echo "YES";
		  } 
		}
		// ЕСЛИ НЕТ LMI_PREREQUEST, СЛЕДОВАТЕЛЬНО ЭТО ФОРМА ОПОВЕЩЕНИЯ О ПЛАТЕЖЕ...
		ELSE {
		  // Задаем значение $secret_key.
		  // Оно должно совпадать с Secret Key, указанным нами в настройках кошелька.
		  $secret_key=$this->pm['params']['secretkey'];
		  // Склеиваем строку параметров
		  $common_string = $_POST['LMI_PAYEE_PURSE'].$_POST['LMI_PAYMENT_AMOUNT'].$_POST['LMI_PAYMENT_NO'].
		  $_POST['LMI_MODE'].$_POST['LMI_SYS_INVS_NO'].$_POST['LMI_SYS_TRANS_NO'].
		  $_POST['LMI_SYS_TRANS_DATE'].$secret_key.$_POST['LMI_PAYER_PURSE'].$_POST['LMI_PAYER_WM'];
		  // Шифруем полученную строку в MD5 и переводим ее в верхний регистр
		  $hash = strtoupper(md5($common_string));
		  // Прерываем работу скрипта, если контрольные суммы не совпадают
		  if($hash == $_POST['LMI_HASH']) {
		  	  $amount = $_POST['LMI_PAYEE_PURSE'].$_POST['LMI_PAYMENT_AMOUNT'];
		  	  
		  	  # LMI_PAYEE_PURSE - кошелек продавца, на который покупатель совершил платеж.
			  # LMI_PAYMENT_AMOUNT - сумма, которую заплатил покупатель.
			  # LMI_PAYER_PURSE - кошелек покупателя, с которого он совершил платеж.
			  # LMI_PAYER_WM - WMID покупателя.
			  # LMI_PAYMER_NUMBER - номер WM-карты или чека Paymer, если покупатель выбрал оплату WM-картой.
              # LMI_TELEPAT_PHONENUMBER - номер телефона покупателя в системе Telepat, если выбрана оплата по системе Telepat.
			  # LMI_HASH - контрольная подпись. Что это такое, расскажем через минуту.
			  # LMI_SYS_TRANS_DATE - дата и время совершения платежа с точностью до секунд.
			  
			  $paidid = 'Оплачено '.$_POST['LMI_PAYMENT_AMOUNT'].' на кашелку '.$_POST['LMI_PAYEE_PURSE'].'. ';
		  	  $paidid.= 'Номер кошелка покупателя: '. $_POST['LMI_PAYER_PURSE'].''. ';';	
		  	  $paidid.= !empty($_POST['LMI_PAYER_PURSE']) ? 'WMID покупателя: '. $_POST['LMI_PAYER_PURSE'].''. ';' : '';
		  	  $paidid.= !empty($_POST['LMI_PAYMER_NUMBER']) ? 'Номер WM-карты или чека Paymer: '. $_POST['LMI_PAYMER_NUMBER'].''. ';' : '';
		  	  $paidid.= !empty($_POST['LMI_TELEPAT_PHONENUMBER']) ? 'Номер телефона покупателя в системе Telepat: '. $_POST['LMI_TELEPAT_PHONENUMBER'].''. ';' : '';
		  	  $paidid.= 'Номер кошелка покупателя: '. $_POST['LMI_PAYER_PURSE'].''. ';';
		  	  $paidid.= 'Номер счета в системе WebMoney Transfer: '. $_POST['LMI_SYS_INVS_NO'].''. ';';
		  	  $paidid.= 'Номер платежа в системе WebMoney Transfer: '. $_POST['LMI_SYS_TRANS_NO'].''. ';';
		  	  $paidid.= 'Дата и время совершения платежа: '. $_POST['LMI_SYS_TRANS_DATE'].''. ';';	
		  	  
		  	  
		  	  $order_id = $_POST['user_order_id'];		  	  
		  	  $this->ec->changeOrderStatus($order_id,$paidid); 
		  	  //$this->ec->sendBonus($order_id);
		  	  $order = $this->ec->getOrderInfo($order_id);
		  	  $customer_id = $order['customer_id'];
		  	  $this->ec->sendPaymentDoneMessage($customer_id,$order_id);
		  	  	
		  } else {
		  	  echo 'no-'.$secret_key;
		  }		  		 
		  // Отправляем товар на email покупателя		  	  
		}
		exit;	
	}	
}

?>