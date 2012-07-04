<?php

require_once('Payment.class.php');

class yandexPayment extends Payment
{
	protected $formAction = 'https://money.yandex.ru/quickpay/confirm.xml';
	protected $formTarget = "_blank";
	
	public function renderForm()
	{
		return '
		<script>
			$(function(){
				$("#'.$this->getFormId().'").submit(function(){
					var params = "";
					$(this).find("[name^=payment]").each(function(){
						params += $(this).attr("name") + "=" + $(this).attr("value") + "&";
					});
					document.location = document.location + "&"+params;
					return true;
				});
			});
		</script>
		
	<input type="hidden" value="41001115959633" name="receiver">
	<input type="hidden" value="" name="label">
	<input type="hidden" value="������ ������/������" name="FormComment">
	<input type="hidden" value="������ ������/������" name="short-dest">
	<input type="hidden" value="false" name="writable-targets">
	<input type="hidden" value="false" name="writable-sum">
	<input type="hidden" value="false" name="comment-needed">
	<input type="hidden" value="shop" name="quickpay-form">
	
	<p>�������� ��������� ������� ������ ������ ���������� 0.5% �� ����� �������.</p><br>
	
	<table class="b-form__grid">
		<tr>
			<td class="b-form__label"><label>���������� �������:</label></td>
			<td class="b-form__field">
				<div class="b-form__label">'.$this->getTitle().'</div>
				<input type="hidden" value="'.$this->getTitle().'" name="targets">
			</td>
		</tr>
		<tr>
			<td class="b-form__label"><label>�����:</label></td>
			<td class="b-form__field">
				<input type="hidden" value="'.$this->getSum().'" name="sum" >
				'.$this->getSum().' ���.
			</td>
		</tr>
		<tr class="b-form__buttons">
			<td style="text-align: right;">
				<a href="https://money.yandex.ru/" target="_blank" class="b-widget-commercial__logo-link"><img height="32px" alt="Yandex.Money" src="https://money.yandex.ru/img/ym_logo.gif" class="b-widget-commercial__logo-img"></a>
			</td>
			<td>
				<input type="submit" class="b-button__input" value="��������" name="submit-button">
				<br>
				<em>� ����� ���� ��������� ���� ��������� �������, ��� �� ������� ���������� ������</em>
			</td>
		</tr>
	</table>
		';
	}
	
	protected function getSum()
	{
		return $this->order['amount'] + ($this->order['amount'] / 100 * 0.5);
	}
	
	protected function getTitle()
	{
		return '�������� ������� ������� ������ chcl.ru: '.parent::getTitle();
	}
	
}

?>