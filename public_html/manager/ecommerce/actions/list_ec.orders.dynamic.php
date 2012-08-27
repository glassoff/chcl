<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_manage_orders')) {
	$e->setError(3);
	$e->dumpError();
}

$status_arr = explode(',',$order_status);
$theme = $manager_theme ? "$manager_theme/":"";
// initialize page view state - the $_PAGE object
$modx->manager->initPageViewState();


// Delete unconfirmed orders


if (isset($ec_settings['ec_notconfirmed_order_delete_days'])) {
	
	$notconfirmed_kill_days = $ec_settings['ec_notconfirmed_order_delete_days']*24*60*60;
	$curr_time = time();
	
	$del_order_sql = "SELECT id FROM ".$modx->getFullTableName("site_ec_orders");
	$del_order_sql.= " WHERE ($curr_time-order_date > $notconfirmed_kill_days) AND confirmed = '0'";	
	$del_order_results = $modx->dbQuery($del_order_sql);	
	//echo $del_order_sql.'<br>';	      
	$del_order_nums = @$modx->recordCount($del_order_results);
	if ($del_order_nums > 0) {	
		for($n=0;$n<$del_order_nums;$n++)  {
			$del_order_row = $modx->fetchRow($del_order_results);
			$del_order_id_arr[] = $del_order_row['id']; 
		}		
		if (sizeof($del_order_id_arr) > 0) 
		$del_order_ids = implode(',', $del_order_id_arr);
		$del_order_ids = str_replace(",","','",$del_order_ids);	
		$del_order_ids = "'".$del_order_ids."'";
			
		$del_order_sql = "DELETE FROM ".$modx->getFullTableName("site_ec_orders")." WHERE id IN ($del_order_ids);";
		$rs = mysql_query($del_order_sql);
		//echo $del_order_sql.'<br>';
		$del_order_sql = "DELETE FROM ".$modx->getFullTableName("site_ec_order_items")." WHERE order_id IN ($del_order_ids);";
		$rs = mysql_query($del_order_sql);
		//echo $del_order_sql.'<br>';
	}
}

if (isset($ec_settings['ec_notpaid_order_delete_days'])) {
	$curr_time = time();
	$notpaid_kill_days = $ec_settings['ec_notpaid_order_delete_days']*24*60*60;
	$del_order_sql = "SELECT id FROM ".$modx->getFullTableName("site_ec_orders");
	$del_order_sql.= " WHERE ($curr_time-order_date > $notpaid_kill_days) AND paid = 0";	
	$del_order_results = $modx->dbQuery($del_order_sql);	
	//echo $del_order_sql.'<br>';	      
	$del_order_nums = @$modx->recordCount($del_order_results);
	if ($del_order_nums > 0) {	
		for($n=0;$n<$del_order_nums;$n++)  {
			$del_order_row = $modx->fetchRow($del_order_results);
			$del_order_id_arr[] = $del_order_row['id']; 
		}		
		if (sizeof($del_order_id_arr) > 0) 
		$del_order_ids = implode(',', $del_order_id_arr);
		$del_order_ids = str_replace(",","','",$del_order_ids);	
		$del_order_ids = "'".$del_order_ids."'";
		$del_order_sql = "DELETE FROM ".$modx->getFullTableName("site_ec_orders")." WHERE id IN ($del_order_ids);";
		$rs = mysql_query($del_order_sql);
		//echo $del_order_sql.'<br>';
		$del_order_sql = "DELETE FROM ".$modx->getFullTableName("site_ec_order_items")." WHERE order_id IN ($del_order_ids);";
		$rs = mysql_query($del_order_sql);
		//echo $del_order_sql.'<br>';
	}
}	

if (isset($_REQUEST['kill_user_id'])) {
	unset($_SESSION['ec_user_id']);
} 

if (isset($_REQUEST['user_id'])) {
	$user_id = intval($_REQUEST['user_id']);
	$_SESSION['ec_user_id'] = $user_id;
	unset($_SESSION['ec_or_search']);	
} elseif(isset($_SESSION['ec_user_id'])) {
	$user_id = $_SESSION['ec_user_id'];
} else {
	$user_id = '';
}



