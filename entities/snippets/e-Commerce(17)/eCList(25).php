<?php 

$type = isset($type) ? $type : 'list';

$eCListID = (!isset($id)) ? "" : $id."_";
$GLOBALS["eCListID"] = $eCListID;
$ecl_base = $modx->config['base_path']."assets/snippets/eclist";
$ecl_lang_file = $ecl_base.'/langs/'.(isset($lang) ? $lang : 'ru').'.php';
if (file_exists($ecl_lang_file)) {
    include($ecl_lang_file);
}
//Include a custom config file if specified
include_once("$ecl_base/eclist.inc.php");
if (class_exists('eCList')) {
   $ecl = new eCList();
} else {
    return 'error: ZCart class not found';
}
$ecl->lang = $ecl_lang;
// Zcart params;

$ecl->params = array(   
    'eclistID' => isset($eclistID) ? $eclistID : '',
    'itemhomeid' => isset($itemHomeId) ? $itemHomeId : '',
    'itemscreenshotshomeid' => isset($itemScreenshotsHomeId) ? $itemScreenshotsHomeId : '',        
    'tabid' => isset($tabid) ? $tabid : '',
    'lang' => isset($lang) ? $lang : 'ru',
    'itemId' => isset($itemId) ? $itemId : '0',
    'parents' => (isset($parents) && !empty($parents)) ? $parents : $_POST['parents'],
   'parents1' => (isset($filter_gr) && !empty($filter_gr)) ? 2906 : '',
   'parents2' => (isset($filter_gr2) && !empty($filter_gr2)) ? 2907 : '',


    'display' => isset($display) ? $display : 'all',
    'paginate' => isset($paginate) ? $paginate : 0,
    'filter' => (isset($filter) && !empty($filter)) ? $filter : '',
  'filter_price' => (isset($filter_price) && !empty($filter_price)) ? $filter_price : '',
  'filter_price2' => (isset($filter_price2) && !empty($filter_price2)) ? $filter_price2 : '',
 'filter_gr' => (isset($filter_gr) && !empty($filter_gr)) ? $filter_gr : '',
 'filter_gr2' => (isset($filter_gr2) && !empty($filter_gr2)) ? $filter_gr2 : '',
    'sort' => isset($sort) ? $sort : 'id',
    'dir' => isset($dir) ? $dir : 'DESC',
    'accsort' => isset($accsort) ? $accsort : 'id',
    'accdir' => isset($accdir) ? $accdir : 'DESC',
    'columns' => isset($columns) ? $columns : 1,
    'accColumnsInRow' => isset($accColumnsInRow) ? $accColumnsInRow : 1    
);

//get user templates
$ecl->templates = array(
    'messageTpl' => isset($messageTpl)? $messageTpl : 'messageTpl',
    'pagerTpl' => isset($pagerTpl)? $pagerTpl : 'pagerTpl',
    'message1Tpl' => isset($message1Tpl)? $message1Tpl : 'message1Tpl',    
    'outerTpl' => isset($outerTpl)? $outerTpl : '@CODE:<table>[+ecl.wrapper+]</table>',
    'rowTpl' => isset($rowTpl)? $rowTpl : '',
    'cellTpl' => isset($cellTpl)? $cellTpl : '',
    'searchOuterTpl' => isset($searchOuterTpl)? $searchOuterTpl : '',
    'searchRowTpl' => isset($searchRowTpl)? $searchRowTpl : '',
    'itemTpl' => isset($itemTpl)? $itemTpl : '',
    'itemScreenshotsTpl' => isset($itemScreenshotsTpl)? $itemScreenshotsTpl : '',
    'tabOuterTpl' => isset($tabOuterTpl)? $tabOuterTpl : '',   
    'tabRowTpl' => isset($tabRowTpl)? $tabRowTpl : '',
    'tabCellTpl' => isset($tabCellTpl)? $tabCellTpl : '', 
    'accOuterTpl' => isset($accOuterTpl)? $accOuterTpl : '',
    'accRowTpl' => isset($accRowTpl)? $accRowTpl : '',
    'accCellTpl' => isset($acccellTpl)? $acccellTpl : '',
    'similarItemRowTpl' => isset($similarItemRowTpl)? $similarItemRowTpl : ''
    
);
//Process 
$item_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$ecl->init();
$ecl->lang = $ecl_lang;

