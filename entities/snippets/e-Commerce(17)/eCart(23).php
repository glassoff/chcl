<?php 

/*
::::::::::::::::::::::::::::::::::::::::
 Snippet name: ecart
 Short Desc: builds site navigation
 Version: 2.0
 Authors: 
    Kyle Jaebker (muddydogpaws.com)
    Ryan Thrash (vertexworks.com)
 Date: February 27, 2006
::::::::::::::::::::::::::::::::::::::::
Description:
    Totally refactored from original DropMenu nav builder to make it easier to
    create custom navigation by using chunks as output templates. By using templates,
    many of the paramaters are no longer needed for flexible output including tables,
    unordered- or ordered-lists (ULs or OLs), definition lists (DLs) or in any other
    format you desire.
::::::::::::::::::::::::::::::::::::::::
Example Usage:
    [[ecart? type=`sidebar`]]
::::::::::::::::::::::::::::::::::::::::
*/
ini_set('display_errors', 1);
error_reporting(E_ALL); // better set to
error_reporting(E_ALL|E_STRICT);
$id = isset($id) ? $id : '';
$type = isset($type) ? $type : 'sidebar';
$paginate = isset($paginate) ? intval($paginate) : 0;
$paymentMathodId = isset($paymentMathodId) ? $paymentMathodId : '';
$paymentFormTpl = isset($paymentFormTpl) ? $paymentFormTpl : '';
$lang = isset($lang) ? $lang : 'ru';
$cmd = isset($_REQUEST[$ec->config['cmdVarName']]) ? $_REQUEST[$ec->config['cmdVarName']] : '';
$successPageId = isset($successPageId) ? $successPageId : '';
$cartHomeId = isset($cartHomeId) ? $cartHomeId : $modx->config['site_start'];
$ec_base = $modx->config['base_path']."assets/snippets/ecart";
$ec_lang_file = $ec_base.'/langs/'.$lang.'.php';

if (file_exists($ec_lang_file)) { 
    include($ec_lang_file);
}

$modx->regClientStartupScript(MODX_SITE_URL . "assets/templates/cd/js/jquery-1.4.4.min.js");
$modx->regClientStartupScript(MODX_SITE_URL . "assets/templates/cd/js/tooltip.js");
//$modx->regClientStartupScript(MODX_SITE_URL . "assets/templates/cd/js/thickbox.js");
//$modx->regClientStartupScript(MODX_SITE_URL . "?id=2959"); 
$modx->regClientStartupScript(MODX_SITE_URL . "assets/templates/cd/js/cart.js"); 
//$modx->regClientCSS(MODX_SITE_URL . "assets/templates/cd/css/thickbox.css"); 
//$modx->regClientStartupScript(MODX_SITE_URL . "assets/templates/cd/js/hover-zoom.js");

$modx->regClientCSS(MODX_SITE_URL . "assets/templates/kidsdream/jqzoom/css/jquery.jqzoom.css");
$modx->regClientStartupScript(MODX_SITE_URL . "assets/templates/kidsdream/jqzoom/js/jquery.jqzoom-core-pack.js");

$modx->regClientStartupScript(MODX_SITE_URL . "assets/templates/cd/js/jquery.tools.min.js");


$is_ajax = false;
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') { 
    $is_ajax = true;
}

//Include a custom config file if specified
include_once("$ec_base/ecart.inc.php");
if (class_exists('eCart')) {
   $ec = new eCart();
} else {
   return 'error: ecart class not found';
}

$ec->init();
$ec->lang = $ec_lang;
// ecart config;
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

$user_filter = 'all';
$user_sort = 'date';
$user_sort_dir = 'DESC';

if (isset($_REQUEST['uof']) && isset($_REQUEST['uos']) && isset($_REQUEST['uosd'])) {
    $_SESSION['uo']['f'] = $_REQUEST['uof']; 
    $_SESSION['uo']['s'] = $_REQUEST['uos'];
    $_SESSION['uo']['d'] = $_REQUEST['uosd'];
    $start = 0;
}    

