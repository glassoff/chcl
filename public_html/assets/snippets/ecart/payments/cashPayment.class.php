<?php

require_once('Payment.class.php');

class cashPayment extends Payment
{
	public function isActive()
	{
		//������ ��� ���������
		return ($this->order['delivery_type'] == 2 || $this->order['delivery_type'] == 4);
	}
}

?>