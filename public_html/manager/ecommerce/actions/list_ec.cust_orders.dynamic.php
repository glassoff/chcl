<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_manage_orders')) return;
$customer_id = $user;	
$status_arr = explode(',',$order_status);
$theme = $manager_theme ? "$manager_theme/":"";
// initialize page view state - the $_PAGE object
$modx->manager->initPageViewState();

if (isset($_POST['status']) &&
    isset($_POST['paid']) &&
    isset($_POST['from_date']) &&
    isset($_POST['to_date']) ) {    	    		
	$_SESSION['c.ec_order_filter']['status'] = mysql_escape_string($_POST['status']);
	$_SESSION['c.ec_order_filter']['paid'] = mysql_escape_string($_POST['paid']);
	$_SESSION['c.ec_order_filter']['fromdate'] = mysql_escape_string($_POST['from_date']);
	$_SESSION['c.ec_order_filter']['todate'] = mysql_escape_string($_POST['to_date']);	
	if (!empty($_SESSION['c.ec_order_filter']['fromdate'])) {
		list ($d, $m, $Y, $H, $M, $S) = sscanf($_SESSION['c.ec_order_filter']['fromdate'], "%2d-%2d-%4d %2d:%2d:%2d");
		$_SESSION['c.ec_order_filter']['fromdate'] = mktime($H, $M, $S, $m, $d, $Y);		
	} 
	if (!empty($_SESSION['c.ec_order_filter']['todate'])) {
		list ($d, $m, $Y, $H, $M, $S) = sscanf($_SESSION['c.ec_order_filter']['todate'], "%2d-%2d-%4d %2d:%2d:%2d");
		$_SESSION['c.ec_order_filter']['todate'] = mktime($H, $M, $S, $m, $d, $Y);
	}	
	unset($_SESSION['c.ec_or_search']);
} 
$filter_sql = '';
$fromdate = '';
$todate  ='';
$fromdate_ = '';
$todate_  ='';
if (isset($_SESSION['c.ec_order_filter'])) {    		
	$order_curr_status= $_SESSION['c.ec_order_filter']['status'];
	$order_curr_paid= $_SESSION['c.ec_order_filter']['paid'];
	$fromdate = !empty($_SESSION['c.ec_order_filter']['fromdate']) ? date("m-d-Y H:i:s",$_SESSION['c.ec_order_filter']['fromdate']) : "";
	$todate = !empty($_SESSION['c.ec_order_filter']['todate'])? date("m-d-Y H:i:s",$_SESSION['c.ec_order_filter']['todate']) : "";	
	$fromdate_ = $_SESSION['c.ec_order_filter']['fromdate'];
	$todate_ = $_SESSION['c.ec_order_filter']['todate'];
	if ($order_curr_status == 'all') $filter_sql = ''; 
	else $filter_sql = " AND status = ".$order_curr_status." ";	
	if ($order_curr_paid == 'all' || empty($order_curr_paid)) $filter_sql .= ''; 
	else $filter_sql .= " AND paid = ".$order_curr_paid." ";
	if (!empty($fromdate_) && !empty($todate_)) {
		$filter_sql .= " AND (order_date >= ".$fromdate_.") AND (order_date <= ".$todate_.")";
	} elseif (!empty($fromdate_)) {
		$filter_sql .= " AND (order_date >= ".$fromdate_.")";
	} elseif (!empty($todate_)) {
		$filter_sql .= " AND (order_date <= ".$todate_.")";
	} 
} 

 

if (isset($_REQUEST['sort'])) {			
	$_SESSION['c.ec_or_sort'] = mysql_escape_string($_REQUEST['sort']);
	if (isset($_SESSION['c.ec_or_sortdir'])) {			
		if ($_SESSION['c.ec_or_sortdir'] == 'DESC') $_SESSION['c.ec_or_sortdir'] = 'ASC'; 	
		else {
			$_SESSION['c.ec_or_sortdir'] = 'DESC';
		}
	} else $_SESSION['c.ec_or_sortdir'] = 'DESC';		
} 

$sortdir = $_SESSION['c.ec_or_sortdir'];

if (isset($_SESSION['c.ec_or_sort'])) {
	$sort_field = $_SESSION['c.ec_or_sort'];
	$sort_sql = "$sort_field  $sortdir";		
} else {
	$sort_sql = "order_date";	
}

if (isset($_REQUEST['perpage'])) {			
	$_SESSION['c.ec_or_perpage'] = mysql_escape_string($_REQUEST['perpage']);
} 

if (isset($_SESSION['c.ec_or_perpage'])) {
	$perpage = $_SESSION['c.ec_or_perpage'];						
} else {
	$perpage = 10;	
}




// get & save listmode
$listmode = isset($_REQUEST['listmode']) ? $_REQUEST['listmode']:$_PAGE['vs']['lm'];
$_PAGE['vs']['lm'] = $listmode;

   

