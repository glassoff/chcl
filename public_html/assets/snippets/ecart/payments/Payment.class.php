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
	 * Вывод формы
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
	 * Содержимое формы
	 */
	protected function renderForm()
	{
		return '
			<input type="submit" value="Подтвердить выбранный способ оплаты" />	
		';
	}
	
	/*
	 * Обязательные срытые поля формы
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
				<p>Спасибо, Вы выбрали способ оплаты "'.$this->name.'".</p>
				<p>При безналичном расчете, мы отправим вам заказ после получения оплаты.</p>
				<p>Если Вы решите выбрать другой способ оплаты, то можете найти этот заказ в списке ваших заказов и снова перейти к выбору способов оплаты.</p>
			</div>
			<h3 style="font-size:16px;">Информация об оплате способом "'.$this->name.'"</h3>
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
		return 'Заказ № ' . $this->order['id'] . ' от ' . date('d.m.Y', $this->order['order_date']);
	}
	
	public function isActive()
	{
		return true;
	}	
}
?>