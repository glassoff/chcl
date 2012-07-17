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
	"WHERE action='5203' AND id='$id'";
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
    $tblsc = $dbase . ".`" . $table_prefix . "site_ec_payment_methods`";
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


function deletedocument() {
    if(confirm("<?php echo $_lang['confirm_delete_payment']; ?>")==true) {
        document.location.href="index.php?id=" + document.mutate.id.value + "&a=5007";
    }
}

/** 
 * Snippet properties 
 */





function setTextWrap(ctrl,b){
    if(!ctrl) return;
    ctrl.wrap = (b)? "soft":"off";
}

// Current Params
var currentParams = {};

function showParameters(ctrl) {
    var c,p,df,cp;
    var ar,desc,value,key,dt;

    currentParams = {}; // reset;

    if (ctrl) {
    	f = ctrl.form;
    } else {
        f= document.forms['mutate'];
        if(!f) return;
    }

    // setup parameters
    dp = (f.properties.value) ? f.properties.value.split("&"):"";
    if(!dp) tr.style.display='none';
    else {
        t='<table width="400" style="margin-bottom:3px;margin-left:0px;background-color:#EEEEEE" cellpadding="2" cellspacing="1"><thead><tr><td width="50%"><?php echo $_lang['parameter']; ?></td><td width="50%"><?php echo $_lang['value']; ?></td></tr></thead>';
        for(p = 0; p < dp.length; p++) {
            dp[p]=(dp[p]+'').replace(/^\s|\s$/,""); // trim
            ar = dp[p].split("=");
            key = ar[0]     // param
            ar = (ar[1]+'').split(";");
            desc = ar[0];   // description
            dt = ar[1];     // data type
            value = decode((ar[2])? ar[2]:'');

            // store values for later retrieval
            if (key && dt=='list') currentParams[key] = [desc,dt,value,ar[3]];
            else if (key) currentParams[key] = [desc,dt,value];

            if (dt) {
                switch(dt) {
                case 'int':
                    c = '<input type="text" name="prop_'+key+'" value="'+value+'" size="30" onchange="setParameter(\''+key+'\',\''+dt+'\',this)" />';
                    break;
                case 'menu':
                    value = ar[3];
                    c = '<select name="prop_'+key+'" style="width:168px" onchange="setParameter(\''+key+'\',\''+dt+'\',this)">';
                    ls = (ar[2]+'').split(",");
                    if(currentParams[key]==ar[2]) currentParams[key] = ls[0]; // use first list item as default
                    for(i=0;i<ls.length;i++){
                        c += '<option value="'+ls[i]+'"'+((ls[i]==value)? ' selected="selected"':'')+'>'+ls[i]+'</option>';
                    }
                    c += '</select>';
                    break;
                case 'list':
                    value = ar[3];
                    ls = (ar[2]+'').split(",");
                    if(currentParams[key]==ar[2]) currentParams[key] = ls[0]; // use first list item as default
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
                    c = '<input type="text" name="prop_'+key+'" value="'+value+'" size="30" onchange="setParameter(\''+key+'\',\''+dt+'\',this)" />';
                    break;

                }
                t +='<tr><td bgcolor="#FFFFFF" width="50%">'+desc+'</td><td bgcolor="#FFFFFF" width="50%">'+c+'</td></tr>';
            };
        }
        t+='</table>';
        td = (document.getElementById) ? document.getElementById('displayparams'):document.all['displayparams'];
        td.innerHTML = t;        
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
        case 'menu':
            v = ctrl.options[ctrl.selectedIndex].value;
            currentParams[key][3] = v;
            implodeParameters();
            return;
            break;
        case 'list':
            v = ctrl.options[ctrl.selectedIndex].value;
            currentParams[key][3] = v;
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
            currentParams[key][3] = arrValues.toString();
            implodeParameters();
            return;
            break;
        default:
            v = ctrl.value+'';
            break;
    }
    currentParams[key][2] = v;
    implodeParameters();
}

