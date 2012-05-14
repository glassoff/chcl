<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

?>
<script type="text/javascript">

	

var brandRows = []; // stores brand information in 2D array. 2nd array = 0-name,1-brand,2-value,3-http_equiv
			
				
			
function addBrand() {
	var f=document.brand;
		if(!f) return;
		if(!f.name.value)  alert("<?php echo $_lang["require_brand"];?>");		
		else {			
		f.op.value=(f.cmdsavebrand.value=="<?php echo $_lang["save"];?>") ? 'edtbrand':'addbrand';
		f.submit();
		}
}
			
				function editBrand(id){
					var f=document.brand;										
					if(!f) return;
					f.name.value = brandRows[id][0];					
					f.listindex.value= brandRows[id][1];
					if (brandRows[id][2] == 1) 
					f.isactive.checked = true;
					else {f.isactive.checked = false;}
					f.id.value=id;						
					f.cmdsavebrand.value='<?php echo $_lang["save"];?>';
					f.cmdcancelbrand.style.visibility = 'visible';					
					f.name.focus();
				}
			
				function cancelBrand(id){
					var f=document.brand;
					if(!f) return;
					f.reset();
					f.id.value='';
					f.cmdsavebrand.value='<?php echo $_lang["ec_add_brand"];?>';
					f.cmdcancelbrand.style.visibility = 'hidden';
				}
			
				function deleteBrands() {
					var f=document.brand;
					if(!f) return;
					else if(confirm("<?php echo $_lang['confirm_delete_brands']; ?>")) {
						f.op.value='delbrands';
						f.submit();
					}
				}
				
				

</script>
<br />
<div class="sectionHeader"><?php echo $_lang["ec_brands"]; ?></div>
<div class="sectionBody">

      			<form name="brand" method="post" action="index.php" onsubmit="return checkForm();">
				<input type="hidden" name="a" value="3001" />
				<input type="hidden" name="op" value="" />
				<input type="hidden" name="id" value="" />
				<!-- META tags -->
				<strong><?php echo $_lang['ec_edit_brand_hdr']?></strong><br />
				<br />
				<div class="searchbara">
				 <table width="700" border="0" cellspacing="0" cellpadding="0">
	              <tr style="height: 24px;">
	                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_brand_name']; ?></span></td>
	                <td><input name="name" type="text" maxlength="255" value="" class="inputBox" style="width:300px;"  spellcheck="true" />
	                </td>
	              </tr>
	              
	              <tr style="height: 24px;">
	                <td align="left" style="width:100px;"><span class='warning'><?php echo $_lang['ec_listindex']; ?></span></td>
	                <td>
	                <table border="0" cellspacing="0" cellpadding="0" style="width:325px;"><tr>
	                <td><input name="listindex" type="text" maxlength="3" value="" class="inputBox" style="width:30px;"  />
	                <input type="button" class="button" value="&lt;" onclick="var elm = document.brand.listindex;var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();" /><input type="button" class="button" value="&gt;" onclick="var elm = document.brand.listindex;var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();" /></td></tr>	                
	                </table>
	                </td>
	              </tr>
	              
	            <tr style="height: 24px;">
	                <td><span class='warning'><?php echo $_lang['ec_brand_active']; ?></span></td>
	                <td>
	                  
	                  <input name="isactive" type="checkbox" class="checkbox" value="1" checked/>	              
	                </td>
                  </tr>
                  <tr>
                  <td nowrap="nowrap" colspan="2"><br>
						<input type="button" value="<?php echo $_lang["ec_add_brand"];?>" name="cmdsavebrand" onclick="addBrand()" /> 
						<input style="visibility:hidden" type="button" value="<?php echo $_lang["cancel"];?>" name="cmdcancelbrand" onclick="cancelBrand()" />
						</td>
					  </tr>
				</table>
				</div>
				
				<br>
				<?php
			
					$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_brands")." ORDER BY listindex,name";
					$ds = mysql_query($sql);					
					include_once $base_path."manager/includes/controls/datagrid.class.php";
					$grd = new DataGrid('',$ds,$number_of_results = 0); // set page size to 0 t show all items
					$grd->noRecordMsg = $_lang["no_records_found"];
					$grd->cssClass="grid";
					$grd->columnHeaderClass="gridHeader";
					$grd->useJSFilter=true;
					$grd->itemClass="gridItem";
					$grd->altItemClass="gridAltItem";
					$grd->fields="";
					$grd->columns=$_lang["listnum"]." ,".$_lang["ec_brand_name"]." ,".$_lang["ec_brand_listindex"]." ,".$_lang["ec_brand_active"]. ",".$_lang["delete"];
					$grd->colWidths="40,,100,100,150";
					$grd->colAligns="center,left,left,left,left";
					$grd->colTypes ="template:[+num+]||".
					$grd->colTypes.="template:<a href='#' title='".$_lang["click_to_edit_title"]."' onclick='editBrand([+id+])'>[+name+]</a><span style='display:none;'><script type=\"text/javascript\">brandRows['[+id+]']=[\"[+js.name+]\",\"[+listindex+]\",\"[+isactive+]\"];</script>||";						$grd->colTypes.="template:[+listindex+]||";
					$grd->colTypes.='php:if ($row["isactive"] == 1) echo "'.$_lang['yes'].'";   else   echo "'.$_lang['no'].'";||';
					$grd->colTypes.="template:<input name='brand[]' type='checkbox' value='[+id+]'/></a>";				
					echo $grd->render();
				?>
				
				<table border=0 cellpadding=2 cellspacing=0>
					<tr><td colspan="5">&nbsp;</td></tr>
					<tr>
						<td align="right">
							<input type="button" name="cmddeltag" value="<?php echo $_lang["ec_delete_brands"];?>" onclick="deleteBrands();" />
						</td>
					</tr>
				</table>
				</form>
					
	        </div>
			
			
			
	        
	    </div>
