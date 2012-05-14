<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('view_eventlog')) {
	$e->setError(3);
	$e->dumpError();
}
$theme = $manager_theme ? "$manager_theme/":"";
// initialize page view state - the $_PAGE object
$modx->manager->initPageViewState();


// get & save listmode
$listmode = isset($_REQUEST['listmode']) ? $_REQUEST['listmode']:$_PAGE['vs']['lm'];
$_PAGE['vs']['lm'] = $listmode;

$sql  =  "SELECT * FROM ".$modx->getFullTableName("site_ec_payment_methods") ."ORDER BY listindex";
$manager_theme = $manager_theme ? $manager_theme : '';
$number_of_results = 0;		
$ds = mysql_query($sql);
$result_size = mysql_num_rows($ds);
include_once $base_path."manager/includes/controls/datagrid.class.php";
$grd = new DataGrid('',$ds,$number_of_results); // set page size to 0 t show all items
$grd->noRecordMsg = $_lang["no_records_found"];
$grd->cssClass="grid";
$grd->showRecordInfo=false;
$grd->columnHeaderClass="gridHeader";
$grd->itemClass="gridItem";
$grd->showRecordInfo=true;
$grd->altItemClass="gridAltItem";
$grd->fields="num,name,description,listindex,active";
$grd->columns = $_lang["id"].",";	
$grd->columns.= $_lang["ec_pm_name"].",";	
$grd->columns.= $_lang["ec_pm_desc"].",";	
$grd->columns.= $_lang["ec_pm_page"].",";
$grd->columns.= $_lang["ec_pm_listindex"].",";
$grd->columns.= $_lang["ec_pm_active"].",";		
$grd->columns.= $_lang["ec_pm_actions"];
$grd->colWidths="20,150,,100,30";
$grd->colAligns="center,left,left,left,left,left,left";
$grd->colTypes ="template:[+id+]";	
$grd->colTypes.="||template:<a href='#' onclick='postEdit([+id+])'>[+name+]</a>";
$grd->colTypes.="||template:[+description+]";
$grd->colTypes.="||template:[+payment_page+]";
$grd->colTypes.="||template:[+listindex+]";
$grd->colTypes.='||php:if ($row["active"] == 1) echo "'.$_lang['yes'].'";   else   echo "'.$_lang['no'].'";';
$grd->colTypes.='||template:<a href="#" title="'.$_lang['ec_pm_edit'].'" onclick="postEdit([+id+])">
<img src="media/style/'.$manager_theme.'/images/icons/save.gif"></a>&nbsp;<a href="#" title="'.$_lang['ec_pm_delete'].'" onclick="postDelete([+id+])">
<img src="media/style/'.$manager_theme.'/images/icons/delete.gif"></a>';
	
if($listmode=='1') $grd->pageSize=0;
if($_REQUEST['op']=='reset') $grd->pageNumber = 1;

?>
<script type="text/javascript">
  

	function postEdit(){
		document.pm_form.a.value = 4002; 
		document.pm_form.submit();
	};

	function changeListMode(){
		if(confirm("<?php echo $_lang['confirm_pm_delete_item']; ?>")==true) {
			document.pm_form.a.value = 4003; 
			document.pm_form.submit();		
	    }
	};

	function postAdd() {
		window.location.href='index.php?a=5203';		
	}
	
	function showAll() {
		window.location.href='index.php?a=5000&all=1';		
	}
	
	function postEdit(id) {
		window.location.href='index.php?a=5204&id='+id;		
	}
	
	function postDelete(id) {
		window.location.href='index.php?a=5206&id='+id;		
	}
	
	function checkall(obj) {
		id_ = 'check_';
		num = 1;
		while (document.getElementById(id_+num)) {					
			document.getElementById(id_+num).checked = obj.checked;
			num++;	
		}
		
	}
	
	
	
	
	
</script>
<br/>
 <?php
 function getItemsCount($pid,$field,$cmd) {
 	global $modx;
 	if ($pid) $pid = 'pid = '.$pid;  else $pid = '';
 	$sql = "SELECT count(id) as cnt FROM " . $modx->getFullTableName("site_ec_items"). " WHERE $pid_state  $field = $cmd ORDER BY listindex";
 	//echo $sql;
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
 }
 ?>
<div class="sectionHeader"><?php echo $_lang["ec_pm_hdr"]; ?></div>
<div class="sectionBody">
	<!-- load modules -->
	<br>
	<table cellpadding="0" cellspacing="0" class="actionButtons">
        <td id="Button1"><a href="#" onclick="postAdd();"><img src="media/style/<?php echo $theme?>images/icons/save.gif" align="absmiddle"> <?php echo $_lang["ec_pm_add"]; ?></a></td>     
        
    </table>
		 
	       			
	
	<br>
	      
	 <form action="index.php" method="POST" name="pm_form">
	 <input type="hidden" name="a">
	<?php				  
	echo $grd->render();
	?>
	</form>	
</div>

