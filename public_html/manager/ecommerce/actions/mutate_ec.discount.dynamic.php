<?php
if (IN_MANAGER_MODE != "true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
// check permissions

if(!$modx->hasPermission('ec_payment_methods')) {
   $e->setError(3);
   $e->dumpError();
}
 
if (!isset ($_REQUEST['id'])) {
    $id = 0;
} else {
    $id = !empty ($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
}

// check to see the document isn't locked
$sql =	"SELECT internalKey, username FROM ".$modx->getFullTableName('active_users') .
	"WHERE action='5402' AND id='$id'";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);
if ($limit > 1) {
    for ($i = 0; $i < $limit; $i++) {
        $lock = mysql_fetch_assoc($rs);
        if ($lock['internalKey'] != $modx->getLoginUserID()) {
            $msg = sprintf($_lang["lock_msg"], $lock['username'], "document");
            $e->setError(5, $msg);
            $e->dumpError();
        }
    }
}
// end check for lock

// get document groups for current user
if (!empty ($id)) {
    $tblsc = $dbase . ".`" . $table_prefix . "site_ec_discounts`";
    $sql = "SELECT * FROM $tblsc WHERE id = $id;";
    $rs = mysql_query($sql);
    $limit = mysql_num_rows($rs);
    if ($limit > 1) {
            $e->setError(6);
            $e->dumpError();
    }
    if ($limit < 1) {
            $e->setError(3);
            $e->dumpError();
    }
    $content = mysql_fetch_assoc($rs);
    $content['groupids'] = unserialize($content['groupids']);
} else {
    $content = array ();
}

// restore saved form
$formRestored = false;
if ($modx->manager->hasFormValues()) {
    $modx->manager->loadFormValues();
    $formRestored = true;
}

// restore saved form
$formRestored = false;
if ($modx->manager->hasFormValues()) {
    $modx->manager->loadFormValues();
    $formRestored = true;
}

// retain form values if template was changed
// edited to convert pub_date and unpub_date
// sottwell 02-09-2006

// increase menu index if this is a new document
?>
<script type="text/javascript">
function changestate(element) {
    currval = eval(element).value;
    if(currval==1) {
        eval(element).value=0;
    } else {
        eval(element).value=1;
    }   
}
function deleteDiscount() {
    if(confirm("<?php echo $_lang['confirm_delete_discount']; ?>")==true) {
        document.location.href="index.php?id=<?php echo $id;?>&a=5404";
    }
}
/** 
 * Snippet rule 
 */
function setTextWrap(ctrl,b){
    if(!ctrl) return;
    ctrl.wrap = (b)? "soft":"off";
}

// Current Params
var currentParams = {};

function removeRow(index) {
	var f= document.forms['mutate'],o = '';
    if(!f) return;
    r = (f.rule.value) ? f.rule.value.split("#"):"";
    if(!r) return;     
    for(v = 0; v < r.length; v++) {
    	if (v != index && v != '') o += '#'+r[v];
    }
	f.rule.value = o;
	showParameters();
}
var time = new Date();
var col_id = time.getTime();

function addRow() {
	var f= document.forms['mutate'];
    if(!f) return;
    r = (f.rule.value) ? f.rule.value.split("#"):"";     
    row_temp = '#&col'+(col_id++)+'=x;float;0&col'+(col_id++)+'=x;float;0&col'+(col_id++)+'=x;float;0';    
	f.rule.value += row_temp;
	showParameters();
}


function showParameters(ctrl) {
    var c,p,df,cp;
    var ar,desc,value,key,dt,r,d;
    currentParams = {}; // reset;
    if (ctrl) {
    	f = ctrl.form;
    } else {
        f= document.forms['mutate'];
        if(!f) return;
    }
    // setup parameters
    tr = (document.getElementById) ? document.getElementById('displayparamrow'):document.all['displayparamrow'];
    r = (f.rule.value) ? f.rule.value.split("#"):"";  
    if(!r) tr.style.display='none';
    else {
        t='<table width="400" style="margin-bottom:3px;margin-left:0px;background-color:#EEEEEE" cellpadding="2" cellspacing="1"><thead><tr><td width="30%"><?php echo $_lang['ec_min_value']; ?></td><td width="30%"><?php echo $_lang['ec_max_value']; ?></td><td width="50%"><?php echo $_lang['discount'];?></td><td width=""><?php echo $_lang['delete'];?></td></tr></thead>';
		         
        for(v = 0; v < r.length; v++) {        
        dp = r[v].split("&");               
        
		for(p = 0; p < dp.length; p++) {       	
			dp[p]=(dp[p]+'').replace(/^\s|\s$/,""); // trim			
            ar = dp[p].split("=");
            key = ar[0]     // param
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
                    c = '<input type="text" name="prop_'+key+'" value="'+value+'" size="30" onchange="setParameter(\''+key+'\',\''+dt+'\',this)" />';
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
    document.forms['mutate'].rule.value = s;
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
</script>
<form name="mutate" method="post" enctype="multipart/form-data" action="index.php">
<input type="hidden" name="a" value="5403" />
<input type="hidden" name="id" value="<?php echo $content['id'];?>" />
<input type="hidden" name="mode" value="<?php echo $_REQUEST['a'];?>" />
<input type="hidden" name="variablesmodified" value="">
<input type="submit" name="save" style="display:none"/>
<div class="subTitle">
   <span class="right"><?php echo $_lang['ec_discount_hdr']; ?></span>
   <table cellpadding="0" cellspacing="0" class="actionButtons">
        <tr>
            <td id="Button1"><a href="#" onclick=" document.mutate.save.click();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" /> <?php echo $_lang['save']; ?></a></td>
             <?php if ($_REQUEST['a'] != 5401) {?>
            <td id="Button2"><a href="#" onclick="deletedocument();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/delete.gif" /> <?php echo $_lang['delete']; ?></a></td>
            <?php }?>
            <td id="Button5"><a href="#" onclick="document.location.href='index.php?a=5400';"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" /> <?php echo $_lang['cancel']; ?></a></td>
        </tr>
    </table>
    <script type="text/javascript">
        <?php if($_REQUEST['a']=='5402') { ?>document.getElementById("Button2").className='disabled';<?php } ?>
    </script>
    <div class="stay">
    <table border="0" cellspacing="1" cellpadding="1">
    <tr>
        <td><span class="comment">&nbsp;<?php echo $_lang["after_saving"];?>:</span></td>
        <?php if ($modx->hasPermission('ec_new_item')) { ?>
            <td><input name="stay" id="stay1" type="radio" class="radio" value="1" <?php echo $_REQUEST['stay']=='1' ? "checked='checked'":'' ?> /></td><td><label for="stay1" class="comment"><?php echo $_lang['stay_new']; ?></label></td>
        <?php } ?>
        <td><input name="stay" id="stay2" type="radio" class="radio" value="2" <?php echo $_REQUEST['stay']=='2' ? "checked='checked'":'' ?> /></td><td><label for="stay2" class="comment"><?php echo $_lang['stay']; ?></label></td>
        <td><input name="stay" id="stay3" type="radio" class="radio" value="" <?php echo $_REQUEST['stay']=='' ? "checked='checked'":'' ?> /></td><td><label for="stay3" class="comment"><?php echo $_lang['close']; ?></label></td>
    </tr>
    </table>
    </div>
</div>
<div class="sectionHeader"><?php echo $_lang['ec_discount_form_hdr'];?></div>
<div class="sectionBody">
          <table width="500" border="0" cellspacing="0" cellpadding="0">
              <tr style="height: 24px;">
                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_discount_name']; ?></span></td>
                <td><input name="name" type="text" maxlength="255" value="<?php echo htmlspecialchars(stripslashes($content['name']));?>" class="inputBox" style="width:300px;" onchange="" spellcheck="true" />
                </td>
          </tr>     
          <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_pm_active']; ?></span></td>
                <td>
                <input type="hidden" name="active" value="0" onchange=""/>
                <input name="active" type="checkbox"  class="checkbox" value="1" <?php echo (isset($content['active']) && $content['active']==1) ? "checked" : "" ;?>/>              
                </td>
          </tr>	 
          <tr style="height: 24px;">
                <td valign="top" width="100" align="left"><span class='warning'><?php echo $_lang['ec_pm_desc']; ?></span></td>
                <td valign="top"><textarea name="description" class="inputBox" rows="3" style="width:300px;" onchange=""><?php echo htmlspecialchars(stripslashes($content['description']));?></textarea></td>
          </tr>            
          <tr>
          	<td colspan="2" height="10" valign="middle">
          	<div class="stay"></div>
          	</td>
          </tr>          
          <tr>
            <td align="left" valign="top"><span class='warning'><?php echo $_lang['ec_discount_rules']; ?>:</span></td>
            <td align="left" valign="top">
            <input name="rule" type="hidden" maxlength="63000" onChange="showParameters(this)" value="<?php echo $content['rule'];?>">         
            <br>
            <div id="stay"></div>
            <div id="displayparamrow">
		        <div id="displayparams"></div>
		    </div>
            <input type="button" value="<?php echo $_lang['ec_discount_add_rule']; ?>" onclick="addRow()">
            </td>
          </tr>                  
          <tr>
          	<td colspan="2" height="10" valign="middle">
          	<div class="stay"></div>
          	</td>
          </tr>  
          <tr>
            <td align="left" valign="top"><span class='warning'><?php echo $_lang['ec_discount_groupids']; ?>:</span></td>
            <td align="left" valign="top">
            
	        	<?php
	        	$sql = "SELECT name, id FROM ".$modx->getFullTableName('webgroup_names')." ORDER BY name";
	        	$rs = mysql_query($sql);
	        	//echo $sql; 
	        	$limit = mysql_num_rows($rs);
				if ($limit > 0) {		
					 for ($i = 0; $i < $limit; $i++) {
        				$group = mysql_fetch_assoc($rs);
        				if (is_array($content['groupids']) && in_array($group['id'], $content['groupids'])) $checked = 'checked'; else $checked = '';
        				echo "<input type=\"checkbox\" id=\"chb$group[id]\" $checked  value=\"$group[id]\" name=\"groupids[]\"><label for=\"chb$group[id]\">$group[name]</label>";
					 }	
				}  	
			  	?>
          	</td>
          </tr>       
          <tr>
          	<td colspan="2" height="10" valign="middle">
          	<div class="stay"></div>
          	</td>
          </tr>   
         </table>              
</div>
<script type="text/javascript">
    setTimeout('showParameters();',10);
</script>