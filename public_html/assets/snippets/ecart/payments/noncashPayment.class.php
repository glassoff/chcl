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
		
		$this->setPaymentType();
		
		foreach((array)$data as $k => $v){
			$data[$k] = iconv('UTF-8', 'WINDOWS-1251', $v);
		}

		$amount = explode('.', $this->order['amount']);		
		return $modx->parseChunk('pd4', array(
			'pd4.title' => '����� � ' . $this->order['id'] . ' �� ' . date('d.m.Y', $this->order['order_date']),
			'pd4.name' => $data['name'],
			'pd4.address' => $data['address'],
			'pd4.summ.rub' => $amount[0],
			'pd4.summ.kop' => $amount[1]
		), '[+', '+]');
		
	}
}
?>