// implode parameters
function implodeParameters(){
    var v, p, s='';
    for(p in currentParams){
        if(currentParams[p]) {
            v = currentParams[p].join(";");
            if(s && v) s+=' ';
            if(v) s += '&'+p+'='+ v;
        }
    }
    document.forms['mutate'].properties.value = s;
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
<input type="hidden" name="a" value="5205" />
<input type="hidden" name="id" value="<?php echo $content['id'];?>" />
<input type="hidden" name="mode" value="<?php echo $_REQUEST['a'];?>" />
<input type="hidden" name="variablesmodified" value="" />
<input type="submit" name="save" style="display:none" />
<div class="subTitle">
   <span class="right"><?php echo $_lang['ec_pm_edit_hdr']; ?></span>
   <table cellpadding="0" cellspacing="0" class="actionButtons">
        <tr>
            <td id="Button1"><a href="#" onclick="documentDirty=false; document.mutate.save.click();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" /> <?php echo $_lang['save']; ?></a></td>
             <?php if ($_REQUEST['a'] != 5203) {?>
            <td id="Button2"><a href="#" onclick="deletedocument();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/delete.gif" /> <?php echo $_lang['delete']; ?></a></td>
            <?php }?>
            <td id="Button5"><a href="#" onclick="documentDirty=false;document.location.href='index.php?a=5202';"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" /> <?php echo $_lang['cancel']; ?></a></td>
        </tr>
    </table>
    <script type="text/javascript">
        <?php if($_REQUEST['a']=='5203') { ?>document.getElementById("Button2").className='disabled';<?php } ?>
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

<div class="sectionHeader"><?php echo $_lang['ec_pm_form_hdr']; ?></div>
<div class="sectionBody">
            <?php
?>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tr style="height: 24px;">
                <td width='250' align="left"><span class='warning'><?php echo $_lang['ec_pm_name']; ?></span></td>
                <td><input name="name" type="text" maxlength="255" value="<?php echo htmlspecialchars(stripslashes($content['name']));?>" class="inputBox" style="width:300px;" onchange="" spellcheck="true" />
                </td>
              </tr>
            
              <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang["ec_payment_auto"]; ?></span></td>
                <td>
                  <input type="hidden" name="auto" value="0" onchange=""/>
                  <input name="auto" type="checkbox"  class="checkbox" value="1" <?php echo (isset($content['auto']) && $content['auto']==1) ? "checked" : "" ;?> />
              
                </td>
              </tr>	 
          
              <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang["ec_payment_confirm"]; ?></span></td>
                <td>
                  <input type="hidden" name="confirm" value="0" onchange=""/>
                  <input name="confirm" type="checkbox"  class="checkbox" value="1" <?php echo (isset($content['confirm']) && $content['confirm']==1) ? "checked" : "" ;?> />
              
                </td>
              </tr>	 
            
              
              <tr style="height: 24px;">
                <td valign="top" width="100" align="left"><span class='warning'><?php echo $_lang['ec_pm_desc']; ?></span></td>
                <td valign="top"><textarea name="description" class="inputBox" rows="3" style="width:300px;" onchange=""><?php echo htmlspecialchars(stripslashes($content['description']));?></textarea></td>
              </tr>
             
           
             
              <tr style="height: 24px;">
                <td align="left" style="width:100px;"><span class='warning'><?php echo $_lang['ec_listindex']; ?></span></td>
                <td>
                <table border="0" cellspacing="0" cellpadding="0" style="width:325px;"><tr>
                <td><input name="listindex" type="text" maxlength="3" value="<?php echo $content['listindex'];?>" class="inputBox" style="width:30px;" onchange="" /><input type="button" class="button" value="&lt;" onclick="var elm = document.mutate.listindex;var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();" /><input type="button" class="button" value="&gt;" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();" /></td>
                
                </table>
                </td>
              </tr>
              
                 
              
             <tr style="height: 24px;">
                <td valign="top" width="100" align="left"><span class='warning'><?php echo $_lang['ec_pm_page']; ?></span></td>
                <td valign="top">
                <input name="payment_page" type="text" maxlength="255" value="<?php echo htmlspecialchars(stripslashes($content['payment_page']));?>" class="inputBox" style="width:300px;" onchange="" spellcheck="true" />
                </td>
              </tr>
              
             <tr style="height: 24px;">
                <td valign="top" width="100" align="left"><span class='warning'>»конка</span></td>
                <td valign="top">
                <input name="icon" type="text" maxlength="255" value="<?php echo htmlspecialchars(stripslashes($content['icon']));?>" class="inputBox" style="width:300px;" onchange="" spellcheck="true" />
                </td>
              </tr>              
                
              
          <tr>
            <td align="left" valign="top"><span class='warning'><?php echo $_lang['ec_pm_params']; ?>:</span></td>
            <td align="left" valign="top">
            <textarea name="properties" class="inputBox" rows="4" style="width:300px;" onChange="showParameters(this)";><?php echo $content['params'];?></textarea>
            <input type="button" value=".." style="width:16px; margin-left:2px;" title="<?php echo $_lang['update_params']; ?>" />           <br>
             <div id="stay"></div>
            <div id="displayparams"></div>
            </td>
          </tr>
          <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_pm_active']; ?></span></td>
                <td>
                  <input type="hidden" name="active" value="0" onchange=""/>
                <input name="active" type="checkbox"  class="checkbox" value="1" <?php echo (isset($content['active']) && $content['active']==1) ? "checked" : "" ;?> />
              
                </td>
              </tr>	 
          
          
              
</div>
<script type="text/javascript">
    setTimeout('showParameters();',10);
</script>


