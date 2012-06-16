<?php
class Payment
{
	protected $order = array();
	protected $formAction = '';
	protected $formTarget = '';
	
	public function __construct($params, $order = array())
	{
		foreach($params as $n => $v){
			$this->$n = $v;
		}
		
		$this->order = $order;			
	}

	protected function getFormId()
	{
		return 'paymentForm_'.$this->id;
	}
	/*
	 * ����� �����
	 */
	public function showForm()
	{
		return $this->description . '<br>' .
		'
			<form id="'.$this->getFormId().'" method="post" action="'.$this->formAction.'" target="'.$this->formTarget.'">
				'.$this->renderHiddenFields().'
				'.$this->renderForm().'
			</form>		
		';		
	}
		
	/*
	 * ���������� �����
	 */
	protected function renderForm()
	{
		return '
			<input type="submit" value="����������� ��������� ������ ������" />	
		';
	}
	
	/*
	 * ������������ ������ ���� �����
	 */
	protected function renderHiddenFields()
	{
		return '
			<input type="hidden" name="payment[id]" value="'.$this->id.'" />
			<input type="hidden" name="payment[order_id]" value="'.$this->order['id'].'" />		
		';
	}
	
	public function validateForm()
	{
		return true;	
	}
	
	public function postForm($data)
	{
		$this->setPaymentType();
		
		return '
			<div class="cart-comment" style="color:#000;">
				<p>�������, �� ������� ������ ������ "'.$this->name.'".</p>
				<p>��� ����������� �������, �� �������� ��� ����� ����� ��������� ������.</p>
				<p>���� �� ������ ������� ������ ������ ������, �� ������ ����� ���� ����� � ������ ����� ������� � ����� ������� � ������ �������� ������.</p>
			</div>
			<h3 style="font-size:16px;">���������� �� ������ �������� "'.$this->name.'"</h3>
			<div>
				'.$this->description.'
			</div>
			<br>
		';
	}
	
	protected function setPaymentType()
	{
		global $modx;
		
		$sql = "UPDATE modx_site_ec_orders SET payment_type='".$this->id."' WHERE id='".$this->order['id']."' AND paid='0'";
		
		return $modx->db->query($sql);
	}
	
	protected function getTitle()
	{
		return '����� � ' . $this->order['id'] . ' �� ' . date('d.m.Y', $this->order['order_date']);
	}
	
	public function isActive()
	{
		return true;
	}	
}
?>