$sql = " SELECT *, IF(paid = 1,'".$_lang['ec_order_paid']."','".$_lang['ec_order_notpaid']."') as 'paid_status'," .
       " os.name as status_name, pt.name as payment_m,so.id as order_id, so.id as order_id".
	   " FROM ".$modx->getFullTableName("site_ec_orders") . " so ".  
	   " INNER JOIN" .$modx->getFullTableName("ec_order_status"). " os ON  os.id = so.status ".
	   " INNER JOIN" .$modx->getFullTableName("site_ec_payment_methods"). " pt ON  so.payment_type = pt.id ". 
	   " WHERE so.customer_id=$user ";
		
if (isset($_SESSION['c.ec_or_search'])) {
	$search_order_id = $_SESSION['c.ec_or_search']['ec_order_id'];	
	$sqladd = $search_order_id!="" ? " AND so.id=$search_order_id " : "" ;	
	$sql .= $sqladd." ";
} 	
if (!empty($filter_sql) || $order_curr_status == 'all') {
	$sql .= $filter_sql." ORDER BY $sort_sql ";
} else {
	$sql .= " ".($order_def_status ? " AND so.status = ".$order_def_status : "")." ORDER BY $sort_sql ";
}		
//echo $sql;
//echo $sql;
//echo $sql;
$number_of_results = $perpage;		
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
$grd->fields="num,fname,customer_region,status_name,payment_m,discount,amount,paid,status";