if (isset($_POST['status']) &&
    isset($_POST['paid']) &&
    isset($_POST['from_date']) &&
    isset($_POST['to_date']) ) {  
    	  	    		
	$_SESSION['ec_order_filter']['status'] = mysql_escape_string($_POST['status']);
	$_SESSION['ec_order_filter']['confirmed'] = mysql_escape_string($_POST['confirmed']);
	$_SESSION['ec_order_filter']['paid'] = mysql_escape_string($_POST['paid']);
	$_SESSION['ec_order_filter']['fromdate'] = mysql_escape_string($_POST['from_date']);
	$_SESSION['ec_order_filter']['todate'] = mysql_escape_string($_POST['to_date']);	
	if (!empty($_SESSION['ec_order_filter']['fromdate'])) {
		list ($d, $m, $Y, $H, $M, $S) = sscanf($_SESSION['ec_order_filter']['fromdate'], "%2d-%2d-%4d %2d:%2d:%2d");
		$_SESSION['ec_order_filter']['fromdate'] = mktime($H, $M, $S, $m, $d, $Y);		
	} 
	if (!empty($_SESSION['ec_order_filter']['todate'])) {
		list ($d, $m, $Y, $H, $M, $S) = sscanf($_SESSION['ec_order_filter']['todate'], "%2d-%2d-%4d %2d:%2d:%2d");
		$_SESSION['ec_order_filter']['todate'] = mktime($H, $M, $S, $m, $d, $Y);
	}	
	unset($_SESSION['ec_or_search']);
} 
$filter_sql = '';
$fromdate = '';
$todate  ='';
$fromdate_ = '';
$todate_  ='';
$order_curr_status = 'all';
if (isset($_SESSION['ec_order_filter'])) {    		
	$order_curr_status= $_SESSION['ec_order_filter']['status'];
	$order_curr_confirmed= $_SESSION['ec_order_filter']['confirmed'];
	$order_curr_paid= $_SESSION['ec_order_filter']['paid'];
	$fromdate = !empty($_SESSION['ec_order_filter']['fromdate']) ? date("d-m-Y H:i:s",$_SESSION['ec_order_filter']['fromdate']) : "";
	$todate = !empty($_SESSION['ec_order_filter']['todate'])? date("d-m-Y H:i:s",$_SESSION['ec_order_filter']['todate']) : "";	
	$fromdate_ = $_SESSION['ec_order_filter']['fromdate'];
	$todate_ = $_SESSION['ec_order_filter']['todate'];
	
		
$filter_sql = " ";	
	
	if ($order_curr_status == 'all') $filter_sql .= ''; 
	else $filter_sql .= " and status = '".$order_curr_status."' ";
	
	if ($order_curr_confirmed == 'all') $filter_sql .= ''; 
	else $filter_sql .= " AND confirmed = '".$order_curr_confirmed."' ";
	
	if ($order_curr_paid == 'all' || empty($order_curr_paid)) $filter_sql .= ''; 
	else $filter_sql .= " AND paid = '".$order_curr_paid."' ";
	
	
	
	if (!empty($fromdate_) && !empty($todate_)) {
		if ($fromdate_ == $todate_) $filter_sql .= " AND (order_date = ".$fromdate_.")";
		else $filter_sql .= " AND (order_date >= ".$fromdate_.") AND (order_date <= ".$todate_.")";
	} elseif (!empty($fromdate_)) {
		$filter_sql .= " AND (order_date >= ".$fromdate_.")";
	} elseif (!empty($todate_)) {
		$filter_sql .= " AND (order_date <= ".$todate_.")";
	} 
} 

 

if (isset($_REQUEST['sort'])) {			
	$_SESSION['ec_or_sort'] = mysql_escape_string($_REQUEST['sort']);
	if (isset($_SESSION['ec_or_sortdir'])) {			
		if ($_SESSION['ec_or_sortdir'] == 'DESC') $_SESSION['ec_or_sortdir'] = 'ASC'; 	
		else {
			$_SESSION['ec_or_sortdir'] = 'DESC';
		}
	} else $_SESSION['ec_or_sortdir'] = 'DESC';		
} 

$sortdir = $_SESSION['ec_or_sortdir'];

if (isset($_SESSION['ec_or_sort'])) {
	$sort_field = $_SESSION['ec_or_sort'];
	$sort_sql = "$sort_field  $sortdir";		
} else {
	$sort_sql = "order_date DESC , status ";	
}

if (isset($_REQUEST['perpage'])) {			
	$_SESSION['ec_or_perpage'] = mysql_escape_string($_REQUEST['perpage']);
} 

