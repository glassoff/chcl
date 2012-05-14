<?php 

$ec_base = $modx->config['base_path']."assets/snippets/ecart";
$ec_lang_file = $ec_base.'/langs/'.$lang.'.php';

if (file_exists($ec_lang_file)) { 
    include($ec_lang_file);
}
include_once("$ec_base/ecart.inc.php");
if (class_exists('eCart')) {
   $ec = new eCart();
} else {
   return 'error: ecart class not found';
}

$ec->init();
$ec->lang = $ec_lang;

$cart = $ec->cart ? $ec->cart : $ec->temp_cart;

if (is_array($cart) && sizeof($cart) > 0){
	return '';
}
else{
	return 1;
}

?>
