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

$sql  =  "SELECT * FROM ".$modx->getFullTableName("site_ec_discounts") ."ORDER BY id";
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
$grd->fields="num,name,description,active";
$grd->columns = $_lang["id"].",";	
$grd->columns.= $_lang["ec_discount_name"].",";	
$grd->columns.= $_lang["ec_discount_desc"].",";	
$grd->columns.= $_lang["ec_discount_active"].",";		
$grd->columns.= $_lang["ec_pm_actions"];
$grd->colWidths="20,150,,100,60";
$grd->colAligns="left,left,left,left,left";
$grd->colTypes ="template:[+id+]";	
$grd->colTypes.="||template:<a href='#' onclick='postEdit([+id+])'>[+name+]</a>";
$grd->colTypes.="||";
$grd->colTypes.='||php:if ($row["active"] == 1) echo "'.$_lang['yes'].'";   else   echo "'.$_lang['no'].'";';
$grd->colTypes.='||template:<a href="#" title="'.$_lang['ec_pm_edit'].'" onclick="postEdit([+id+])">
<img src="media/style/'.$manager_theme.'/images/icons/save.gif"></a>&nbsp;<a href="#" title="'.$_lang['ec_pm_delete'].'" onclick="postDelete([+id+])">
<img src="media/style/'.$manager_theme.'/images/icons/delete.gif"></a>';	
if($listmode=='1') $grd->pageSize=0;
if($_REQUEST['op']=='reset') $grd->pageNumber = 1;

?>
<script type="text/javascript">
  
	function postAdd() {
		window.location.href='index.php?a=5401';		
	}
	
	
	function postEdit(id) {
		window.location.href='index.php?a=5402&id='+id;		
	}
	
	function postDelete(id) {
		if(confirm("<?php echo $_lang['confirm_discount_delete']; ?>")==true) {
			window.location.href='index.php?a=5404&id='+id;		
		}
	}	
	
</script>
<br/>
<div class="sectionHeader"><?php echo $_lang["ec_discount_hdr"]; ?></div>
<div class="sectionBody">
	<!-- load modules -->
	<br>
	<table cellpadding="0" cellspacing="0" class="actionButtons">
       <tr> 
		<td id="Button1">
        <a href="#" onclick="postAdd();"><img src="media/style/<?php echo $theme?>images/icons/save.gif" align="absmiddle"> 
        <?php echo $_lang["ec_discount_add"]; ?></a>
        </td>
       </tr>         
    </table>	
	<br>	      
	<form action="index.php" method="POST" name="discount_form">
	<input type="hidden" name="a">
	<?php				  
	echo $grd->render();
	?>
	</form>	
</div>

