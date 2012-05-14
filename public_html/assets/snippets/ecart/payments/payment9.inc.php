<?php
	
class Payment {

	var $params;
	var $paymentFormTpl = 'OnPayPaymentFormTpl';
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
		$payment_form = $modx->getChunk($this->paymentFormTpl);			
		$customer = $modx->getWebUserInfo($customer_id);
		$total_amount = floatval($this->order['amount']);
		$a_name = $this->order['customer_sname'];
		$a_lname = $this->order['customer_fname'];
		$a_email =$this->order['customer_email'];
			
		$phx = array(
		            
		             user_order_id => $order_id,	
					 price => $total_amount,
					 a_name => $a_name,
					 a_lname => $a_lname,
					 a_email => $a_email
					          					
					);
		
					foreach($phx as $k => $v) {
			$payment_form = str_replace('[+'.$k.'+]', $v, $payment_form); }
		
		return $payment_form;			
	}
	
	function processPayment() {
		global $ec,$modx;		
		exit;	
	}	
}

?>