<?php
if(IN_MANAGER_MODE!="true") 
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if($modx->hasPermission('ec_overview')) {
	$e->setError(3);
	$e->dumpError();
}
$theme = $manager_theme ? "$manager_theme/":"";
// get & save listmode
?>
<script type="text/javascript">
	function postAction() {
		var value = document.getElementById("action_cmd").options[document.getElementById("action_cmd").selectedIndex].value;
		if (value != '0') {
			if(confirm("<?php echo $_lang['confirm_ec_item_group_action']; ?>")==true) {				
				document.group_actions.a.value = value;
				document.group_actions.submit();
			}	
		}
	}
		
	function selectBrand(id){
		window.location.href='index.php?a=5000&ec_brand='+id;	
	}
	
	function postfilter() {
		document.filterform.submit();		
	}
</script>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<br/>
 <?php
 function getItemsStat($field,$cmd) {
 	global $modx;
 	$wheresql = !empty($field) ? (" WHERE ".$field. " = ". $cmd) : ""; 	
 	$sql = "SELECT count(id) as cnt FROM " . $modx->getFullTableName("site_ec_items"). $wheresql; 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
 }
 
 function getCategoryStat() {
 	global $modx; 	
 	$sql = "SELECT count(parent) as cnt FROM " . $modx->getFullTableName("site_items");	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
 }
 
 
function getCustomerStat($group = '') {
 	global $modx;
 	if (!empty($group)) $group = ' WHERE webgroup = '.$group;  else $group = '';
 	$sql = "SELECT count(webgroup) as cnt FROM " . $modx->getFullTableName("web_groups"). $group; 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
 }
 

 
function getOrderStat($status = '') {
 	global $modx;
 	if (!empty($status)) $status = ' WHERE status = '.$status;  else $status = '';
 	$sql = "SELECT count(id) as cnt, sum(amount) as totalamount  FROM " . $modx->getFullTableName("site_ec_orders"). $status; 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row;
	} else return 0; 	
}


  
 function getOrderStatus() {
 	global $modx;
 	$order_curr_status = isset($order_curr_status) ? $order_curr_status : $order_def_status;
	$sql = 'SELECT * FROM ' . $modx->getFullTableName("ec_order_status") . ' order by listindex';
	$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());	
	$order_status = array();			
	if ($rs && mysql_num_rows($rs)>0) {
		while ($row = mysql_fetch_assoc($rs)) {	
			$order_status[] = $row;
		}			
	}
	return $order_status; 	
 } 
 
 function getCustomersStat($webgroup = '') {
 	global $modx;
 	if (!empty($webgroup )) $webgroup  = ' WHERE webgroup = '.$webgroup ;  else $webgroup  = '';
 	$sql = "SELECT count(id) as cnt  FROM " . $modx->getFullTableName("web_groups"). $webgroup; 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
}


  
 function getWebGroups() {
 	global $modx;
 	$sql = 'SELECT * FROM ' . $modx->getFullTableName("webgroup_names") . ' order by id';
	$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());	
	$web_groups = array();			
	if ($rs && mysql_num_rows($rs)>0) {
		while ($row = mysql_fetch_assoc($rs)) {	
			$web_groups[] = $row;
		}			
	}
	return $web_groups; 	
 } 
 
