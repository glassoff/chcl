<?php
	
class Payment {

	var $params;
	var $paymentFormTpl = 'MailPayFormTpl';
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
		
		return $payment_form;			
	}
	
	function processPayment() {
		global $ec,$modx;		
		exit;	
	}	
}

?>