## filters
    $user_filter = '';
    $user_sort = '';
    $user_sort_dir = '';
 $user_filter_price = '';
 $user_filter_price2 = '';
 $user_filter_gr= '';
 $user_filter_gr2 = '';
 
    
    if (isset($_REQUEST['ucf']) && isset($_REQUEST['ucs']) && isset($_REQUEST['ucsd'])) {
         
        $_SESSION['uc']['s'] = $_REQUEST['ucs'];// по какому параметру сортировка
        $_SESSION['uc']['d'] = $_REQUEST['ucsd'];//сортировка по возрастанию или убыванию
  $_SESSION['uc']['f_price'] ='';
  $_SESSION['uc']['f_price2'] ='';
  $_SESSION['uc']['f_gr'] = $_REQUEST['ucf_gr1']; 
  $_SESSION['uc']['f_gr2'] = $_REQUEST['ucf_gr2']; 
        $start = 0;
    }    
    $_SESSION['uc']['f'] = $_REQUEST['ucf'];

     if (isset($_REQUEST['ucf_price'])) $_SESSION['uc']['f_price'] = $_REQUEST['ucf_price'];// цена от
     if (isset($_REQUEST['ucf_price2'])) $_SESSION['uc']['f_price2'] = $_REQUEST['ucf_price2'];//цена до
     if (isset($_REQUEST['ucf_gr'])) $_SESSION['uc']['f_gr'] = $_REQUEST['ucf_gr']; 
    if (isset($_REQUEST['ucf_gr2'])) $_SESSION['uc']['f_gr2'] = $_REQUEST['ucf_gr2']; 

   
    if (isset($_SESSION['uc'])) {
        $uc = $_SESSION['uc'];
        $user_filter = !empty($uc['f']) ? $uc['f']:'';  
$user_filter_price = !empty($uc['f_price']) ? $uc['f_price']:'';
$user_filter_price2 = !empty($uc['f_price2']) ? $uc['f_price2']:'';

$user_filter_gr = !empty($uc['f_gr']) ? $uc['f_gr']:'';
$user_filter_gr2 = !empty($uc['f_gr2']) ? $uc['f_gr2']:'';

        $user_sort = !empty($uc['s']) ? $uc['s']:'pagetitle';
        $user_sort_dir = !empty($uc['d']) ? $uc['d']:'';
        $ecl->params['dir'] = !empty($uc['d']) ? $uc['d'] : $ecl->params['dir'];
        $f_pre = !empty($ecl->params['filter']) ? ' ' : '';  
        $s_pre = !empty($ecl->params['sort']) ? ',' : '';   
    } else {
        $s_pre = !empty($ecl->params['sort']) ? ',' : '';
        $user_sort = 'rating';
        $user_sort_dir = ' DESC ';
        
    }
    
    $modx->setPlaceholder('ucf',$user_filter);//какие товары показать (в наличии, новинки и пр.)
   $modx->setPlaceholder('ucf_price',$user_filter_price);// цена от
  $modx->setPlaceholder('ucf_price2',$user_filter_price2);//цена до
   $modx->setPlaceholder('ucf_gr',$user_filter_gr);//от 0 до 1 года
  $modx->setPlaceholder('ucf_gr2',$user_filter_gr2);//от 1 до 3 лет
    $modx->setPlaceholder('ucs',$user_sort);// по какому параметру сортировка
    $modx->setPlaceholder('ucsd',$user_sort_dir);//сортировка по возрастанию или убыванию
    
    $parents = $ecl->params['parents'] ? $ecl->params['parents'] : $_POST['parents'];
    $modx->setPlaceholder('parents', $parents);
    $modx->setPlaceholder('search', $_REQUEST['type']=='search' ? 'search' : '');
    
    switch ($user_filter) {
        case 'instock':$ecl->params['filter'] = $f_pre.'si.sku <> 0';break; 
        case 'popular':$ecl->params['filter'] .= $f_pre.'si.popular:1';break;
        case 'sell':$ecl->params['filter'] .= $f_pre.'si.sell:1';break;
        case 'new':$ecl->params['filter'] .= $f_pre.'si.new:1';break;    
    case 'action':$ecl->params['filter'] .= $f_pre.'si.action:1';break;    
        case 'all':$ecl->params['filter'] .= '';break;
    }

    //die($ecl->params['sort']);
    switch ($user_sort) {
     
        case 'price':$ecl->params['sort'] = "si.retail_price  $user_sort_dir ".$s_pre.$ecl->params['sort'];break;
        case 'pagetitle':$ecl->params['sort'] = "pagetitle  $user_sort_dir ".$s_pre.$ecl->params['sort'];break;   
    }

$price1=$_POST['ucf_price'];
$price2=$_POST['ucf_price2'];


if ($user_filter_price>0)   {$ecl->params['filter_price'] = " si.price>=$price1 ";} else  {$ecl->params['filter_price'] = ''; }
if ($user_filter_price2>0)   {$ecl->params['filter_price2'] = " si.price<=$price2 ";} else  {$ecl->params['filter_price2'] = '';}