if (isset($_SESSION['ec_or_perpage'])) {
	$perpage = $_SESSION['ec_or_perpage'];						
} else {
	$perpage = 10;	
}

if (isset($_REQUEST['search']) && intval($_REQUEST['search']) == 1 && 
	isset($_POST['ec_order_id'])) {
	$_SESSION['ec_or_search']['ec_order_id'] = mysql_escape_string($_POST['ec_order_id']);	
	unset($_SESSION['ec_user_id']);
	$ec_user_id = '';
}


// get & save listmode
$listmode = isset($_REQUEST['listmode']) ? $_REQUEST['listmode']:$_PAGE['vs']['lm'];
$_PAGE['vs']['lm'] = $listmode;

$sql = " SELECT *, IF(paid = 1,'".$_lang['ec_order_paid']."','".$_lang['ec_order_notpaid']."') as 'paid_status'," .
	   " IF(confirmed = 1,'".$_lang['ec_order_confirmed']."','".$_lang['ec_order_notconfirmed']."') as 'confirmed_name',".	
	   " os.name as status_name, pt.name as payment_m,so.id as order_id".
	   " FROM ".$modx->getFullTableName("site_ec_orders") . " so ".  
	   " INNER JOIN" .$modx->getFullTableName("ec_order_status"). " os ON  os.id = so.status ".
	   " LEFT JOIN" .$modx->getFullTableName("site_ec_payment_methods"). " pt ON  so.payment_type = pt.id ". 
	   " WHERE 1=1 ";
	
	
$sql5 = " SELECT *, IF(paid = 1,'".$_lang['ec_order_paid']."','".$_lang['ec_order_notpaid']."') as 'paid_status'," .
	   " IF(confirmed = 1,'".$_lang['ec_order_confirmed']."','".$_lang['ec_order_notconfirmed']."') as 'confirmed_name',".	
	   " os.name as status_name, pt.name as payment_m,so.id as order_id".
	   " FROM ".$modx->getFullTableName("site_ec_orders") . " so ".  
	   " INNER JOIN" .$modx->getFullTableName("ec_order_status"). " os ON  os.id = so.status ".
	   " LEFT JOIN" .$modx->getFullTableName("site_ec_payment_methods"). " pt ON  so.payment_type = pt.id ". 
	   " WHERE 1=1 and status=5";	

##
if($order_id = $_REQUEST['order_id']){
	$sql5 .= " AND so.id='$order_id' ";
}

if (!empty($user_id)) {
	$sql .= " AND so.customer_id = $user_id ";	
	$sql5 .= " AND so.customer_id = $user_id ";
}elseif (isset($_SESSION['ec_or_search'])) {
	$search_order_id = $_SESSION['ec_or_search']['ec_order_id'];	
	$sqladd = $search_order_id!="" ? " AND so.id='$search_order_id' " : "" ;	
	$sql .= $sqladd." ";
	$sql5 .= $sqladd." ";
} 	
if (!empty($filter_sql) || $order_curr_status == 'all') {
	$sql .= $filter_sql." ORDER BY $sort_sql ";
	$sql5 .= $filter_sql." ORDER BY $sort_sql ";
} else {
	$sql .= "  ORDER BY $sort_sql ";
	$sql5 .= "  ORDER BY $sort_sql ";
}		

$ar = array('curer_v_mkad' => 'Курьер по москве в пределах МКАД  / По Сочи / По Краснодару', 'vstrecha' => 'Встреча в метро','samovivoz' => 'Самовывоз', 'curer_za_mkad' => 'Курьер по москве за пределы МКАД', '1class' => 'Отправление "1 класса"', 'basic' => 'Наземная почта', 'outsea'=>'Доставка в Ближнее и Дальнее зарубежье');


