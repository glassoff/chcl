<?php 

foreach((array)$_GET['payment'] as $k => $v){
	$_GET['payment'][$k] = iconv('UTF-8', 'WINDOWS-1251', $v);
}

return $modx->parseChunk('pd4', array(
	'pd4.title' => '',
	'pd4.name' => $_GET['payment']['name'],
	'pd4.address' => $_GET['payment']['address'],
	'pd4.summ.rub' => '',
	'pd4.summ.kop' => ''
), '[+', '+]');

?>