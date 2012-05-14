<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

?>
<script type="text/javascript">

	

var packRows = []; // stores pack information in 2D array. 2nd array = 0-name,1-pack,2-value,3-http_equiv
			
				
			
function addpack() {
	var f=document.pack;
	if(!f) return;
	if(!f.name.value)  alert("<?php echo $_lang["require_pack"];?>");		
	else {			
	f.op.value=(f.cmdsavepack.value=="<?php echo $_lang["save"];?>") ? 'edtpack':'addpack';
	f.submit();
	}
}
			
				function editpack(id){
					var f=document.pack;										
					if(!f) return;
					f.name.value = packRows[id][0];					
					f.weight.value= packRows[id][1];
					f.id.value=id;						
					f.cmdsavepack.value='<?php echo $_lang["save"];?>';
					f.cmdcancelpack.style.visibility = 'visible';					
					f.name.focus();
				}
			
				function cancelpack(id){
					var f=document.pack;
					if(!f) return;
					f.reset();
					f.id.value='';
					f.cmdsavepack.value='<?php echo $_lang["ec_add_pack"];?>';
					f.cmdcancelpack.style.visibility = 'hidden';
				}
			
				function deletepacks() {
					var f=document.pack;
					if(!f) return;
					else if(confirm("<?php echo $_lang['confirm_delete_packs']; ?>")) {
						f.op.value='delpacks';
						f.submit();
					}
				}
				
				

</script>
<br />
<div class="sectionHeader"><?php echo $_lang["ec_packs"]; ?></div>
<div class="sectionBody">

      			<form name="pack" method="post" action="index.php" onsubmit="return checkForm();">
				<input type="hidden" name="a" value="3003" />
				<input type="hidden" name="op" value="" />
				<input type="hidden" name="id" value="" />
				<!-- META tags -->
				<strong><?php echo $_lang['ec_edit_pack_hdr']?></strong><br />
				<br />
				<div class="searchbara">
				 <table width="700" border="0" cellspacing="0" cellpadding="0">
	              <tr style="height: 24px;">
	                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_pack_name']; ?></span></td>
	                <td><input name="name" type="text" maxlength="255" value="" class="inputBox" style="width:300px;"  spellcheck="true" />
	                </td>
	              </tr>
	              <tr style="height: 24px;">
	                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_pack_weight']; ?></span></td>
	                <td><input name="weight" type="text" maxlength="10" value="" class="inputBox" style="width:100px;"  spellcheck="true" />
	                </td>
	              </tr>                      
	            
                  <tr>
                  <td nowrap="nowrap" colspan="2"><br>
						<input type="button" value="<?php echo $_lang["ec_add_pack"];?>" name="cmdsavepack" onclick="addpack()" /> 
						<input style="visibility:hidden" type="button" value="<?php echo $_lang["cancel"];?>" name="cmdcancelpack" onclick="cancelpack()" />
						</td>
					  </tr>
				</table>
				</div>
				
				<br>
				<?php
			
					$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_packs")." ORDER BY name";
					$ds = mysql_query($sql);					
					include_once $base_path."manager/includes/controls/datagrid.class.php";
					$grd = new DataGrid('',$ds,$number_of_results = 0); // set page size to 0 t show all items
					$grd->noRecordMsg = $_lang["no_records_found"];
					$grd->cssClass="grid";
					$grd->columnHeaderClass="gridHeader";
					$grd->itemClass="gridItem";
					$grd->altItemClass="gridAltItem";
					$grd->fields="";
					$grd->columns=$_lang["listnum"]." ,".$_lang["ec_pack_name"]." ,".$_lang["ec_pack_weight"].",".$_lang["delete"];
					$grd->colWidths="40,,100,150";
					$grd->useJSFilter=true;
					$grd->colAligns="left,left,left,left";
					$grd->colTypes ="template:[+num+]||".
					$grd->colTypes.="template:<a href='#' title='".$_lang["click_to_edit_title"]."' onclick='editpack([+id+])'>[+name+]</a><span style='display:none;'><script type=\"text/javascript\">packRows['[+id+]']=[\"[+js.name+]\",\"[+weight+]\"];</script>||";	
					$grd->colTypes.="template:[+weight+]".$_lang['kg']."||";				
					$grd->colTypes.="template:<input name='pack[]' type='checkbox' value='[+id+]'/></a>";				
					echo $grd->render();
				?>
				
				<table border=0 cellpadding=2 cellspacing=0>
					<tr><td colspan="5">&nbsp;</td></tr>
					<tr>
						<td align="right">
							<input type="button" name="cmddeltag" value="<?php echo $_lang["ec_delete_packs"];?>" onclick="deletepacks();" />
						</td>
					</tr>
				</table>
				</form>
					
	        </div>
			
			
			
	        
	    </div>
