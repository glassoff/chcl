<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

function first_region() {
	global $modx,$_lang;	
	$sql = 'select * from ' . $modx->getFullTableName("site_ec_regions") . '  ORDER BY listindex LIMIT 1';
	$rs = mysql_query($sql) or die ('Error');
	$rows = mysql_fetch_assoc($rs);
	if (sizeof($rows) < 1) die("Could not query cityes table");
	if (isset($rows['id'])) return $rows; 
}
	
function region_list($id) {
	global $modx, $_lang, $theme;	
	$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_regions") . ' order by name';
	$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
	$lines = array();
	$lines[] = '<select name="rid" id="tregion" onchange="region_cities_list(this.options[this.selectedIndex].value)">';
	
	if ($rs && mysql_num_rows($rs)>0) {
		while ($row = mysql_fetch_assoc($rs)) {			
			if ($id == $row['id']) 	$lines[] = '<option value="'.$row['id'].'"  selected>'.$row['name'].'</option>';
			else $lines[] = '<option value="'.$row['id'].'"  >'.$row['name'].'</option>';
		}		
	}
	$lines[] = '</select>';

	echo implode("\n", $lines);
}

function rate_list($zone = 0) {
	global $modx, $_lang, $theme;	
	$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_shipping_rates") . ' order by zone';
	$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
	$lines = array();
	$lines[] = '<select name="rate_zone">';	
	if ($rs && mysql_num_rows($rs)>0) {
		while ($row = mysql_fetch_assoc($rs)) {			
			if ($zone == $row['zone']) 	$lines[] = '<option value="'.$row['zone'].'"  selected>'.$row['zone'].'</option>';
			else $lines[] = '<option value="'.$row['id'].'"  >'.$row['zone'].'</option>';
		}		
	}
	$lines[] = '</select>';
	echo implode("\n", $lines);
}