if (isset($_SESSION['uo'])) {
    $uo = $_SESSION['uo'];
    $user_filter = !empty($uo['f']) ? $uo['f']:'';  
    $user_sort = !empty($uo['s']) ? $uo['s']:'';
    $user_sort_dir = !empty($uo['d']) ? $uo['d']:'';
    $ec->params['odir'] = !empty($uo['d']) ? $uo['d'] : $ec->params['odir'];
    $f_pre = !empty($ec->params['ofilter']) ? ' ' : '';  
    $s_pre = !empty($ec->params['osort']) ? ',' : '';   
}

$modx->setPlaceholder('uof',$user_filter);
$modx->setPlaceholder('uos',$user_sort);
$modx->setPlaceholder('uosd',$user_sort_dir);


switch ($user_filter) {
    case 'paid':$ec->params['ofilter'] = 'o.paid:1';break; 
    case 'notpaid':$ec->params['ofilter'] = 'o.paid:0';break;
    case 'confirmed':$ec->params['ofilter'] = 'o.confirmed:1';break;
    case 'notconfirmed':$ec->params['ofilter'] = 'o.confirmed:0';break;    
    case 'all':$ec->params['ofilter'] .= '';break;
}

switch ($user_sort) {
    case 'date':$ec->params['osort'] = "o.order_date $user_sort_dir "; break; 
    case 'amount':$ec->params['osort'] = "o.amount  $user_sort_dir ";break;
    case 'quantity':$ec->params['osort'] = "o.quantity  $user_sort_dir ";break;
    case 'status':$ec->params['osort'] = "o.status  $user_sort_dir ";break;   
}

   

//get user templates
$ec->templates = array(
    'messageTpl' => isset($messageTpl)? $messageTpl : 'messageTpl',  
    'message1Tpl' => isset($message1Tpl)? $message1Tpl : 'message1Tpl',
    'pagerTpl' => isset($pagerTpl)? $pagerTpl : 'pagerTpl',
    'cartOrderOuterTpl' => isset($cartOrderOuterTpl) ? $cartOrderOuterTpl : '',  
    'cartOuterTpl' => isset($cartOuterTpl) ? $cartOuterTpl : '',
    'cartOrderBonusTpl' => isset($cartOrderBonusTpl) ? $cartOrderBonusTpl : '',    
    'cartConfirmOrderTpl' => isset($cartConfirmOrderTpl) ? $cartConfirmOrderTpl : '',
    'cartRowTpl' => isset($cartRowTpl) ? $cartRowTpl : '',
    'cartOrdersOuterTpl' => isset($cartOrdersOuterTpl) ? $cartOrdersOuterTpl : '',
    'cartOrdersRowTpl' => isset($cartOrdersRowTpl) ? $cartOrdersRowTpl : '',
    'cartOrdersOuterTpl' => isset($cartOrdersOuterTpl) ? $cartOrdersOuterTpl : '',
    'cartUserOrderRowTpl' => isset($cartUserOrderRowTpl) ? $cartUserOrderRowTpl : '',
    'cartUserOrderOuterTpl' => isset($cartUserOrderOuterTpl) ? $cartUserOrderOuterTpl : '',
    'cartUserOrderStatusTpl' => isset($cartUserOrderStatusTpl) ? $cartUserOrderStatusTpl : '',
    'cartOrderRowTpl' => isset($cartOrderOuterTpl) ? $cartOrderRowTpl : '',
    'cartSideBarTpl' => isset($cartSideBarTpl) ? $cartSideBarTpl : '',
    'cartPaymentOuterTpl' => isset($cartPaymentOuterTpl) ? $cartPaymentOuterTpl : '',
    'cartPaymentRowTpl' => isset($cartPaymentRowTpl) ? $cartPaymentRowTpl : '',
    'cartAddressTpl' => isset($cartAddressTpl) ? $cartAddressTpl : '',   
    'paymentCurrencyFormTpl' => isset($paymentCurrencyFormTpl) ? $paymentCurrencyFormTpl : '',
    'paymentFormTpl' => isset($paymentFormTpl) ? $paymentFormTpl : '',
    'successAddToCartTpl' => isset($successAddToCartTpl) ? $successAddToCartTpl : 'successAddToCartTpl'    
);
//Process ecart;
//comands;
if (isset($_REQUEST['addtocart'])) $do = 'addtocart';
elseif (isset($_REQUEST['updatecart'])) $do = 'updatecart';
elseif (isset($_REQUEST['placeorder'])) $do = 'placeorder';
else $do = '';

