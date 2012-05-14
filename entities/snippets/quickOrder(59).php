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

$ec->params = array(
    'sort' => isset($sort) ? $sort : '',
    'filter' => isset($filter) ? $filter : '',  
    'dir' => isset($dir) ? $dir : '',
    'osort' => isset($osort) ? $osort : 'o.id',
    'ofilter' => isset($ofilter) ? $ofilter : '',  
    'odir' => isset($odir) ? $odir : '',
    'itemhomeid' => isset($itemhomeid) ? intval($itemhomeid) : '',
    'orderdetailshomeid' => isset($orderdetailshomeid) ? intval($orderdetailshomeid) : '',
    'confirmorderhomeid' => isset($confirmorderhomeid) ? intval($confirmorderhomeid) : '',
    'checkouthomeid' => isset($checkouthomeid) ? intval($checkouthomeid) : '',
    'placeorderpageid' => isset($placeOrderPageId) ? intval($placeOrderPageId) : '',
    'mustloginpageid' => isset($mustLoginPageId) ? intval($mustLoginPageId) : ''
);

$ec->quickPlaceOrder();

?>
