<?php

require_once('payment.class.php');

class noncashPayment extends Payment
{
	public $formTpl = 'noncashPaymentFormTpl';
	
	public function renderForm()
	{
		global $modx;
		
		$output = '';
		$output .= $modx->parseChunk($this->formTpl, array(
			'payment.name' => $this->order['customer_sname'],
			'payment.address' => $this->getAddress()
		), '[+', '+]');
		return $output;
	}
	
	private function getAddress()
	{
		$o = array();
		
		$o[] = $this->order['customer_region'];
		
		if($this->order['customer_region'] != '������'){
			$o[] = $this->order['customer_state'];
		}
		
		$o[] = '��. '.$this->order['customer_street'];
		
		$o[] = '�. '.$this->order['customer_dom'];
		
		if($this->order['customer_korpus']){
			$o[] = '����. '.$this->order['customer_korpus'];
		}
		
		if($this->order['customer_kvartira']){
			$o[] = '��. '.$this->order['customer_kvartira'];
		}
		
		return implode(', ', $o); 
	}
	
	public function postForm($data)
	{
		global $modx;
		
		
	}
}
?>