$ec->init();

switch ($type) {  
    case "checkout": 
        if (isset($_SESSION['user_order_id']) || isset($_REQUEST['user_order_id'])) {
            
            if (isset($_SESSION['user_order_id'])) {
                $order_id = mysql_escape_string($_SESSION['user_order_id']);
                unset($_SESSION['user_order_id']);
            } elseif (isset($_REQUEST['user_order_id'])) {
                $order_id = mysql_escape_string($_REQUEST['user_order_id']);                       
            } else {
                $url = $modx->makeUrl($modx->config['site_start']);
                $modx->sendRedirect($url,0,'REDIRECT_HEADER'); 
                return;         
            }
                                             
            $order_datas = $ec->getOrderInfo($order_id);            
            if ($order_datas == false) {
                $url = $modx->makeUrl($modx->config['site_start']);
                $modx->sendRedirect($url,0,'REDIRECT_HEADER'); 
                return;
            }            
            ##            
            /*if (!$modx->getLoginUserID('web')) {
                $_SESSION['AFTER_LOGIN_GO_URL'] = $modx->config['server_protocol'].'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];                   
                $url = $modx->makeUrl($ec->params['mustloginpageid']);                
                //session_write_close();
                $modx->sendRedirect($url,0,'REDIRECT_HEADER'); 
                return;
            } */           
            
            if ($order_datas['paid'] == '1') {            
                $messageTpl = $ec->getTemplate($ec->templates['messageTpl']);               
                $output = str_replace('[+message+]',$ec->lang[16], $messageTpl);
                return $output;
            }
            
			$output = $ec->buildCheckoutPage($order_id);
			            
            /*$paymentMethodId = $order_datas['payment_type'];
            include_once("$ec_base/payments/payment".$paymentMethodId.".inc.php"); 
            $pm = new Payment($paymentMethodId,$ec);
            $pm->order = $order_datas;
            $output = $pm->buildPaymentForm($order_id);
            if ($_SESSION['EC_ORDER_DETAILS_EMAILED']) {           
                unset($_SESSION['EC_ORDER_DETAILS_EMAILED']);
                $messageTpl = $ec->getTemplate($ec->templates['messageTpl']);               
                $message = str_replace('[+message+]',$ec->lang[11], $messageTpl);
                $output = str_replace('[+message+]',$message, $output);             
            }*/   
            return $output;
        }      
   case "confirmorder": 
        $output = $ec->confirmOrder(); 
        return $output;  
   case "payment":       
        if (!empty($paymentMethodId)) {
            $paymentMathodId = intval($_REQUEST['payment_method']);
            include_once("$ec_base/payments/payment".$paymentMethodId.".inc.php");
            $pm = new Payment($paymentMethodId,$ec);   
            $output = $pm->processPayment();
            return $output;
        }          
    case "sidebarcart":            
        $output = $ec->buildSideBarCart();        
        return $output; 
    case "orders":   
        $start = 0;
        $stop = 0;    
        if($paginate == 1) {            
            $start = (isset($_GET['start'])) ? intval($_GET['start']) : 0;  
            if (isset($_REQUEST['uos'])) {                  
                $start = 0;
            } 
            $total = (isset($total)) ? intval($total) : 'all'; 
            $display = (isset($display)) ? intval($display) : 'all'; 
            $count = $ec->getOrdersCount($ec->user['id']);                  
            $total = ($total == "all") ? $count : min($total,$count);  
            $pagerlinkcount = (isset($pagerlinkcount)) ? intval($pagerlinkcount) : 9;              
            $display = ($display == "all") ? min($count,$total) : min($display,$total);
            $stop = min($total-$start,$display);            
            $paginateSplitterCharacter = isset($paginateSplitterCharacter)? $paginateSplitterCharacter : $ec->lang['button_splitter']; 
            $tplPaginatePrevious = isset($tplPaginatePrevious)? $ditto->template->fetch($tplPaginatePrevious) : $ec->lang['prev'];     
            $tplPaginateNext = isset($tplPaginateNext)? $ditto->template->fetch($tplPaginateNext) : $ec->lang['next'];     
            $ec->paginate($start, $stop, $total,$pagerlinkcount, $display, $paginateAlwaysShowLinks, $tplPaginateNext, $tplPaginatePrevious, $paginateSplitterCharacter);
        } else {
            $total = (isset($total)) ? intval($total) : 'all';
            if ($total == 'all') $stop = 0; else $stop = $stop;
            $start = 0;
        }       
        $output = $ec->buildOrdersList($start,$stop);        
        return $output;   
    case "orderdetails":            
        if (isset($_REQUEST['orderid'])) {
            $orderid = $_REQUEST['orderid'];
            $output = $ec->buildOrderDetails($orderid);        
            return $output;      
        }     
    case "orderstatus":            
        if (isset($_REQUEST['orderid'])) {
            $orderid = $_REQUEST['orderid'];
        } else {
            $orderid = '';
        }
        $output = $ec->buildOrderStatus($orderid);        
        return $output;      
    
    case "bonus":            
        if (isset($_REQUEST['bonuscode'])) {
            $bonuscode= $_REQUEST['bonuscode'];
        } else {
            $bonuscode = '';
        }
        $output = $ec->buildOrderBonus($bonuscode);        
        return $output;     
        
               
    case "addtocart":       
        switch ($do) {        
            case "addtocart": {            
                if (isset($_POST['item'])) {                                            
                    $ec->addItemToCart();              
                }                 
                if (isset($_SESSION['catalog_last_page'])) 
                $modx->sendRedirect(@$_SESSION['catalog_last_page'],0,'REDIRECT_HEADER');
                else 
                $modx->sendRedirect($modx->makeURL($modx->config['site_start']),0,'REDIRECT_HEADER');
                return;                 
            }
        }         
    case "pagecart":       
        switch ($do) {        
            case "addtocart": {
                if (isset($_POST['items'])) {  
                    //die('add1');                                           
                    $ec->addItemToCart();
                                                        
                    if (isset($_SESSION['catalog_last_page'])) 
                    $return_url = @$_SESSION['catalog_last_page'];
                    else 
                    $return_url = $modx->makeURL($modx->config['site_start']);
                    
                    $output = $ec->buildPageCart();              
                    $message = $ec->getTemplate($ec->templates['successAddToCartTpl']);    
                    $message = str_replace('[+message+]',$ec->lang['ec_item_success_added'],$message);
                    if ($is_ajax){
                        return $ec->lang['ec_item_success_added'];
                    }    
                    $message = str_replace('[+return_url+]',$return_url,$message);
                    $output = str_replace('[+message+]',$message,$output);
                    return $output;
                } else {
                    $output = $ec->buildPageCart();
                    return $output;
                }                   
            };break;              
            case "updatecart":  
                //die('update');
                 $ec->updateCart();
                 ##
                 if ($is_ajax){
                    return $ec->buildPageCart();
                 }
                 
                 $url = $modx->makeURL($cartHomeId);
                 $modx->sendRedirect($url,0,'REDIRECT_HEADER');       
                 return;       
            // show ckeckout form for step1
                                    
            default:
                $output = $ec->buildPageCart();
                return $output;                       
        }  
    case "makeorder": 
        switch ($do) {        
            case "addtocart": {            
                if (isset($_POST['item'])) {                                            
                    $ec->addItemToCart();                                    
                    $url = $modx->makeURL($cartHomeId);
                    $modx->sendRedirect($url,0,'REDIRECT_HEADER');
                    return;
                } else {
                    $output = $ec->buildPageCart();
                    return $output;
                }                
            };break;              
            case "updatecart":  
                //die ('order update');                    
                 $ec->updateCart();   
                 if ($is_ajax){
                    return $ec->buildOrderPage();
                 }                      
                 $url = $modx->makeURL($cartHomeId);
                 $modx->sendRedirect($url,0,'REDIRECT_HEADER');      
                 return;        
            // show ckeckout form for step1
                                    
            default:    
                $output = $ec->buildOrderPage();
                return $output;                       
        } 
    case "makeorder_not_user":
        switch ($do){
            default:
                $output = $ec->buildNotUserOrderPage();
                return $output;                 
        }
}




?>

?>
