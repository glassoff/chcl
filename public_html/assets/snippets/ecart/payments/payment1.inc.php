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
		  // 2) ���������, �� ��������� �� ������� �����.
		  // C��������� ��������� ������ � ���� ������ � ��� ������, ��� �������� ��� ���������.
		  // ���� ����� �� ���������, �� ������� ������ � ��������� ������ �������.
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
			    echo "ERROR: �������� ����� ".$amount.' == '.$_POST['LMI_PAYMENT_AMOUNT'];
			    exit;
			  }
			  // 3) ���������, �� ��������� �� ������� ��������.
			  // C��������� ��� ��������� ������� � ��� ���������, ������� ������� ��� ���������.
			  // ���� �������� �� ���������, �� ������� ������ � ��������� ������ �������.
			  if ($wm == 'z') $purse = $this->pm['params']['wmz_purse'];
			  else $purse = $this->pm['params']['wmr_purse'];
			  
			  if($_POST['LMI_PAYEE_PURSE'] != $purse) {
			    $err=1;
			    echo "ERROR: �������� ������� ���������� ".$purse.'=='.$_POST['LMI_PAYEE_PURSE'];
			    exit;
			  }
			  // 4) ���������, ������ �� ������������ ���� email.
			  // ���� ������ �� ��������, �� ������� YES
		  	  if(!$err) echo "YES";
		  } 
		}
		// ���� ��� LMI_PREREQUEST, ������������� ��� ����� ���������� � �������...
		ELSE {
		  // ������ �������� $secret_key.
		  // ��� ������ ��������� � Secret Key, ��������� ���� � ���������� ��������.
		  $secret_key=$this->pm['params']['secretkey'];
		  // ��������� ������ ����������
		  $common_string = $_POST['LMI_PAYEE_PURSE'].$_POST['LMI_PAYMENT_AMOUNT'].$_POST['LMI_PAYMENT_NO'].
		  $_POST['LMI_MODE'].$_POST['LMI_SYS_INVS_NO'].$_POST['LMI_SYS_TRANS_NO'].
		  $_POST['LMI_SYS_TRANS_DATE'].$secret_key.$_POST['LMI_PAYER_PURSE'].$_POST['LMI_PAYER_WM'];
		  // ������� ���������� ������ � MD5 � ��������� �� � ������� �������
		  $hash = strtoupper(md5($common_string));
		  // ��������� ������ �������, ���� ����������� ����� �� ���������
		  if($hash == $_POST['LMI_HASH']) {
		  	  $amount = $_POST['LMI_PAYEE_PURSE'].$_POST['LMI_PAYMENT_AMOUNT'];
		  	  
		  	  # LMI_PAYEE_PURSE - ������� ��������, �� ������� ���������� �������� ������.
			  # LMI_PAYMENT_AMOUNT - �����, ������� �������� ����������.
			  # LMI_PAYER_PURSE - ������� ����������, � �������� �� �������� ������.
			  # LMI_PAYER_WM - WMID ����������.
			  # LMI_PAYMER_NUMBER - ����� WM-����� ��� ���� Paymer, ���� ���������� ������ ������ WM-������.
              # LMI_TELEPAT_PHONENUMBER - ����� �������� ���������� � ������� Telepat, ���� ������� ������ �� ������� Telepat.
			  # LMI_HASH - ����������� �������. ��� ��� �����, ��������� ����� ������.
			  # LMI_SYS_TRANS_DATE - ���� � ����� ���������� ������� � ��������� �� ������.
			  
			  $paidid = '�������� '.$_POST['LMI_PAYMENT_AMOUNT'].' �� ������� '.$_POST['LMI_PAYEE_PURSE'].'. ';
		  	  $paidid.= '����� ������� ����������: '. $_POST['LMI_PAYER_PURSE'].''. ';';	
		  	  $paidid.= !empty($_POST['LMI_PAYER_PURSE']) ? 'WMID ����������: '. $_POST['LMI_PAYER_PURSE'].''. ';' : '';
		  	  $paidid.= !empty($_POST['LMI_PAYMER_NUMBER']) ? '����� WM-����� ��� ���� Paymer: '. $_POST['LMI_PAYMER_NUMBER'].''. ';' : '';
		  	  $paidid.= !empty($_POST['LMI_TELEPAT_PHONENUMBER']) ? '����� �������� ���������� � ������� Telepat: '. $_POST['LMI_TELEPAT_PHONENUMBER'].''. ';' : '';
		  	  $paidid.= '����� ������� ����������: '. $_POST['LMI_PAYER_PURSE'].''. ';';
		  	  $paidid.= '����� ����� � ������� WebMoney Transfer: '. $_POST['LMI_SYS_INVS_NO'].''. ';';
		  	  $paidid.= '����� ������� � ������� WebMoney Transfer: '. $_POST['LMI_SYS_TRANS_NO'].''. ';';
		  	  $paidid.= '���� � ����� ���������� �������: '. $_POST['LMI_SYS_TRANS_DATE'].''. ';';	
		  	  
		  	  
		  	  $order_id = $_POST['user_order_id'];		  	  
		  	  $this->ec->changeOrderStatus($order_id,$paidid); 
		  	  //$this->ec->sendBonus($order_id);
		  	  $order = $this->ec->getOrderInfo($order_id);
		  	  $customer_id = $order['customer_id'];
		  	  $this->ec->sendPaymentDoneMessage($customer_id,$order_id);
		  	  	
		  } else {
		  	  echo 'no-'.$secret_key;
		  }		  		 
		  // ���������� ����� �� email ����������		  	  
		}
		exit;	
	}	
}

?>