if (isset($_REQUEST['importorders']) && intval($_REQUEST['importorders']) == 1) {
	$file = $base_path."assets/files/orders.txt";
	$export = fopen($file,'w');	
	$ds1 = mysql_query($sql5);
	while ($order = mysql_fetch_assoc($ds1)) {
		$line = $order['order_id'].'#';
		$line.= strftime('%d.%m.%Y %H:%I:%S',$order['order_date']).'#';
		$line.= $order['customer_fname'].' '.$order['customer_sname'].' '.$order['customer_lname'].'#';
        $line.= $order['customer_type'].' '.$order['customer_company'].'#';
		$line.= $order['payment_m'].'#';
		$line.= $order['amount'].'#';
		$line.= @$ar[$order['delivery_type']].'#';
		$line.= $order['delivery_amount'].'#';	
		$line.= $order['status'].'#';	
	
	
		$line.= "\r\n";
		
		
		$sql_ = " SELECT oi.*,si.*".
			   " FROM ".$modx->getFullTableName("site_ec_order_items") . " oi ".  
			   " LEFT JOIN" .$modx->getFullTableName("site_ec_items"). " si ON  si.id = oi.item_id ".
			   " WHERE order_id = '".$order['order_id']."'  ";
		
		$ds2 = mysql_query($sql_);
		$result_size = mysql_num_rows($ds2);
		$line_items_ = '';
		while ($item = mysql_fetch_assoc($ds2)) {
		
		$sql_5 = "Select discount From `modx_site_ec_orders` Where id = '".$order['order_id']."' ";
		$rt5= mysql_query($sql_5);
		$row5 = mysql_fetch_assoc($rt5);
		$discount = (100-$row5['discount'])/100;
		$price = $item['price']*$discount;
		$price = str_replace(",",".", $price);
		
			$line_items = $item['item_id'].'#';
			$line_items.= $item['acc_id'].'#';
			$line_items.= $item['pagetitle'].'#';
			
			$line_items.= $price.'#';  
			
			$line_items.= $item['quantity'].'#';;
		    $line_items.= $item['color_z'].'#';;
			$line_items.= $item['size_z'];
			
			
			$line_items.= "\r\n";
			$line_items_.= $line_items;
		}
		$order_line = $line."{\r\n".$line_items_."}\r\n";
		fwrite($export, $order_line);        
    }   
    fwrite($export, "@@@\r\n");
	fclose($export);	
	header('Content-type: application/txt');
	// It will be called downloaded.pdf
	if($order_id = $_REQUEST['order_id']){
		header('Content-Disposition: attachment; filename="order'.$order_id.'.txt"');
	}
	else{	
		header('Content-Disposition: attachment; filename="ZAK.txt"');
	}
	// The PDF source is in original.pdf
	readfile($file);
	unlink($file);			
	exit; 
}

//echo number_format('1234.56',2,'.','');	
$number_of_results = $perpage;		
$ds = mysql_query($sql);//die($sql);
$result_size = mysql_num_rows($ds);
include_once $base_path."manager/includes/controls/datagrid.class.php";
$grd = new DataGrid('',$ds,$number_of_results); // set page size to 0 t show all items
$grd->noRecordMsg = $_lang["no_records_found"];
$grd->cssClass="grid";
$grd->showRecordInfo=true;
$grd->columnHeaderClass="gridHeader";
$grd->itemClass="gridItem";
$grd->altItemClass="gridAltItem";
$grd->fields="num,fname,customer_id,customer_region,status_name,payment_m,delivery_t,discount,amount,paid,status";

$grd->columns = $_lang["order_id"].",";	

$grd->columns.= "<div class=\"".($sort_field == 'so.customer_fname' ? 'actsortfield' : 'sortfield')."\"><a  href=\"index.php?a=5500&sort=so.customer_fname\">".$_lang["ec_order_cust_name"] ."</a></div>,";	