?>
<div class="sectionHeader"><?php echo $_lang["ec_main_header"]?></div>
<div class="sectionBody">
	<!-- load modules -->
		   	<table class="grid" align="center" border="0" cellpadding="0" cellspacing="0">
				<tbody>
				<tr class="gridHeader" align="center">
					<td colspan="8" valign="middle">
						<b><?php echo $_lang["ec_main_items_stat_title"]?>:</strong><br>
					</td></tr>
				</tr>
				<tr>
					<td align="center" class="gridItem" ><?php echo $_lang["ec_item_all"];?></td>
					<td align="center" class="gridItem" ><?php echo $_lang["ec_items_qnt_published"];?></td>
					<td align="center" class="gridItem" ><?php echo $_lang["ec_items_qnt_sell"];?></td>
					<td align="center" class="gridItem" ><?php echo $_lang["ec_items_qnt_new"];?></td>
					<td align="center" class="gridItem" ><?php echo $_lang["ec_items_qnt_popular"];?></td>
					<td align="center" class="gridItem"><?php echo $_lang["ec_items_qnt_recommended"];?></td>
					<td align="center" class="gridItem"><?php echo $_lang["ec_items_qnt_byorder"];?></td>
					<td align="center" class="gridItem"><?php echo $_lang["ec_items_qnt_deleted"];?></td>
				</tr>
					
				<tr>
					<td align="center" class="gridAltItem"><?php echo quantity(getItemsStat("",""));?> <?php echo $_lang["qnt"];?></td>
					<td align="center" class="gridAltItem"><?php echo quantity(getItemsStat("published","1"));?> <?php echo $_lang["qnt"];?></td>
					<td align="center" class="gridAltItem"><?php echo quantity(getItemsStat("sell","1"));?> <?php echo $_lang["qnt"];?></td>
					<td align="center" class="gridAltItem"><?php echo quantity(getItemsStat("new","1"));?> <?php echo $_lang["qnt"];?></td>
					<td align="center" class="gridAltItem"><?php echo quantity(getItemsStat("popular","1"));?> <?php echo $_lang["qnt"];?></td>
					<td align="center" class="gridAltItem"><?php echo quantity(getItemsStat("recommended","1"));?> <?php echo $_lang["qnt"];?></td>			<td align="center" class="gridAltItem"><?php echo quantity(getItemsStat("byorder","1"));?> <?php echo $_lang["qnt"];?></td>
					<td align="center" class="gridAltItem"><?php echo quantity(getItemsStat("deleted","1"));?> <?php echo $_lang["qnt"];?></td>
					</tr>
					<tr>
					<tr>
						<td align="left" valign="top" colspan="8" class="gridItem" >
						<strong><?php echo $_lang["ec_main_items_last"]; ?>:</strong><br>
						<?php
							$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_items") . ' order by createdon LIMIT 10';
							$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());	
							$i = 0;
							$count = mysql_num_rows($rs); 
							if ($rs && mysql_num_rows($rs)>0) {
								while ($row = mysql_fetch_assoc($rs)) {	
									$i++;
									if ($i != $count) echo  "<a href=\"?a=5004&id=$row[id]\">$row[pagetitle]</a>&nbsp;,&nbsp;";
									else echo  "<a href=\"?a=5004&id=$row[id]\">$row[pagetitle]</a>";
								}			
							}
						?>
						</td>						
					</tr>
					</tr>
				</tbody>
			</table>
		
			<br>
		   	<table class="grid" align="center" border="0" cellpadding="0" cellspacing="0">
					<tbody>
					<?php
					$web_groups_arr = getWebGroups();
					?>
					<tr class="gridHeader" >
						<td align="center" colspan="<?php echo (1+count($web_groups_arr))?>" valign="middle">
							<b><?php echo $_lang["ec_main_customers_stat_title"]?> </b>
						</td></tr>											
					</tr>
					<tr>
						<td align="center" valign="top" class="gridItem" ><?php echo $_lang["ec_all_cust_count"];?></td>
						<?php							
							foreach ($web_groups_arr as $k => $v) {								
								echo '<td align="center" class="gridItem" >'.$v['name'].'</td>'; 	
							} 						
						?>											
					</tr>
					
					<tr>
					<td align="center" class="gridAltItem">
					<?php 
					$cust_all_cnt = getCustomersStat();
					echo quantity($cust_all_cnt);
					?>
					</td>
						<?php 
							foreach ($web_groups_arr as $k => $v) {			
								$cust_cnt = getCustomersStat($v['id']);					
								echo '<td align="center" class="gridAltItem" >'. quantity($cust_cnt).'</td>'; 	
							} 						
						?>	
					</tr>
					<tr>
					<td align="left" valign="top" colspan="<?php echo (1+count($order_status_arr))?>" class="gridItem" >
					<strong><?php echo $_lang["ec_main_customers_last"]; ?>:</strong><br>
					<?php
						$sql = 'SELECT * FROM ' . $modx->getFullTableName("web_user_attributes") . ' order by reg_date LIMIT 10';
						$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());	
						$i = 0;
						$count = mysql_num_rows($rs); 
						if ($rs && mysql_num_rows($rs)>0) {
							while ($row = mysql_fetch_assoc($rs)) {	
								$i++;
								if ($i != $count) 
								echo "<a href=\"?a=88&id=$row[id]\">$row[fname] $row[sname] $row[lname]</a>&nbsp;,&nbsp;";
								else echo "<a href=\"?a=88&id=$row[id]\">$row[fname] $row[sname] $row[lname]</a>";
								
							}			
						}
					?>
					</td>						
					</tr>
					</tbody>
				</table>
		   <br>
			<table class="grid" align="center" border="0" cellpadding="0" cellspacing="0">
					<tbody>
					<?php
						$order_status_arr = getOrderStatus();
					?>
					<tr class="gridHeader" >
						<td align="center" colspan="<?php echo (1+count($order_status_arr))?>" valign="middle">
							<b><?php echo $_lang["ec_main_orders_stat_title"]?> </b>
						</td></tr>											
					</tr>
					<tr>
						<td align="center" valign="top" class="gridItem" ><?php echo $_lang["ec_order_all_cnt"];?></td>
						<?php							
							foreach ($order_status_arr as $k => $v) {								
								echo '<td align="center" class="gridItem" >'.$v['name'].'</td>'; 	
							} 						
						?>											
					</tr>					
					<tr>
					<td align="center" class="gridAltItem">
					<?php 
					$order = getOrderStat();
					echo quantity($order['cnt']);
					?>
					</td>
						<?php 
							foreach ($order_status_arr as $k => $v) {			
								$order = getOrderStat($v['id']);					
								echo '<td align="center" class="gridAltItem" >'. quantity($order['cnt']).'</td>'; 	
							} 						
						?>	
					</tr>
					<tr>
					<td align="left" valign="top" colspan="<?php echo (1+count($order_status_arr))?>" class="gridItem" >
					<strong><?php echo $_lang["ec_main_orders_last"]; ?>:</strong><br>
					<?php
						$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_orders") . ' order by order_date LIMIT 10';
						$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());	
						$i = 0;
						$count = mysql_num_rows($rs); 
						if ($rs && mysql_num_rows($rs)>0) {
							while ($row = mysql_fetch_assoc($rs)) {	
								$i++;
								if ($i != $count) 
								echo "<a href=\"?a=5501&id=$row[id]\">$_lang[ec_order_num] $row[id](".money($row['amount']).")</a>&nbsp;,&nbsp;";
								else echo "<a href=\"?a=5501&id=$row[id]\">$_lang[ec_order_num] $row[id](".money($row['amount']).")</a>";			
							}			
						}
					?>
					</td>						
					</tr>
					</tbody>
				</table>
		
	</div>
</form>
