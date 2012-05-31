<?php
class Payment
{
	protected $order = array();
	
	public function __construct($params, $order = array())
	{
		foreach($params as $n => $v){
			$this->$n = $v;
		}
		
		$this->order = $order;			
	}

	public function showForm(){
		return $this->description . $this->renderForm();
	}
		
	public function renderForm(){

	}
	
	public function postForm($data){

	}	
}
?>