$grd->columns.= "<div class=\"".($sort_field == 'order_date' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=order_date\">".$_lang["ec_order_date"] ."</a></div>,";

$grd->columns.= "<div class=\"".($sort_field == 'bonus' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=bonus\">".$_lang["user_bonus"] ."</a></div>,";

$grd->columns.= "<div class=\"".($sort_field == 'discount' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=discount\">".$_lang["ec_order_discount"] ."</a></div>,";

$grd->columns.= "<div class=\"".($sort_field == 'quantity' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=quantity\">".$_lang["ec_order_quantity"] ."</a></div>,";

//$grd->columns.= "<div class=\"".($sort_field == 'delivery_amount' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=delivery_amount\">".$_lang["ec_order_delivery_amount"] ."</a></div>,";

$grd->columns.= "<div class=\"".($sort_field == 'pt.name' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=pt.name\">".$_lang["ec_order_payment_type"] ."</a></div>,";

$grd->columns.= "<div class=\"".($sort_field == 'amount' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=amount\">".$_lang["ec_order_amount"] ."</a></div>,";

$grd->columns.= "<div class=\"".($sort_field == 'so.status' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=so.status\">".$_lang['ec_order_status'] ."</a></div>,";

$grd->columns.= "<div class=\"".($sort_field == 'so.confirmed' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=so.confirmed\">".$_lang['ec_order_confirmed'] ."</a></div>,";

$grd->columns.= $_lang["ec_order_paid_status"].",";

$grd->columns.= '';		

$grd->colWidths="150,200,80,20,20,40,100,50,50,60,60,50,85";
$grd->colAligns="left,left,left,left,left,left,left,left,left,left,left,left,left";
$grd->colTypes ="template:[+order_id+]";	
$grd->colTypes.='||template:<a href="index.php?a=88&id=[+customer_id+]">[+customer_fname+] [+customer_sname+] [+customer_lname+] [+customer_company+]</a>';
$grd->colTypes.="||php:echo datetime(\$row['order_date']);";
$grd->colTypes.="||template:[+bonus+]%";
$grd->colTypes.="||template:[+discount+]%";
$grd->colTypes.="||php:echo quantity(\$row['quantity']);";
//$grd->colTypes.="||php:global \$ar; echo money(\$row['delivery_amount']).'<br>'.\$ar[\$row['delivery_type']];";
$grd->colTypes.="||template:[+payment_m+]";
$grd->colTypes.="||php:echo money(\$row['amount']);";
$grd->colTypes.="||template:[+status_name+]";
$grd->colTypes.="||template:[+confirmed_name+]";
$grd->colTypes.="||template:[+paid_status+]";
$grd->colTypes.='||template:<a href="index.php?a=5501&id=[+order_id+]" title="'.$_lang['ec_order_details'].'">'.$_lang['ec_order_details'].'</a>';
	
	
if($listmode=='1') $grd->pageSize=0;
if($_REQUEST['op']=='reset') $grd->pageNumber = 1;

?>
<script type="text/javascript" src="media/script/datefunctions.js"></script>
<script type="text/javascript">
  	function ec_delete_order(id) {
	    if(confirm("<?php echo $_lang['confirm_ec_delete_user']; ?>")==true) {
			window.location.href='index.php?a=5500&id='+id;		
	    }
	}	
	
	function listByStatusDate() {		
		document.filter.submit();	
	}
	function changePerPage(to) {
		window.location.href='index.php?a=5500&perpage='+to;		
	}
</script>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<br/>
 <?php
 function getTotalOrdersCount() {
 	global $modx;
 	$sql = " SELECT count(id) as cnt FROM " . $modx->getFullTableName("site_ec_orders"); 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
 }
  
 function getOrdersCountByStatus($status) {
 	global $modx; 	
 	$status = mysql_escape_string($status);
 	$sql = "SELECT count(id) as cnt FROM " . $modx->getFullTableName("site_ec_orders").  "WHERE status = $status"; 	
 	//echo $sql."<br>"; 
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
  }
  	
 ?>
<div class="sectionHeader"><?php echo $_lang["ec_order_hdr"];?></div>
<div class="sectionBody">
	<!-- load modules -->
		<div class="tab-pane" id="FilterPane" style="border:0">
			<script type="text/javascript">
		    	tpSettings = new WebFXTabPane( document.getElementById( "FilterPane" ) );
		    </script>
		    <div class="tab-page" id="tabMain">
	        	<h2 class="tab"><?php echo $_lang["ec_order_hdr"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabMain" ) );</script>	
	            <form action="index.php?a=5500&search=1" method="post" name="searchform">
				<table  width="100%"  border="0" class="actionButtons">
						<tr>
						    <td  nowrap><?php echo $_lang['ec_search_criteria_order_id']; ?>:</td>
						    <td width="10">&nbsp;</td>
						    <td width="120">
						    <input name="ec_order_id" type="text" value="<?php echo @$_SESSION['ec_or_search']['ec_order_id']?>"></td>
							<td id="Button1" nowrap>							
							<a href="#" onclick="document.searchform.submitok.click();">
							<img src="media/style/<?php echo $theme?>images/icons/save.gif" align="absmiddle"> 
							<?php echo $_lang['search']; ?>
							</a>							
							</td>
						<td width="100%">
						</td>	
						<td>
							<a target="_blank" href="index.php?a=5500&importorders=1">
							<img src="media/style/<?php echo $theme?>images/icons/save.gif" align="absmiddle"> 
							<?php echo $_lang['ec_order_1c_export']; ?>
							</a>
						</td>
					  </tr>			 		 
				</table>								
				<input type="submit" value="Search" name="submitok" style="display:none">
				</form>
				<div class="stay"></div>	
				<p><strong>
				<?php if (isset($_SESSION['ec_or_search'])) echo $_lang['ec_search_found']." ".$result_size." ".$_lang['ec_search_found_records'];?>
				</strong></p>
	        	
				<?php if (!empty($_SESSION['ec_user_id'])) { 
					echo '<p><strong>';
					$curr_user = $modx->getWebUserInfo(intval($_SESSION['ec_user_id']));
					echo $_lang['user_orders'].":</strong> ".$curr_user['fname']." ".$curr_user['sname']." ".$curr_user['lname'];		
					echo '';
					echo '<table cellpadding="0" cellspacing="0" border="0" class="actionButtons"><tr><td id="Button1" align="right">';
					echo '<a href="index.php?a=5500&kill_user_id=1"><img src="media/style/'.($manager_theme ? "$manager_theme/":"").'images/icons/save.gif" align="absmiddle"> '.$_lang['show_all_orders'].'</a>';		
					echo '</td></tr></table></p><div class="stay"></div>';				
				}
				?>	
				
	        	
	        	<form action="index.php?a=5500" method="POST" name="filter">	
					 <table border="0" width="100%" >
						 <tr>	
						 	
						      <td  align="left">
						      
						       
						      
									        <strong><?php echo $_lang["ec_order_status"];?></strong>&nbsp;
									        <?php
									        $order_status_arr = array();
											$order_curr_status = isset($order_curr_status) ? $order_curr_status : 'all';
											$sql = 'SELECT * FROM ' . $modx->getFullTableName("ec_order_status") . ' order by listindex';
											$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
											$lines[] = '<select id="order_status" name="status">';	
											$lines[] = '<option value="all">'.$_lang['ec_order_status_all'].'</option>';				
											if ($rs && mysql_num_rows($rs)>0) {
												while ($row = mysql_fetch_assoc($rs)) {	
													$order_status_arr[] = $row;
													if ($order_curr_status == $row['id']) $lines[] = '<option value="'.$row['id'].'"  selected>'.$row['name'].'</option>';
													else $lines[] = '<option value="'.$row['id'].'">'.$row['name'].'</option>';					
												}	
											}				
											$lines[] = '</select>';		
											echo implode("\n", $lines);
											?>
									 	 </td> 	
									 	 	
									 	 <td  align="left">						 
									 	   <strong><?php echo $_lang["ec_order_confirmed"];?></strong>&nbsp;      
									       <select id="confirmed" name="confirmed">	
											<option value="all" <?php if ($order_curr_confirmed == 'all')  echo "selected";?>><?php echo $_lang['all']?></option>
											<option value="1" <?php if ($order_curr_confirmed == '1') echo "selected";?>><?php echo $_lang['yes']?></option>	
											<option value="0" <?php if ($order_curr_confirmed == '0') echo "selected";?>><?php echo $_lang['no']?></option>	
											</select>
											
									 	 </td>	
						 	 			 	
						      			<td  align="left">
						      
						       <strong><?php echo $_lang["ec_order_paid"];?></strong>&nbsp;				       
						       <select id="order_paid" name="paid">	
								<option value="all" <?php if ($order_curr_paid == 'all')  echo "selected";?>><?php echo $_lang['all']?></option>
								<option value="1" <?php if ($order_curr_paid == '1') echo "selected";?>><?php echo $_lang['yes']?></option>	
								<option value="0" <?php if ($order_curr_paid == '0') echo "selected";?>><?php echo $_lang['no']?></option>	
								</select>								
						 	 </td>	
						 	
												
						 	
						 	 		
						 	 <td nowrap>
						 	    <strong><?php echo $_lang["ec_order_date_from"];?>&nbsp;</strong>
						 	    <input id="from_date" name="from_date" type="text" size="20" value="<?php echo $fromdate; ?>" readonly>
						 	 </td>
						 	
						 	 <td>
						 	 <a onclick="cal1.popup();" onmouseover="window.status='Select a date'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal.gif" width="16" height="16" border="0" /></a>
						 	 </td>
						 	  <td>
						 	 <a onclick="document.filter.from_date.value='';" href='#'><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="No date" /></a>
						 	 </td>
						 	  <td nowrap>
						 	    <strong><?php echo $_lang["ec_order_date_to"];?>&nbsp;</strong>
						 	    <input id="to_date" name="to_date" type="text" size="20" value="<?php echo $todate;?>" readonly>
						 	 </td>
						 	
						 	 <td>
						 	 <a onclick="cal2.popup();" onmouseover="window.status='Select a date'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal.gif" width="16" height="16" border="0" /></a>
						 	 </td>
						 	  <td>
						 	  <a onclick="document.filter.to_date.value='';" href='#'><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="No date" /></a>
						 	 </td>
						 	 <td  align="left" id="button1" >	
							 	 <table border="0" width="100%" class="actionButtons">
									 <tr>	
									 	<td  align="left" nowrap id="button1">
							 	 	<a href="#" onclick="listByStatusDate();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/newdoc.gif" /> <?php echo $_lang["ec_order_filter_show"];?></a>
							 	 		</td>
							 	 	</tr>
							 	 </table>	
						 	 </td>
						 	 
						</tr> 
						<tr>	  	
							 <td colspan="10"  align="right" width="100%">
								 <?php echo $_lang["ec_item_per_page"];?>
									 <select name="per_page" onchange="changePerPage(this.options[this.selectedIndex].value)">
									 	<option value="10" <?php if ($perpage == 10) echo "selected";?>>10</option>				 	
									 	<option value="20" <?php if ($perpage == 20) echo "selected";?>>20</option>
									 	<option value="30" <?php if ($perpage == 30) echo "selected";?>>30</option>
									 	<option value="50" <?php if ($perpage == 50) echo "selected";?>>50</option>
									 	<option value="100" <?php if ($perpage == 100) echo "selected";?>>100</option>
									 	<option value="150" <?php if ($perpage == 150) echo "selected";?>>150</option>					  
									 	<!--<option value="0" <?php if ($perpage == 0) echo "selected";?>><?php echo $_lang['ec_item_per_page_all']?></option>-->
									 </select>						 
						     </td>
						      </tr>
					 
					 </table>	     
					</form>
					<?php				  
					echo $grd->render();
					?>
	        	
	        	        	
	        </div>			   
	        
	        <div class="tab-page" id="tabInfo">
	        	<h2 class="tab"><?php echo $_lang["ec_item_info"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabInfo" ) );</script>     	
	        	<table class="grid" align="center" border="0" cellpadding="0" cellspacing="0">
					<tbody>
					<tr class="gridHeader" align="center">
						<td colspan="<?php echo (1+count($order_status_arr))?>" valign="middle">
							<b><?php echo $_lang["ec_order_stat"]?> </b>
						</td></tr>
											
					</tr>
					<tr>
						<td align="center" class="gridItem" ><?php echo $_lang["ec_order_all_cnt"];?></td>
						<?php 
							foreach ($order_status_arr as $k => $v) {								
								echo '<td align="center" class="gridItem" >'.$v['name'].'</td>'; 	
							} 						
						?>											
					</tr>
					
					<tr>
					<td align="center" class="gridAltItem"><?php echo quantity(getTotalOrdersCount());?></td>
						<?php 
							foreach ($order_status_arr as $k => $v) {								
								echo '<td align="center" class="gridAltItem" >'. quantity(getOrdersCountByStatus($v['id'])).'</td>'; 	
							} 						
						?>	
					</tr>
					</tbody>
				</table>     	  	
		 </div>		
		 <div class="tab-page" id="tabOrderStatus">
	        	<h2 class="tab"><?php echo $_lang["ec_order_status_hdr"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabOrderStatus" ) );</script>       	
	        	<?php
					$sql  =  "SELECT * FROM ".$modx->getFullTableName("ec_order_status")." ORDER BY listindex";
					$manager_theme = $manager_theme ? $manager_theme : '';
					$number_of_results = 0;		
					$ds = mysql_query($sql);
					$result_size = mysql_num_rows($ds);
					include_once $base_path."manager/includes/controls/datagrid.class.php";
					$grd = new DataGrid('',$ds,$number_of_results); // set page size to 0 t show all items
					$grd->noRecordMsg = $_lang["no_records_found"];
					$grd->cssClass="grid";
					$grd->showRecordInfo=true;
					$grd->columnHeaderClass="gridHeader";
					$grd->itemClass="gridItem";
					$grd->altItemClass="gridAltItem";
					$grd->fields="num,name,listindex";
					$grd->columns = $_lang["id"].",";
					$grd->columns .= $_lang["ec_ordsts_name"].",";
					$grd->columns .= $_lang["ec_ordsts_listindex"].",";	
					$grd->columns .= $_lang["ec_ordsts_remove"];	
					$grd->colWidths="15,150,70,50";
					$grd->colAligns="left,left,left,left";
					$grd->colTypes ="template:[+id+]";					
					$grd->colTypes.="||template:
					<input name=\"name[[+id+]]\" maxlength=\"128\"  value=\"[+name+]\" class=\"inputBox\" style=\"width: 300px;\" type=\"text\">";			
					$grd->colTypes.="||template:
					<input name=\"listindex[[+id+]]\" maxlength=\"4\" id=\"index_[+id+]\" value=\"[+listindex+]\" class=\"inputBox\" style=\"width: 30px;\" type=\"text\">
					<input class=\"button\" value=\"&lt;\" onclick=\"var elm = document.getElementById('index_[+id+]');var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();\" type=\"button\">
					<input class=\"button\" value=\"&gt;\" onclick=\"var elm = document.getElementById('index_[+id+]');var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();\" type=\"button\">
					";						
					$grd->colTypes.="||template:<input type=\"checkbox\" name=\"remove[[+id+]]\" id=\"check_[+num+]\" value=\"[+id+]\">";		
					if($listmode=='1') $grd->pageSize=0;
					if($_REQUEST['op']=='reset') $grd->pageNumber = 1;					
					?>
					<script type="text/javascript">		
						function add_ordsts() {
							document.ordsts_add.submit();			
						}	
						function checkall(obj) {
							id_ = 'check_';
							num = 1;
							while (document.getElementById(id_+num)) {					
								document.getElementById(id_+num).checked = obj.checked;
								num++;	
							}		
						}	
						function save_ordsts() {
							if(confirm("<?php echo $_lang['confirm_ec_ordsts_save']; ?>")==true) {			
								document.ordsts_save.submit();
							}
						}
					</script>	
					<strong><?php echo $_lang["ec_ordsts_add_hdr"];?>&nbsp;</strong>
					<form action="index.php?a=5510" method="POST" name="ordsts_add">
					 <table border="0"  class="actionButtons">
							<tr>							 	    	
							 	 <td nowrap>
							 	    <strong><?php echo $_lang["ec_ordsts_name"];?>&nbsp;</strong>
							 	    <input name="name" type="text" size="20">
							 	 </td>
							 	  <td nowrap>
							 	    <strong><?php echo $_lang["ec_ordsts_listindex"];?>&nbsp;</strong>
							 	    <input name="listindex" type="text" size="10">
							 	 </td>
							 	 
							 	 <td  align="left" id="button1" >	
							 	 	<a href="#" onclick="add_ordsts();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/newdoc.gif" /> <?php echo $_lang["ec_ordsts_add"];?></a></td>
					           	 </td>					         
							</tr>
					</table> 
					</form> 
					<br>         	 
					<form action="index.php?a=5511" method="POST" name="ordsts_save">	 
						<?php				  
						echo $grd->render();
						?>
					</form>
					
					<table border="0" class="actionButtons">
						<tr>	
					  		<td  align="left" id="button1" >	
							 	 	<a href="#" onclick="save_ordsts();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/newdoc.gif" /> <?php echo $_lang["ec_ordsts_save"];?></a></td>
					        </td>        
					</tr>
					</table>        	
	        	
	        	
	     </div>	
	
	
      
      
      
	 
	
	</div>
	
	
	
</div>
</form>
<script type="text/javascript">
    var cal1 = new calendar1(document.forms['filter'].elements['from_date'], document.getElementById("from_date"));
    cal1.path="<?php echo str_replace("index.php", "media/", $_SERVER["PHP_SELF"]); ?>";
    cal1.year_scroll = true;
    cal1.time_comp = true;


    var cal2 = new calendar1(document.forms['filter'].elements['to_date'], document.getElementById("to_date"));
    cal2.path="<?php echo str_replace("index.php", "media/", $_SERVER["PHP_SELF"]); ?>";
    cal2.year_scroll = true;
    cal2.time_comp = true;
</script>