if (!isset ($_REQUEST['id'])) {
    $id = 0;
} else {
    $id = !empty ($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
}
$theme = $manager_theme ? "$manager_theme/":"";
$modx->manager->initPageViewState();
include_once $base_path."manager/includes/controls/datagrid.class.php";
$rateformat = "&rate0.5=äî 500ãð.;float;&rate1=äî 1êã;float;&rate2=äî 2êã;float;&rate3=äî 3êã;float;&rate4=äî 4êã;float;&rate5=äî 5êã;float;&rate6=äî 6êã;float;&rate7=äî 7êã;float;&rate8=äî 8êã;float;&rate9=äî 9êã;float;&rate10=äî 10êã;float";
?>
<script type="text/javascript">
// Current Params
var currentParams = {};

function removeRow(index) {
	var f= document.forms['zone'],o = '';
    if(!f) return;
    r = (f.rate.value) ? f.rate.value.split("#"):"";
    if(!r) return;     
    for(v = 0; v < r.length; v++) {
    	if (v != index && v != '') o += '#'+r[v];
    }
	f.rate.value = o;
	showParameters();
}
var time = new Date();
var col_id = time.getTime();

function addRow() {
	var f= document.forms['zone'];
    if(!f) return;
    r = (f.rate.value) ? f.rate.value.split("#"):"";     
    row_temp = '#&col'+(col_id++)+'=x;float;0&col'+(col_id++)+'=x;float;0&col'+(col_id++)+'=x;float;0&col'+(col_id++)+'=x;float;0';  
	f.rate.value += row_temp;
	showParameters();
}


function showParameters(ctrl) {
    var c,p,df,cp;
    var ar,desc,value,key,dt,r,d;
    currentParams = {}; // reset;
    if (ctrl) {
    	f = ctrl.form;
    } else {
        f= document.forms['zone'];
        if(!f) return;
    }
    // setup parameters
    tr = (document.getElementById) ? document.getElementById('displayparamrow'):document.all['displayparamrow'];
    r = (f.rate.value) ? f.rate.value.split("#"):"";
    if(!r) tr.style.display='none';
    else{
 		t='<table width="400" style="margin-bottom:3px;margin-left:0px;background-color:#EEEEEE" cellpadding="2" cellspacing="1"><thead><tr><td width="25%"><?php echo $_lang["ec_sh_min_value"]; ?></td><td width="25%"><?php echo $_lang["ec_sh_max_value"]; ?></td><td width="40%"><?php echo $_lang['ec_shipping_price'];?></td><td width="40%"><?php echo $_lang['ec_main_pack'];?></td><td width=""><?php echo $_lang['delete'];?></td></tr></thead>';
     
        for(v = 0; v < r.length; v++) { 
        dp = r[v].split("&");               
		for(p = 0; p < dp.length; p++) {       	
			dp[p]=(dp[p]+'').replace(/^\s|\s$/,""); // trim
            ar = dp[p].split("=");
            key = ar[0]     // param
            //alert(key+'-'+dt);
            //if (key) key = col_id++; else key = ar[0]; 
            ar = (ar[1]+'').split(";");
            desc = ar[0];   // description
            dt = ar[1];     // data type
            value = decode((ar[2])? ar[2]:'');
            // store values for later retrieval
            if (key && dt=='list') currentParams[key] = [v,desc,dt,value,ar[3]];
            else if (key) currentParams[key] = [v,desc,dt,value];
            if (dt) {
                switch(dt) {
                case 'int':
                    c = '<input type="text" name="prop_'+key+'" value="'+value+'" size="10" onchange="setParameter(\''+key+'\',\''+dt+'\',this)" />';
                    break;
                
                case 'menu':
                    value = ar[3];
                    c = '<select name="prop_'+key+'" style="width:168px" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">';
                    ls = (ar[2]+'').split(",");
                    if(currentParams[v][key]==ar[2]) currentParams[v][key] = ls[0]; // use first list item as default
                    for(i=0;i<ls.length;i++){
                        c += '<option value="'+ls[i]+'"'+((ls[i]==value)? ' selected="selected"':'')+'>'+ls[i]+'</option>';
                    }
                    c += '</select>';
                    break;
                case 'list':
                    value = ar[3];
                    ls = (ar[2]+'').split(",");
                    if(currentParams[v][key]==ar[2]) currentParams[v][key] = ls[0]; // use first list item as default
                    c = '<select name="prop_'+key+'" size="'+ls.length+'" style="width:168px" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">';
                    for(i=0;i<ls.length;i++){
                        c += '<option value="'+ls[i]+'"'+((ls[i]==value)? ' selected="selected"':'')+'>'+ls[i]+'</option>';
                    }
                    c += '</select>';
                    break;
                case 'list-multi':
                    value = (ar[3]+'').replace(/^\s|\s$/,"");
                    arrValue = value.split(",")
                    ls = (ar[2]+'').split(",");
                    if(currentParams[key]==ar[2]) currentParams[key] = ls[0]; // use first list item as default
                    c = '<select name="prop_'+key+'" size="'+ls.length+'" multiple="multiple" style="width:168px" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">';
                    for(i=0;i<ls.length;i++){
                        if(arrValue.length){
                            for(j=0;j<arrValue.length;j++){
                                if(ls[i]==arrValue[j]){
                                    c += '<option value="'+ls[i]+'" selected="selected">'+ls[i]+'</option>';
                                }else{
                                    c += '<option value="'+ls[i]+'">'+ls[i]+'</option>';
                                }
                            }
                        }else{
                            c += '<option value="'+ls[i]+'">'+ls[i]+'</option>';
                        }
                    }
                    c += '</select>';
                    break;
                case 'textarea':
                    c = '<textarea name="prop_'+key+'" cols="40" rows="4" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">'+value+'</textarea>';
                    break;
                default:  // string
                    c = '<input type="text" name="prop_'+key+'" value="'+value+'" size="10" onchange="setParameter(\''+key+'\',\''+dt+'\',this)" />';
                    break;
                } 
                d +='<td bgcolor="#FFFFFF" width="50%">'+c+'</td>';               
            }                       
        }
        if (d) t +='<tr>'+d+'<td bgcolor="#FFFFFF"><img onclick="removeRow('+v+')" src="media/style/<?php echo $manager_theme?>/images/icons/delete.gif"></td></tr>';
        d = '';
        }
        t+='</table>';
        td = (document.getElementById) ? document.getElementById('displayparams'):document.all['displayparams'];
        td.innerHTML = t;
        tr.style.display='';
    }
    implodeParameters();
}

function setParameter(key,dt,ctrl) {
    var v;
    if(!ctrl) return null;
    switch (dt) {
        case 'int':
            ctrl.value = parseInt(ctrl.value);
            if(isNaN(ctrl.value)) ctrl.value = 0;
            v = ctrl.value;
            break;
        case 'float':        	
            ctrl.value = parseFloat(ctrl.value);
            if(isNaN(ctrl.value)) ctrl.value = 0;
            v = ctrl.value;
            break;    
        case 'menu':
            v = ctrl.options[ctrl.selectedIndex].value;
            currentParams[key][4] = v;
            implodeParameters();
            return;
            break;
        case 'list':
            v = ctrl.options[ctrl.selectedIndex].value;
            currentParams[key][4] = v;
            implodeParameters();
            return;
            break;
        case 'list-multi':
            var arrValues = new Array;
            for(var i=0; i < ctrl.options.length; i++){
                if(ctrl.options[i].selected){
                    arrValues.push(ctrl.options[i].value);
                }
            }
            currentParams[key][4] = arrValues.toString();
            implodeParameters();
            return;
            break;
        default:
            v = ctrl.value+'';
            break;
    }
    currentParams[key][3] = v;
    implodeParameters();
}

// implode parameters
function implodeParameters(){
    var v, p, s='',r,r1=0,ca = {};    
    for(p in currentParams){
        if(currentParams[p]) {        	
            if (currentParams[p][4]) v = currentParams[p][1]+';'+currentParams[p][2]+';'+currentParams[p][3]+';'+currentParams[p][4];
            else v = currentParams[p][1]+';'+currentParams[p][2]+';'+currentParams[p][3];                        
            if(s && v) s+=' ';
            r = currentParams[p][0];
            if(v && r != r1) s += '#&'+p+'='+ v;
            else if(v) s += '&'+p+'='+ v;
            r1 = r;
        }
    }  	
    document.forms['zone'].rate.value = s;
}

function encode(s){
    s=s+'';
    s = s.replace(/\=/g,'%3D'); // =
    s = s.replace(/\&/g,'%26'); // &
    return s;
}

function decode(s){
    s=s+'';
    s = s.replace(/\%3D/g,'='); // =
    s = s.replace(/\%26/g,'&'); // &
    return s;
}

function region_cities_list(id) {
	window.location.href='index.php?a=5300&rid='+id;	
}
	
var regionRows = []; // stores region information in 2D array. 2nd array = 0-name,1-region,2-value,3-http_equiv
			
				
			
				function addRegion() {
					var f=document.region;
					if(!f) return;
					if(!f.name.value)  alert("<?php echo $_lang["require_region"];?>");		
					else {			
						f.op.value=(f.cmdsaveregion.value=="<?php echo $_lang["save"];?>") ? 'edtregion':'addregion';
						f.submit();
					}
				}
			
				function editRegion(id){
					var f=document.region;
					var opt;					
					if(!f) return;
					f.name.value = regionRows[id][0];					
					f.note.value= regionRows[id][1];							
					f.listindex.value= regionRows[id][3];
					if (regionRows[id][4] == 1) 
					f.isactive.checked = true;	
					else f.isactive.checked = false; 				
					f.id.value=id;
					for(i=0;i<f.rate_zone.options.length;i++) {
						opt = f.rate_zone.options[i];						
						if(opt.value==regionRows[id][2]){
							opt.selected = true;
							break;
						}
					}		
					f.cmdsaveregion.value='<?php echo $_lang["save"];?>';
					f.cmdcancelregion.style.visibility = 'visible';					
					f.name.focus();
				}
			
				function cancelRegion(id){
					var f=document.region;
					if(!f) return;
					f.name.value = '';					
					f.note.value= '';
					f.rate.value= '';
					f.listindex.value= '';				
					f.id.value='';
					f.cmdsaveregion.value='<?php echo $_lang["ec_region_add"];?>';
					f.cmdcancelregion.style.visibility = 'hidden';
				}
			
				function deleteRegions() {
					var f=document.region;
					if(!f) return;
					else if(confirm("<?php echo $_lang['confirm_delete_region']; ?>")) {
						f.op.value='delregions';
						f.submit();
					}
				}
				
				
var cityRows = []; // stores city information in 2D array. 2nd array = 0-name,1-city,2-value,3-http_equiv
			
				function addCity() {
					var f=document.city;
					if(!f) return;
					if(!f.name.value)  alert("<?php echo $_lang["require_city"];?>");		
					else {			
						f.op.value=(f.cmdsavecity.value=="<?php echo $_lang["save"];?>") ? 'edtcity':'addcity';
						f.submit();
					}
				}
			
				function editCity(id){
					var f=document.city;
					var opt;
					if(!f) return;
					f.name.value = cityRows[id][0];
					f.postcode.value = cityRows[id][1];					
					f.note.value= cityRows[id][2];						
					f.listindex.value= cityRows[id][4];
					if (cityRows[id][5] == 1) 
					f.isactive.checked = true;
					else f.isactive.checked = false;					
					f.id.value=id;
					for(i=0;i<f.rate_zone.options.length;i++) {
						opt = f.rate_zone.options[i];						
						if(opt.value==cityRows[id][3]){
							opt.selected = true;
							break;
						}
					}		
					f.cmdsavecity.value='<?php echo $_lang["save"];?>';
					f.cmdcancelcity.style.visibility = 'visible';					
					f.name.focus();
				}
			
				function cancelCity(id){
					var f=document.city;
					if(!f) return;
					f.reset()				
					f.id.value='';
					f.cmdsavecity.value='<?php echo $_lang["ec_city_add"];?>';
					f.cmdcancelcity.style.visibility = 'hidden';
				}
			
				function deleteCities() {
					var f=document.city;
					if(!f) return;
					else if(confirm("<?php echo $_lang['confirm_delete_city']; ?>")) {
						f.op.value='delcities';
						f.submit();
					}
				}							
				var rateFormat = '';
				var rateRows = [];				
				function addRate() {
			
					var f=document.zone;									
					if(!f) return;
					if(!f.zone.value)  alert("<?php echo $_lang["require_zone_name"];?>");		
					else {			
						f.op.value=(f.cmdsaverate.value=="<?php echo $_lang["save"];?>") ? 'edtrate':'addrate';
						f.submit();
					}
				}
			
				function editRate(id){
					var f=document.zone;
					if(!f) return;
					f.zone.value = rateRows[id][0];									
					f.rate.value= rateRows[id][1];	
					f.description.value= rateRows[id][2];							
					f.id.value=id;
					f.cmdsaverate.value='<?php echo $_lang["save"];?>';
					f.cmdcancelrate.style.visibility = 'visible';
					
					f.zone.focus();
				}
			
				function cancelRate(id){
					var f=document.zone;					
					if(!f) return;					
					f.reset();
					f.rate.value= rateFormat;
									
					f.cmdsaverate.value='<?php echo $_lang["ec_rate_add"];?>';
					f.cmdcancelrate.style.visibility = 'hidden';
				}
			
				function deleteRates() {
					var f=document.zone;
					if(!f) return;
					else if(confirm("<?php echo $_lang['confirm_delete_rates']; ?>")) {
						f.op.value='delrates';
						f.submit();
					}
				}
</script>




<div id="overlay" onclick="hideBox()" style="display:none"></div>
<div id="box" name="box" style="display:none">
    <img id="close" src="ecommerce/js/modal_window/images/close.gif" onclick="hideBox()" alt="Close" title="Close this Window" />
    <div id="title" style="font-weight: bold; font-size: 14px;"><?php echo $_lang["ec_edit"]?></div><br />
    <div id="inner-box"></div>
</div>
<br>
<div class="sectionHeader"><?php echo $_lang["ec_manage_taxes"]; ?></div>
<div class="sectionBody">
<br />
<script type="text/javascript" src="media/script/tabpane.js"></script>
	<!-- load modules -->
		<div class="tab-pane" id="cityPane" style="border:0">
			<script type="text/javascript">
		    	tpSettings = new WebFXTabPane( document.getElementById( "cityPane" ) );
		    </script>			
			 <div class="tab-page" id="tabRegions">
	        	<h2 class="tab"><?php echo $_lang["ec_regions_hdr"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabRegions" ) );</script>	
				<form name="region" method="post" action="index.php" onsubmit="return checkForm();">
				<input type="hidden" name="a" value="5301" />
				<input type="hidden" name="op" value="" />
				<input type="hidden" name="id" value="" />
				<br />
			<!-- META tags -->
				<strong><?php echo $_lang['ec_edit_region_hdr']?></strong><br />
				<?php echo $_lang['ec_region_intro'] ;?><br />
				<div class="searchbara">
				 <table width="700" border="0" cellspacing="0" cellpadding="0">
	              <tr style="height: 24px;">
	                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_region_name']; ?></span></td>
	                <td><input name="name" type="text" maxlength="255" value="" class="inputBox" style="width:300px;"  spellcheck="true" />
	                </td>
	              </tr>           
	            
	              <tr style="height: 24px;">
	                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_region_rate']; ?></span></td>
	                <td>
	                <?php echo rate_list()?>
	                </td>
	              </tr>
	            	           
	                <tr style="height: 24px;">
	                <td valign="top" width="100" align="left"><span class='warning'><?php echo $_lang['ec_region_note']; ?></span></td>
	                <td valign="top"><textarea name="note" class="inputBox" rows="2" style="width:300px;height:50px;" ></textarea></td>
	              </tr>
	              
	             
	              <tr style="height: 24px;">
	                <td align="left" style="width:100px;"><span class='warning'><?php echo $_lang['ec_listindex']; ?></span></td>
	                <td>
	                <table border="0" cellspacing="0" cellpadding="0" style="width:325px;"><tr>
	                <td><input name="listindex" type="text" maxlength="3" value="" class="inputBox" style="width:30px;"  />
	                <input type="button" class="button" value="&lt;" onclick="var elm = document.region.listindex;var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();" /><input type="button" class="button" value="&gt;" onclick="var elm = document.region.listindex;var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();" /></td></tr>	                
	                </table>
	                </td>
	              </tr>
	              
	            <tr style="height: 24px;">
	                <td><span class='warning'><?php echo $_lang['ec_region_active']; ?></span></td>
	                <td>
	               <input name="isactive" type="checkbox" class="checkbox" value="1" checked/>
	              
	                </td>
                  </tr>
                  <tr>
                  <td nowrap="nowrap" colspan="2"><br>
						<input type="button" value="<?php echo $_lang["ec_region_add"];?>" name="cmdsaveregion" onclick="addRegion()" /> 
						<input style="visibility:hidden" type="button" value="<?php echo $_lang["cancel"];?>" name="cmdcancelregion" onclick="cancelRegion()" />
						</td>
					  </tr>
				</table>
				</div>
				
				<br>
				<?php			
					$sql = "SELECT sr.*,sra.*,sr.id as rid FROM ".$modx->getFullTableName("site_ec_regions")." sr LEFT JOIN ";
					$sql .= $modx->getFullTableName("site_ec_shipping_rates"). " sra ON sr.rate_zone = sra.zone ORDER BY sr.name";
					$ds = mysql_query($sql);							
					include_once $base_path."manager/includes/controls/datagrid.class.php";
					$grd = new DataGrid('',$ds,$number_of_results = 0); // set page size to 0 t show all items
					$grd->noRecordMsg = $_lang["no_records_found"];
					$grd->useJSFilter=true;
					$grd->cssClass="grid";
					$grd->showRecordInfo=true;
					$grd->columnHeaderClass="gridHeader";
					$grd->itemClass="gridItem";
					$grd->altItemClass="gridAltItem";
					$grd->fields="";
					$grd->columns=$_lang["listnum"]." ,".$_lang["ec_region_name"]." ,".$_lang["ec_region_rate"]." ,".$_lang["user_postcode"]. ",".$_lang["ec_region_active"]. ",".$_lang["delete"];
					$grd->colWidths="40,,100,100,150,100,60";
					$grd->colAligns="center,left,left,left,left,left,left";
					$grd->colTypes ="template:[+num+]||".
					$grd->colTypes.="template:<a href='#' title='".$_lang["click_to_edit_title"]."' onclick='editRegion([+rid+])'>[+name+]</a><span style='display:none;'><script type=\"text/javascript\">regionRows['[+rid+]']=[\"[+js.name+]\",\"[+js.note+]\",\"[+rate_zone+]\",\"[+listindex+]\",\"[+isactive+]\"];</script>||";			
					$grd->colTypes.="template:[+zone+]||";
					$grd->colTypes.="template:[+postcode+]||";					
					$grd->colTypes.='php:if ($row["isactive"] == 1) echo "'.$_lang['yes'].'";   else   echo "'.$_lang['no'].'";||';
					$grd->colTypes.="template:<input name='region[]' type='checkbox' value='[+rid+]'/></a>";				
					echo $grd->render();
				?>
				
				<table border=0 cellpadding=2 cellspacing=0>
					<tr><td colspan="5">&nbsp;</td></tr>
					<tr>
						<td align="right">
							<input type="button" name="cmddeltag" value="<?php echo $_lang["ec_delete_regions"];?>" onclick="deleteRegions();" />
						</td>
					</tr>
				</table>
				</form>
					
	        </div>
			
			
			
	         <div class="tab-page" id="tabRates">
	        	<h2 class="tab"><?php echo $_lang["ec_rates_hdr"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabRates" ) );</script>	
				<form name="zone" method="post" action="index.php">
				<input type="hidden" name="a" value="5303"/>
				<input type="hidden" name="op" value=""/>
				<input type="hidden" name="id" value=""/>
				<br />
			<!-- META tags -->
				<strong><?php echo $_lang['ec_edit_zone_hdr']?></strong><br />
				<?php echo $_lang['ec_rates_intro'] ;?><br />
				<div class="searchbara">
				 <table width="700" border="0" cellspacing="0" cellpadding="0">
	              <tr style="height: 50px;">
	                <td width='150' align="left" valign="top"><span class='warning'><?php echo $_lang['ec_zone_name']; ?>:<br><?php echo$_lang["ec_zone_name_sample"]?></span></td>
	                <td valign="top"><input name="zone" type="text" maxlength="2" value="" class="inputBox" style="width:40px;"  spellcheck="true" />
	                </td>
	              </tr>        
	             <tr style="height: 50px;">
	                <td width='150' align="left" valign="top"><span class='warning'><?php echo $_lang['ec_zone_desc']; ?>:</span></td>
	                <td valign="top"><input name="description" type="text" maxlength="255" value="" class="inputBox" style="width:300px;"  spellcheck="true" />
	                </td>
	              </tr>  
	              
	              <tr style="height: 24px;" valign="top">
	                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_zone_rate']; ?></span></td>
	                <td>	               
	               <input name="rate" type="text" maxlength="65535" value="" class="inputBox" style="width:100px;"  />
            		<br>
		            <div id="stay"></div>
		            <div id="displayparamrow">
		            <div id="displayparams"></div>
		            </div>
		            
	                </td>
	              </tr>   
	              
	              <tr>
                  <td nowrap="nowrap" colspan="2"><br>
						<input type="button" value="<?php echo $_lang["ec_rate_add"];?>" name="cmdsaverate" onclick="addRate()" /> 
						<input style="visibility:hidden" type="button" value="<?php echo $_lang["cancel"];?>" name="cmdcancelrate" onclick="cancelRate()" />
				  </td>
				  </tr>	            
				</table>
				</div>
				
				<br>
				<?php			
					$sql = "SELECT * FROM ".$modx->getFullTableName("site_ec_shipping_rates")." ORDER BY zone";
					$ds = mysql_query($sql);
					include_once $base_path."manager/includes/controls/datagrid.class.php";
					$grd = new DataGrid('',$ds,$number_of_results); // set page size to 0 t show all items
					$grd->noRecordMsg = $_lang["no_records_found"];
					$grd->cssClass="grid";
					$grd->showRecordInfo=true;
					$grd->useJSFilter=true;
					$grd->columnHeaderClass="gridHeader";
					$grd->itemClass="gridItem";
					$grd->altItemClass="gridAltItem";
					$grd->fields="*";
					$grd->columns=$_lang["ec_zone_name"]." ,".$_lang["ec_zone_desc"]." ,".$_lang["delete"];
					$grd->colWidths="100,200,100";
					$grd->colAligns="left,left,left";					
					$grd->colTypes.="template:[+zone+]||";
					$grd->colTypes.="template:<a href='#' title='".$_lang["click_to_edit_title"]."' onclick='editRate([+id+])'>[+description+]</a><span style='display:none;'><script type=\"text/javascript\"> rateRows['[+id+]']=[\"[+zone+]\",\"[+js.rate+]\",\"[+js.description+]\"];</script>||";							$grd->colTypes.="template:<input name='rate[]' type='checkbox' value='[+id+]'/></a>";				
					echo $grd->render();
				?>
				
				<table border=0 cellpadding=2 cellspacing=0>
					<tr><td colspan="5">&nbsp;</td></tr>
					<tr>
						<td align="right">
							<input type="button" name="cmddeltag" value="<?php echo $_lang["ec_delete_rates"];?>" onclick="deleteRates();" />
						</td>
					</tr>
				</table>
				</form>
					
	        </div>
	        
	    </div>
<script type="text/javascript">
    setTimeout('showParameters();',10);  
</script>
