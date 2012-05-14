<?php
$sql  =  "SELECT * FROM ".$modx->getFullTableName("ec_orders")." ORDER BY listindex";
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
$grd->columns = $_lang["listnum"].",";
$grd->columns = $_lang["ec_ordsts_name"].",";
$grd->columns = $_lang["ec_ordsts_listindex"].",";	
$grd->columns.= $_lang["ec_ordsts_remove"];	
$grd->colWidths="20,150,70,50";
$grd->colAligns="center,left,left,left";
$grd->colTypes ="template:[+num+]";	

$grd->colTypes.="||template:
<input name=\"listindex[[+id+]]\" maxlength=\"4\" id=\"index_[+id+]\" value=\"[+name+]\" class=\"inputBox\" style=\"width: 300px;\" type=\"text\">
<input class=\"button\" value=\"&lt;\" onclick=\"var elm = document.getElementById('index_[+id+]');var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();\" type=\"button\">
<input class=\"button\" value=\"&gt;\" onclick=\"var elm = document.getElementById('index_[+id+]');var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();\" type=\"button\">
";  

$grd->colTypes.="||template:
<input name=\"name[[+id+]]\" maxlength=\"4\" id=\"index_[+id+]\" value=\"[+listindex+]\" class=\"inputBox\" style=\"width: 30px;\" type=\"text\">";  
	
$grd->colTypes.="||template:<input type=\"checkbox\" nam=\"remove[[+id+]]\" id=\"check_[+num+]\" value=\"[+id+]\">";
	
if($listmode=='1') $grd->pageSize=0;
if($_REQUEST['op']=='reset') $grd->pageNumber = 1;

?>
<script type="text/javascript">		
	function add_ordsts()() {
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
<form action="index.php?a=5510" method="POST" name="ordsts_add">
 <table border="0" width="100%" class="actionButtons">
		 <tr>	
		 	  <td  align="left" nowrap>
		 	    <strong><?php echo $_lang["ec_ordsts_add_hdr"];?>&nbsp;</strong>		 	 	
		 	  </td>		    	
		 	 <td nowrap>
		 	    <strong><?php echo $_lang["ec_ordsts_name"];?>&nbsp;</strong>
		 	    <input name="name" type="text" size="100">
		 	 </td>
		 	  <td nowrap>
		 	    <strong><?php echo $_lang["ec_ordsts_listindex"];?>&nbsp;</strong>
		 	    <input name="listindex" type="text" size="20">
		 	 </td>
		 	 
		 	 <td  align="left" id="button1" >	
		 	 	<a href="#" onclick="listByStatusDate();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/newdoc.gif" /> <?php echo $_lang["ec_ordsts_add"];?></a></td>
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

<table border="0" width="100%" class="actionButtons">
	<tr>	
  		<td  align="left" id="button1" >	
		 	 	<a href="#" onclick="listByStatusDate();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/newdoc.gif" /> <?php echo $_lang["ec_ordsts_save"];?></a></td>
        </td>        
</tr>
</table> 