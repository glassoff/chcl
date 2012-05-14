<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('edit_web_user')) {
	$e->setError(3);
	$e->dumpError();
}


$user = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$theme = $manager_theme ? "$manager_theme/":"";
// initialize page view state - the $_PAGE object
$modx->manager->initPageViewState();






if (isset($_REQUEST['group_id'])) {
	$group_id = intval($_REQUEST['group_id']);
	$_SESSION['ec_group_id'] = $group_id;
	unset($_SESSION['ec_ul_search']);
} elseif(isset($_SESSION['ec_group_id'])) {
	$group_id = $_SESSION['ec_group_id'];
} else {		
	$sql = 'SELECT * FROM ' . $modx->getFullTableName("webgroup_names") . ' order by name LIMIT 1';
	$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
	if ($rs && mysql_num_rows($rs) == 1) {
		 $r = mysql_fetch_assoc($rs);
		 $group_id = $r['id'];
	}	
}




if (isset($_REQUEST['sort'])) {			
	$_SESSION['ec_ul_sort'] = mysql_escape_string($_REQUEST['sort']);
	if (isset($_SESSION['ec_ul_sortdir'])) {			
		if ($_SESSION['ec_ul_sortdir'] == 'DESC') $_SESSION['ec_ul_sortdir'] = 'ASC'; 	
		else {
			$_SESSION['ec_ul_sortdir'] = 'DESC';
		}
	} else $_SESSION['ec_ul_sortdir'] = 'DESC';		
} 

$sortdir = $_SESSION['ec_ul_sortdir'];

if (isset($_SESSION['ec_ul_sort'])) {
	$sort_field = $_SESSION['ec_ul_sort'];
	$sort_sql = "$sort_field  $sortdir";		
} else {
	$sort_sql = "wu.id";	
}

if (isset($_REQUEST['perpage'])) {			
	$_SESSION['ec_ul_perpage'] = mysql_escape_string($_REQUEST['perpage']);
} 

if (isset($_SESSION['ec_ul_perpage'])) {
	$perpage = $_SESSION['ec_ul_perpage'];						
} else {
	$perpage = 10;	
}


if (isset($_REQUEST['search']) && intval($_REQUEST['search']) == 1 && 
	isset($_POST['ec_user_id']) && 
	isset($_POST['ec_user_fname'])&& 
	isset($_POST['ec_user_sname'])&& 
	isset($_POST['ec_user_lname'])&& 
	isset($_POST['ec_user_email'])
	
	) {
	$_SESSION['ec_ul_search']['ec_user_id'] = intval($_POST['ec_user_id']);
	$_SESSION['ec_ul_search']['ec_user_fname'] = $_POST['ec_user_fname'];
	$_SESSION['ec_ul_search']['ec_user_sname'] = $_POST['ec_user_sname'];
	$_SESSION['ec_ul_search']['ec_user_lname'] = $_POST['ec_user_lname'];
	$_SESSION['ec_ul_search']['ec_user_email'] = $_POST['ec_user_email'];
	$group_id = 0;
	
}


// get & save listmode
$listmode = isset($_REQUEST['listmode']) ? $_REQUEST['listmode']:$_PAGE['vs']['lm'];
$_PAGE['vs']['lm'] = $listmode;

$sql = " SELECT wu.*,wua.*, IF(wua.blocked = 1,'".$_lang['yes']."','".$_lang['no']."') as 'blocked', " .
			" ser.name as region_name,wu.id as user_id  "." ".
			" FROM ".$modx->getFullTableName("web_users")." wu ".
			" INNER JOIN ".$modx->getFullTableName("web_user_attributes")." wua ON wua.internalKey=wu.id ".
			" LEFT JOIN ".$modx->getFullTableName("site_ec_regions")." ser ON ser.id=wua.region ".
			" INNER JOIN ".$modx->getFullTableName("web_groups")." wg ON wg.webuser=wu.id ".			
			" WHERE wg.webgroup = $group_id ";
			