##
	$modx->setPlaceholder('nav.ucf',$user_filter);
	$modx->setPlaceholder('nav.ucf_price',$price1);
	$modx->setPlaceholder('nav.ucf_price2',$price2);
	$modx->setPlaceholder('nav.ucf_gr',$_POST['ucf_gr']);
	$modx->setPlaceholder('nav.ucf_gr2',$_POST['ucf_gr2']);
	$modx->setPlaceholder('nav.ucs',$user_sort);
	$modx->setPlaceholder('nav.ucsd',$user_sort_dir);
## end filters

if ($type == 'list') {        

    $ecl->init();
     
    $start_url = (isset($_GET['start'])) ? '?start='.intval($_GET['start']) : '';
    $_SESSION['catalog_last_page'] =  $modx->makeUrl($modx->documentIdentifier).$start_url;   
    if($paginate == 1) {
        $start = (isset($_GET['start'])) ? intval($_GET['start']) : 0; 
        if (isset($_REQUEST['ucf']) && !isset($_GET['start'])) $start = 0;   
        $count = $ecl->itemCount;  
        $pagerlinkcount = (isset($pagerlinkcount)) ? intval($pagerlinkcount) : 9;     
        $total = ($total == "all") ? $count : min($total,$count);    
        $display = ($display == "all") ? min($count,$total) : min($display,$total);      
        $stop = min($total-$start,$display);       
        $paginateSplitterCharacter = isset($paginateSplitterCharacter)? $paginateSplitterCharacter :  $ecl->lang['button_splitter']; 
        $tplPaginatePrevious = isset($tplPaginatePrevious)? $ecl->getTemplate($tplPaginatePrevious) :  $ecl->lang['prev'];     
        $tplPaginateNext = isset($tplPaginateNext)? $ecl->getTemplate($tplPaginateNext) : $ecl->lang['next'];   
        $ecl->paginate($start, $stop, $total,$pagerlinkcount, $display, $paginateAlwaysShowLinks, $tplPaginateNext, $tplPaginatePrevious, $paginateSplitterCharacter);
    } else {
        $total = (isset($total)) ? intval($total) : '9';
        $start = 0;
        $stop = $total;  
        $ecl->paginate($start, $stop, $total, 0, $display, $paginateAlwaysShowLinks, $tplPaginateNext, $tplPaginatePrevious, $paginateSplitterCharacter);
    }
    $output = $ecl->buildList($start,$stop);
} elseif ($type == 'blocklist') {          
    $start = 0;
    $stop =  $total; 
    $output = $ecl->buildList($start,$stop);
} elseif ($type == 'search') {
    $search = $ecl->processSearchText();
    if(!empty($search))  
    if($paginate == 1) {            
        $start = (isset($_GET['start'])) ? intval($_GET['start']) : 0; 
        $count = $ecl->getSearchResultsCount($search);  
        $pagerlinkcount = (isset($pagerlinkcount)) ? intval($pagerlinkcount) : 9;     
        $total = ($total == "all") ? $count : min($total,$count);    
        $display = ($display == "all") ? min($count,$total) : min($display,$total);      
        $stop = min($total-$start,$display);       
        $paginateSplitterCharacter = isset($paginateSplitterCharacter)? $paginateSplitterCharacter :  $ecl->lang['button_splitter']; 
        $tplPaginatePrevious = isset($tplPaginatePrevious)? $ecl->getTemplate($tplPaginatePrevious) :  $ecl->lang['prev'];     
        $tplPaginateNext = isset($tplPaginateNext)? $ecl->getTemplate($tplPaginateNext) : $ecl->lang['next'];   
        $ecl->paginate($start, $stop, $total,$pagerlinkcount, $display, $paginateAlwaysShowLinks, $tplPaginateNext, $tplPaginatePrevious, $paginateSplitterCharacter);
    } else {
        $total = (isset($total)) ? intval($total) : '9';
        $start = 0;
        $stop = $total;  
        $ecl->paginate($start, $stop, $total, 0, $display, $paginateAlwaysShowLinks, $tplPaginateNext, $tplPaginatePrevious, $paginateSplitterCharacter);
    }    
    $output = $ecl->buildSearch($search,$start,$stop);
} elseif ($type == 'itemscreenshots') {
    $output = $ecl->buildItemScreenshots($item_id);
} elseif ($type == 'itemmovie') {
    $output = $ecl->buildItemMovie($item_id);    
} else {    
    $output = $ecl->buildItem($item_id);
}
header("Cache-Control: post-check=0, pre-check=0", true);

return $output;

?>

?>