$grd->columns = $_lang["listnum"].",";	
$grd->columns.= "<div class=\"".($sort_field == 'so.customer_fname' ? 'actsortfield' : 'sortfield')."\"><a  href=\"index.php?a=5500&sort=so.customer_fname\">".$_lang["ec_order_cust_fio"] ."</a></div>,";	
$grd->columns.= "<div class=\"".($sort_field == 'so.customer_region' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=so.customer_region\">".$_lang["ec_order_cust_shipping_address"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'so.status' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=so.status\">".$_lang['ec_order_status'] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'pt.name' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=pt.name\">".$_lang["ec_order_payment_type"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'discount' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=discount\">".$_lang["ec_order_discount"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'quantity' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=quantity\">".$_lang["ec_order_quantity"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'delivery_amount' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=delivery_amout\">".$_lang["ec_order_delivery_amount"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'amout' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=amount\">".$_lang["ec_order_amount"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'order_date' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5500&sort=order_date\">".$_lang["ec_order_date"] ."</a></div>,";
$grd->columns.= $_lang["ec_order_paid_status"].",";
$grd->columns.= '';		

$grd->colWidths="20,100%,70,80,20,40,60,50,50,60,50,85";
$grd->colAligns="center,left,left,left,left,left,left,left,left,left,left,left,left";

$grd->colTypes ="template:[+num+]";	
$grd->colTypes.="||template:[+customer_fname+] [+customer_sname+] [+customer_lname+]";
$grd->colTypes.="||template:[+customer_postcode+]-[+customer_region+],[+customer_state+]";
$grd->colTypes.="||";
$grd->colTypes.="||";
$grd->colTypes.="||";
$grd->colTypes.="||php:echo quantity(\$row['quantity']);";
$grd->colTypes.="||php:echo money(\$row['delivery_amount']);";
$grd->colTypes.="||php:echo money(\$row['amount']);";
$grd->colTypes.="||php:echo datetime(\$row['order_date']);";
$grd->colTypes.="||template:[+paid_status+]";
$grd->colTypes.='||template:<a href="index.php?a=5501&id=[+order_id+]" title="'.$_lang['ec_order_details'].'">'.$_lang['ec_order_details'].'</a>';
	
if($listmode=='1') $grd->pageSize=0;
if($_REQUEST['op']=='reset') $grd->pageNumber = 1;

?>
<script type="text/javascript" src="media/script/datefunctions.js"></script>
<script type="text/javascript">
  	function ec_delete_order(id) {
	    if(confirm("<?php echo $_lang['confirm_ec_delete_user']; ?>")==true) {
			window.location.href='index.php?a=88&cust_id=<?php echo $customer_id;?>&id='+id;		
	    }
	}	
	
	function listByStatusDate() {
		var status = document.getElementById('order_status').options[document.getElementById('order_status').selectedIndex].value;	
		var fromdate = document.getElementById('from_date').value;
		var todate = document.getElementById('to_date').value;
		window.location.href='index.php?a=88&cust_id=<?php echo $customer_id;?>&filter=1&status='+status+'&fromdate='+fromdate+'&todate='+todate;	
	}
</script>
<?php
 function getCustTotalOrdersCount($customer_id) {
 	global $modx;
 	$sql = " SELECT count(id) as cnt FROM " . $modx->getFullTableName("site_ec_orders") ."  WHERE customer_id = $customer_id"; 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
 }
  
 function getCustOrdersCountByStatus($status,$customer_id) {
 	global $modx; 	
 	$status = mysql_escape_string($status);
 	$sql = "SELECT count(id) as cnt FROM " . $modx->getFullTableName("site_ec_orders").  "WHERE customer_id = $customer_id AND status = $status "; 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
  }
 ?>

	<!-- load modules -->
		<div class="tab-pane" id="FilterPane" style="border:0">
			<script type="text/javascript">
		    	tpSettings = new WebFXTabPane( document.getElementById( "FilterPane" ) );
		    </script>
		    <div class="tab-page" id="tabMain">
	        	<h2 class="tab"><?php echo $_lang["ec_order_hdr"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabMain" ) );</script> 
	        	<form action="index.php?a=88&id=<?php echo $user;?>" method="POST" name="filter">	
					 <table border="0" width="100%" >
						 <tr>	
						 	  <td  align="left" nowrap>
						 	    <strong><?php echo $_lang["ec_order_show"];?>&nbsp;</strong>		 	 	
						 	  </td>	
						      <td  align="left">
						        <?php
						        $order_status_arr = array();
						        $lines = array();
								$order_curr_status = isset($order_curr_status) ? $order_curr_status : $order_def_status;
								$sql = 'SELECT * FROM ' . $modx->getFullTableName("ec_order_status") . ' order by listindex';
								$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
								$lines[] = '<select id="order_status">';	
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
						       <select id="order_paid" name="paid">	
								<option value="all" <?php if ($order_curr_paid == 'all')  echo "selected";?>><?php echo $_lang['ec_order_status_all']?></option>
								<option value="1" <?php if ($order_curr_paid == '1') echo "selected";?>><?php echo $_lang['ec_order_paid']?></option>	
								<option value="0" <?php if ($order_curr_paid == '0') echo "selected";?>><?php echo $_lang['ec_order_notpaid']?></option>	
								</select>
								
						 	 </td>	
						 	 		
						 	 <td nowrap>
						 	    <strong><?php echo $_lang["ec_order_date_from"];?>&nbsp;</strong>
						 	    <input id="from_date" name="from_date" type="text" size="20" value="<?php echo $fromdate;?>" readonly>
						 	 </td>
						 	
						 	 <td>
						 	 <a onclick="cal1.popup();" onmouseover="window.status='Select a date'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal.gif" width="16" height="16" border="0" /></a>
						 	 </td>
						 	  <td>
						 	 <a onclick="document.userform.from_date.value='';" href='#'><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="No date" /></a>
						 	 </td>
						 	  <td nowrap>
						 	    <strong><?php echo $_lang["ec_order_date_to"];?>&nbsp;</strong>
						 	    <input id="to_date" name="to_date" type="text" size="20" value="<?php echo $todate;?>" readonly>
						 	 </td>
						 	
						 	 <td>
						 	 <a onclick="cal2.popup();" onmouseover="window.status='Select a date'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal.gif" width="16" height="16" border="0" /></a>
						 	 </td>
						 	  <td>
						 	  <a onclick="document.userform.to_date.value='';" href='#'><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="No date" /></a>
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
							 <td  align="right" width="100%">
								 <?php echo $_lang["ec_item_per_page"];?>
									 <select name="per_page" onchange="changePerPage(this.options[this.selectedIndex].value)">
									 	<option value="5" <?php if ($perpage == 5) echo "selected";?>>5</option>	
									 	<option value="10" <?php if ($perpage == 10) echo "selected";?>>10</option> 	
									 	<option value="20" <?php if ($perpage == 20) echo "selected";?>>20</option>
									 	<option value="30" <?php if ($perpage == 30) echo "selected";?>>30</option>
									 	<option value="50" <?php if ($perpage == 50) echo "selected";?>>50</option>
									 	<option value="100" <?php if ($perpage == 100) echo "selected";?>>100</option>
									 	<option value="150" <?php if ($perpage == 150) echo "selected";?>>150</option>				  
									 	<!--<option value="0" 
								         <?php if ($perpage == 0) echo "selected";?>><?php echo $_lang['ec_item_per_page_all']?></option>-->
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
						<td colspan="<?php echo (1+count($status_arr))?>" valign="middle">
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
					<td align="center" class="gridAltItem"><?php echo quantity(getCustTotalOrdersCount($customer_id));?></td>
						<?php 
							foreach ($order_status_arr as $k => $v) {								
								echo '<td align="center" class="gridAltItem" >'. quantity(getCustOrdersCountByStatus($customer_id,$v['id'])).'</td>'; 	
							} 						
						?>	
					</tr>
					</tbody>
				</table>        	  	
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