$sql_search = " SELECT wu.*,wua.*, IF(wua.blocked,'".$_lang['yes']."','".$_lang['no']."') as 'blocked', " .
			" ser.name as region_name "." ".
			" FROM ".$modx->getFullTableName("web_users")." wu ".
			" INNER JOIN ".$modx->getFullTableName("web_user_attributes")." wua ON wua.internalKey=wu.id ".
			" LEFT JOIN ".$modx->getFullTableName("site_ec_regions")." ser ON ser.id=wua.region ".						
			" WHERE ";			
			
if (isset($_SESSION['ec_ul_search'])) {
	$search_user_id =   $_SESSION['ec_ul_search']['ec_user_id'];	
	$search_user_fname = $_SESSION['ec_ul_search']['ec_user_fname'];	
	$search_user_sname = $_SESSION['ec_ul_search']['ec_user_sname'];
	$search_user_lname = $_SESSION['ec_ul_search']['ec_user_lname'];
	$search_user_email = $_SESSION['ec_ul_search']['ec_user_email'];
	$sqladd = " wua.internalKey>0  " ;
	$sqladd .= $search_user_id!="" ? " and wua.internalKey=$search_user_id " : " " ;
	$sqladd .= $search_user_fname!="" ? " and wua.fname LIKE '%$search_user_fname%' " : " " ;
	$sqladd .= $search_user_sname!="" ? "and   wua.sname LIKE '%$search_user_sname%' " : "  " ;
	$sqladd .= $search_user_lname!="" ? "and wua.lname LIKE '%$search_user_lname%' " : " " ;
	$sqladd .= $search_user_email!="" ? "and  wua.email LIKE '%$search_user_email%' " : " " ;
	
	$sql = $sql_search." ".$sqladd." ";
} 	
$sql .= " ORDER BY $sort_sql";
$manager_theme = $manager_theme ? $manager_theme : '';
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
$grd->fields="";
$grd->columns.= "<div class=\"".($sort_field == 'wu.id' ? 'actsortfield' : 'sortfield')."\"><a  href=\"index.php?a=99&sort=wu.id\">".$_lang["ec_cust_id"] ."</a></div>,";	
$grd->columns.= "<div class=\"".($sort_field == 'wua.fname' ? 'actsortfield' : 'sortfield')."\"><a  href=\"index.php?a=99&sort=wua.fname\">".$_lang["ec_cust_fullname"] ."</a></div>,";	
$grd->columns.= $_lang["ec_cust_email"].",";
$grd->columns.= "<div class=\"".($sort_field == 'wua.bonus' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=99&sort=wua.bonus\">".$_lang["user_bonus"] ."</a></div>,";


//if (isset($_POST['ec_user_id']) && $_POST['ec_user_id']!='' ){
//$id_user1= $_POST['ec_user_id'];
//} else if (isset($_POST['ec_user_id']) && $_POST['ec_user_id']=='') {$id_user1='[+internalKey+]';}

//else 


$id_user1='[+internalKey+]';



$grd->columns.= $_lang["user_orders"].",";
$grd->columns.= $_lang["ec_cust_blocked"].",";
$grd->columns.= $_lang["ec_item_actions"];		
$grd->colWidths="20,,70,80,80,120,40";
$grd->colAligns="center,left,left,left,left,left,left";
$grd->colTypes ='template:'.$id_user1.'';	

$grd->colTypes.='||template:<a href="index.php?a=88&id='.$id_user1.'">[+fname+] [+sname+] [+lname+] [+type+] [+company+]</a>';
$grd->colTypes.="||template:[+email+]";
$grd->colTypes.="||template:[+bonus+]%";
$grd->colTypes.='||template:<a href="index.php?a=5500&user_id='.$id_user1.'">'.$_lang[user_orders].'</a>';
$grd->colTypes.="||template:[+blocked+]";
	
$grd->colTypes.=($modx->hasPermission('edit_web_user') ? '||template:<a href="#" title="'.$_lang['ec_edit_cust'].'" onclick="ec_edit_cust('.$id_user1.')"><img src="media/style/'.$manager_theme.'/images/icons/save.gif"></a>' : '');
$grd->colTypes.=($modx->hasPermission('delete_web_user') ? '<a href="#" title="'.$_lang['ec_delete_cust'].'" onclick="ec_delete_cust('.$id_user1.')"><img src="media/style/'.$manager_theme.'/images/icons/delete.gif"></a>' : '');
	
	
if($listmode=='1') $grd->pageSize=0;
if($_REQUEST['op']=='reset') $grd->pageNumber = 1;

?>
<script type="text/javascript">
  	function searchResource(){
		document.resource.op.value="srch";
		document.resource.submit();
	};

	function resetSearch(){
		document.resource.search.value = ''
		document.resource.op.value="reset";
		document.resource.submit();
	};

    function ec_edit_cust(id) {
		window.location.href='index.php?a=88&id='+id;		
	}
	
	 function ec_add_cust() {
		window.location.href='index.php?a=87';		
	}

	function ec_delete_cust(id) {
	    if(confirm("<?php echo $_lang['confirm_ec_delete_user']; ?>")==true) {
			window.location.href='index.php?a=90&id='+id;		
	    }
	}	
	
	function listByGroup(id) {
		if (id == '') return;		
		window.location.href='index.php?a=99&group_id='+id;	
	}
	function changePerPage(to) {
		window.location.href='index.php?a=99&perpage='+to;		
	}		
	
</script>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<br/>
 <?php
 function getItemsCount($pid,$field,$cmd) {
 	global $modx;
 	if ($pid != false) $pid_state = 'pid = '.$pid;  else $pid_state = '';
 	$wheresql = !empty($field) ? ($field. " = ". $cmd) : "";
 	if (!empty($pid_state) && !empty($wheresql)) $state = " WHERE ". $pid_state." AND ".$wheresql;
 	elseif (!empty($pid_state) || !empty($wheresql)) $state = " WHERE ". $pid_state." ".$wheresql;
 	else $state = "";
 	$sql = "SELECT count(id) as cnt FROM " . $modx->getFullTableName("site_ec_items"). $state; 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
 }
 ?>
<div class="sectionHeader"><?php echo $_lang["ec_cust_list"];?></div>
<div class="sectionBody">
	<!-- load modules -->
		<div class="tab-pane" id="FilterPane" style="border:0">
			<script type="text/javascript">
		    	tpSettings = new WebFXTabPane( document.getElementById( "FilterPane" ) );
		    </script>
		    <div class="tab-page" id="tabMain">
	        	<h2 class="tab"><?php echo $_lang["ec_cust_list"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabMain" ) );</script>	        	
	        </div>			   
		<div class="tab-page" id="tabSearch">
	        <h2 class="tab"><?php echo $_lang["ec_search"] ?></h2>
	        <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabSearch" ) );</script>	
		    <?php echo $_lang['ec_search_criteria']; ?>
			<p><form action="index.php?a=99&search=1" method="post" name="searchform"></p>
			<table width="100%" border="0">
			  <tr>
			    <td width="120"><?php echo $_lang['ec_search_criteria_user_id']; ?></td>
			    <td width="20">&nbsp;</td>
			    <td width="120"><input name="ec_user_id" type="text" value="<?php echo !@empty($_SESSION['ec_ul_search']['ec_user_id']) ? $_SESSION['ec_ul_search']['ec_user_id'] : "";?>"></td>
				<td><?php echo $_lang['ec_ul_ec_search_msg']; ?></td>
			  </tr>
			   <tr>
			    <td wfnameth="120"><?php echo $_lang['ec_search_criteria_user_fname']; ?></td>
			    <td wfnameth="20">&nbsp;</td>
			    <td wfnameth="120"><input name="ec_user_fname" type="text" value="<?php echo !@empty($_SESSION['ec_ul_search']['ec_user_fname']) ? $_SESSION['ec_ul_search']['ec_user_fname'] : "";?>"></td>
				<td><?php echo $_lang['ec_ul_search_msg']; ?></td>
			  </tr>  
			   <tr>
			    <td wsnameth="120"><?php echo $_lang['ec_search_criteria_user_sname']; ?></td>
			    <td wsnameth="20">&nbsp;</td>
			    <td wsnameth="120"><input name="ec_user_sname" type="text" value="<?php echo !@empty($_SESSION['ec_ul_search']['ec_user_sname']) ? $_SESSION['ec_ul_search']['ec_user_sname'] : "";?>"></td>
				<td><?php echo $_lang['ec_ul_search_msg']; ?></td>
			  </tr>  
			   <tr>
			    <td wlnameth="120"><?php echo $_lang['ec_search_criteria_user_lname']; ?></td>
			    <td wlnameth="20">&nbsp;</td>
			    <td wlnameth="120"><input name="ec_user_lname" type="text" value="<?php echo !@empty($_SESSION['ec_ul_search']['ec_user_lname']) ? $_SESSION['ec_ul_search']['ec_user_lname'] : "";?>"></td>
				<td><?php echo $_lang['ec_ul_search_msg']; ?></td>
			  </tr>  
			   <tr>
			    <td wlnameth="120"><?php echo $_lang['ec_search_criteria_user_email']; ?></td>
			    <td wlnameth="20">&nbsp;</td>
			    <td wlnameth="120"><input name="ec_user_email" type="text" value="<?php echo !@empty($_SESSION['ec_ul_search']['ec_user_email']) ? $_SESSION['ec_ul_search']['ec_user_email'] : "";?>"></td>
				<td><?php echo $_lang['ec_ul_search_msg']; ?></td>
			  </tr>  
			  <tr>
			  	<td colspan="4">
					<table cellpadding="0" cellspacing="0" border="0" class="actionButtons">
					    <td id="Button1" align="right"><a href="#" onclick="document.searchform.submitok.click();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" align="absmiddle"> <?php echo $_lang['search']; ?></a></td>
					    <td id="Button2" align="right"><a href="index.php?a=99"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" align="absmiddle"> <?php echo $_lang['cancel']; ?></a></td>
					</table>
				</td>
			  </tr>
			</table>
			
			<input type="submit" value="Search" name="submitok" style="display:none">
			</form>
			<div class="stay"></div>	
			<p><strong><?php if (isset($_SESSION['ec_ul_search'])) echo $_lang['ec_search_found']." ".$result_size." ".$_lang['ec_search_found_records'];?></strong></p>
		</div>
	
		
		
		
	
	
	
	 <table border="0" width="100%" class="actionButtons">
		 <tr>	
		 	  <td  align="left" nowrap>
		 	    <strong><?php echo $_lang["ec_user_group"];?>:&nbsp;</strong>		 	 	
		 	  </td>	
		      <td  align="left">
		        <?php
				$sql = 'SELECT * FROM ' . $modx->getFullTableName("webgroup_names") . ' order by name';
				$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
				$lines = array();
				$lines[] = '<select name="region" onchange="listByGroup(this.options[this.selectedIndex].value)">';	
				$lines[] = '<option value="">-</option>';
				if ($rs && mysql_num_rows($rs)>0) {
					while ($row = mysql_fetch_assoc($rs)) {			
						if ($group_id == $row['id']) $lines[] = '<option value="'.$row['id'].'"  selected>'.$row['name'].'</option>';
						else $lines[] = '<option value="'.$row['id'].'"  >'.$row['name'].'</option>';
					}		
				}
				$lines[] = '</select>';		
				echo implode("\n", $lines);
				?>
		 	 </td> 				
		 	 <td  align="left" id="button1" >
		 	 	<?php if ($modx->hasPermission('new_web_user')) { ?>
		 	 	<a href="#" onclick="ec_add_cust();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/newdoc.gif" /> <?php echo $_lang["ec_new_user"];?></a></td>
            	<?php }?>
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
					  
					 	<!--<option value="0" <?php if ($perpage == 0) echo "selected";?>><?php echo $_lang['ec_item_per_page_all']?></option>-->
					 </select>						 
		     </td>
		 </tr>		 
	 </table>	     
	 <form action="index.php" method="POST" name="group_actions">
	 <input type="hidden" name="a">
	<?php				  
	echo $grd->render();
	?>
	</form>
	</div>
</div>
</form>
