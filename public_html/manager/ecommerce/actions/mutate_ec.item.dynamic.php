<?php
if (IN_MANAGER_MODE != "true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
// check permissions
switch($_REQUEST['a']) {
  case 5002:
    if(!$modx->hasPermission('ec_edit_item')) {
      $e->setError(3);
      $e->dumpError();
    }
    break;
  case 5001:
    if(!$modx->hasPermission('ec_new_item')) {
      $e->setError(3);
      $e->dumpError();
    }     
    break;
 default:
    $e->setError(3);
   $e->dumpError();
}
if ((!isset($_REQUEST['pid']) || $_REQUEST['pid'] == 0) && $_REQUEST['a'] == '5001' && isset($_SESSION['ec_list_pid'])) {
	$_REQUEST['pid'] = $_SESSION['ec_list_pid'];	
}
if (!isset ($_REQUEST['id'])) {
    $id = 0;
} else {
    $id = !empty ($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
}

// check to see the document isn't locked
$sql =	"SELECT internalKey, username FROM ".$modx->getFullTableName('active_users') .
	    "WHERE action='5002' AND id='$id'";
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
if ($_SESSION['mgrDocgroups']) {
    $docgrp = implode(",", $_SESSION['mgrDocgroups']);
}

if (!empty ($id)) {
    $tblsc = $dbase . ".`" . $table_prefix . "site_ec_items`";    
    $sql = "SELECT DISTINCT sc.*
            FROM $tblsc sc
            WHERE sc.id = $id;";
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
// retain form values if template was changed
// edited to convert pub_date and unpub_date
// sottwell 02-09-2006
if ($formRestored == true || isset ($_REQUEST['newtemplate'])) {
    $content = array_merge($content, $_POST);
    $content["content"] = $_POST["ta"];
    if (empty ($content["date_issue"])) {
        unset ($content["date_issue"]);
    } else {
        $date_issue = $content['date_issue'];
        list ($d, $m, $Y, $H, $M, $S) = sscanf($date_issue, "%2d-%2d-%4d %2d:%2d:%2d");
        $pub_date = strtotime("$m/$d/$Y $H:$M:$S");
        $content['date_issue'] = $date_issue;
	}   
}
// increase menu index if this is a new document
if (!isset ($_REQUEST["id"])) {
    if (!isset ($auto_menuindex) || $auto_menuindex) {
        $pid = intval($_REQUEST["pid"]);
        $tbl = $modx->getFullTableName("site_ec_items");
        $sql = "SELECT count(*) as 'cnt' FROM $tbl WHERE parent='$pid'";
        $content["menuindex"] = $modx->db->getValue($sql);
    } else {
        $content['menuindex'] = 0;
    }
}

if (isset ($_POST['which_editor'])) {
    $which_editor = $_POST['which_editor'];
}
?>
<script type="text/javascript" src="media/script/datefunctions.js"></script>
<script type="text/javascript">
// save tree folder state
parent.tree.saveFolderState();

function changestate(element) {
    currval = eval(element).value;
    if(currval==1) {
        eval(element).value=0;
    } else {
        eval(element).value=1;
    }
    
}

function deletedocument() {
    if(confirm("<?php echo $_lang['confirm_delete_item']; ?>")==true) {
        document.location.href="index.php?id=" + document.mutate.id.value + "&a=5007";
    }
}

function previewdocument() {
    var win = window.frames['preview'];
    url = "../index.php?id=" + document.mutate.id.value + "&manprev=z";
    nQ = "id=" + document.mutate.id.value + "&manprev=z"; // new querysting
    oQ = (win.location.href.split("?"))[1]; // old querysting
    if (nQ != oQ) {
        win.location.href = url;
        win.alreadyPreviewed = true;
    }
}
// Added by Raymond
var modVariables = [];
function setVariableModified(fieldName){
    var i, isDirty = false, mv = modVariables;
    for(i=0;i<mv.length;i++){
        if (mv[i]==fieldName) {
            isDirty=true;
        }
    }
    if (!isDirty) {
        mv[mv.length]=fieldName;
        var f = document.forms['mutate'];
        f.variablesmodified.value=mv.join(",");
    }
}

function saveRefreshPreview(){
    var f = document.forms['mutate'];
    
    f.target = "preview";
    f.refresh_preview.value=1;
    f.save.click();
    setTimeout("document.forms['mutate'].target='';document.forms['mutate'].refresh_preview.value=0",100);
}
// end modifications
var allowParentSelection = false;
var allowParentSelectionForItemSelector = false;
var allowLinkSelection = false;

function enableLinkSelection(b){
  parent.tree.ca = "link";
    var closed = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folder.gif";
    var opened = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folderopen.gif";
    if(b) {
        document.images["llock"].src = opened;
        allowLinkSelection = true;
    }
    else {
        document.images["llock"].src = closed;
        allowLinkSelection = false;
    }
}

function setLink(lId) {
    if (!allowLinkSelection) {
        window.location.href="index.php?a=3&id="+lId;
        return;
    }
    else {  
            
            document.mutate.ta.value=lId;
    }
}



function enableParentSelection(b){
  parent.tree.ca = "parent";
    var closed = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folder.gif";
    var opened = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folderopen.gif";
    if(b) {
        document.images["plock"].src = opened;
        allowParentSelection = true;
    }
    else {
        document.images["plock"].src = closed;
        allowParentSelection = false;
    }
}

function enableParentSelectionForItemSelector(b){	
  	
    var closed = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folder.gif";
    var opened = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folderopen.gif";
    if(b) {
        document.images["plock1"].src = opened;
        allowParentSelectionForItemSelector = true;
        parent.tree.ca = "parentforitemselector";
    }
    else {
        document.images["plock1"].src = closed;
        allowParentSelectionForItemSelector = false;
        parent.tree.ca = "";
    }
}


function setParent(pId, pName) {
    if (!allowParentSelection) {
        window.location.href="index.php?a=3&id="+pId;
        return;
    }
    else {
        if(pId==0 || checkParentChildRelation(pId, pName)){
            
            document.mutate.parent.value=pId;
            var elm = document.getElementById('parentName');
            if(elm) {
                elm.innerHTML = (pId + " (" + pName + ")");
            }
        }
    }
}
// check if the selected parent is a child of this document
function checkParentChildRelation(pId, pName) {
    var sp;
    var id = document.mutate.id.value;
    var tdoc = parent.tree.document;
    var pn = (tdoc.getElementById) ? tdoc.getElementById("node"+pId) : tdoc.all["node"+pId];
    if(!pn) return;
    if (pn.id.substr(4)==id) {
        alert("<?php echo $_lang['illegal_parent_self']; ?>");
        return;
    }
    else {
        while (pn.getAttribute("p")>0) {
            pId = pn.getAttribute("p");
            pn = (tdoc.getElementById) ? tdoc.getElementById("node"+pId) : tdoc.all["node"+pId];
            if (pn.id.substr(4)==id) {
                alert("<?php echo $_lang['illegal_parent_child']; ?>");
                return;
            }
        }
    }
    return true;
}

function clearKeywordSelection() {
    var opt = document.mutate.elements["keywords[]"].options;
    for(i = 0; i < opt.length; i++) {
        opt[i].selected = false;
    }
}

function clearMetatagSelection() {
    var opt = document.mutate.elements["metatags[]"].options;
    for(i = 0; i < opt.length; i++) {
        opt[i].selected = false;
    }
}

// ADDED BY S BRENNAN
var curTemplate = -1;
var curTemplateIndex = 0;
function storeCurTemplate(){
    var dropTemplate = document.getElementById('template');
    if (dropTemplate){ 
        for (var i=0; i<dropTemplate.length; i++){
            if (dropTemplate[i].selected){
                curTemplate = dropTemplate[i].value;
                curTemplateIndex = i;
            }
        }
    }
}
function templateWarning(){
    var dropTemplate = document.getElementById('template');
    if (dropTemplate){ 
        for (var i=0; i<dropTemplate.length; i++){
            if (dropTemplate[i].selected){
                newTemplate = dropTemplate[i].value;
                break;
            }
        }
    }
    if (curTemplate == newTemplate){return;}

    if (confirm('<?php echo $_lang['tmplvar_change_template_msg']?>')){
        
        document.mutate.a.value = <?php echo $action; ?>;
        document.mutate.newtemplate.value = newTemplate;
        document.mutate.submit();
    }
    else{
        dropTemplate[curTemplateIndex].selected = true;
    }
}
// END ADDED BY S BRENNAN
// Added for RTE selection
function changeRTE(){
    var whichEditor = document.getElementById('which_editor');
    if (whichEditor){
        for (var i=0; i<whichEditor.length; i++){
            if (whichEditor[i].selected){
                newEditor = whichEditor[i].value;
                break;
            }
        }
    }
    var dropTemplate = document.getElementById('template');
    if (dropTemplate){ 
        for (var i=0; i<dropTemplate.length; i++){
            if (dropTemplate[i].selected){
                newTemplate = dropTemplate[i].value;
                break;
            }
         }          
    }    
    document.mutate.a.value = <?php echo $action; ?>;
    document.mutate.newtemplate.value = newTemplate;
    document.mutate.which_editor.value = newEditor;
    document.mutate.submit();
}
/** 
 * Snippet properties 
 */
</script>
<form name="mutate" method="post" enctype="multipart/form-data" action="index.php">
<input type="hidden" name="a" value="5003" />
<input type="hidden" name="id" value="<?php echo $content['id'];?>" />
<input type="hidden" name="mode" value="<?php echo $_REQUEST['a'];?>" />
<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo isset($upload_maxsize)? $upload_maxsize:30048576; ?>" />
<input type="hidden" name="refresh_preview" value="0" />
<input type="hidden" name="variablesmodified" value="" />
<input type="hidden" name="newtemplate" value="" />
<div class="subTitle">
    <span class="right"><?php echo $_lang['ec_edit_item_hdr']; ?></span>

    <table cellpadding="0" cellspacing="0" class="actionButtons">
        <tr>
            <td id="Button1"><a href="#" onclick="documentDirty=false; document.mutate.save.click();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" /> <?php echo $_lang['save']; ?></a></td>
             <?php if ($_REQUEST['a'] != 5001 && $modx->hasPermission('ec_delete_item'))  {?>
            <td id="Button2"><a href="#" onclick="documentDirty=false;deletedocument();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/delete.gif" /> <?php echo $_lang['delete']; ?></a></td>
            <?php }?>
            <td id="Button5"><a href="#" onclick="document.location.href='index.php?a=5000';"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" /> <?php echo $_lang['cancel']; ?></a></td>
        </tr>
    </table>   
   
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

<div class="sectionHeader"><?php echo $_lang['ec_item_settings']; ?></div><div class="sectionBody">
    <script type="text/javascript" src="media/script/tabpane.js"></script>   

    <div class="tab-pane" id="documentPane">
        <script type="text/javascript">
            tpSettings = new WebFXTabPane( document.getElementById( "documentPane" ),false);
        </script>

        <!-- General -->
        <div class="tab-page" id="tabGeneral">
            <h2 class="tab"><?php echo $_lang["settings_general"] ?></h2>
            <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabGeneral" ) );</script>
            <?php
?>
            <table width="650" border="0" cellspacing="0" cellpadding="0">
              <?php if ($_REQUEST['a'] == '5002') {?>
              <tr style="height: 24px;">
                <td align="left"><span class='warning'><?php echo $_lang['ec_item_id']; ?></span></td>
                <td><strong><?php echo $content['id'];?></strong></td>
              </tr>	
              <?php }?>
              <tr style="height: 24px;">
                <td width='280' align="left"><span class='warning'><?php echo $_lang['ec_item_title']; ?></span></td>
                <td><input name="pagetitle" type="text" maxlength="255" value="<?php echo htmlspecialchars(stripslashes($content['pagetitle']));?>" class="inputBox" style="width:300px;" onchange="" spellcheck="true" />&nbsp;&nbsp;<img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif" onmouseover="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02.gif';" onmouseout="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif';" alt="<?php echo $_lang['document_title_help']; ?>" onclick="alert(this.alt);" style="cursor:help;" /></td>
              </tr>
              <tr style="height: 24px;">
                <td align="left"><span class='warning'><?php echo $_lang['ec_item_long_title']; ?></span></td>
                <td><input name="longtitle" type="text" maxlength="255" value="<?php echo htmlspecialchars(stripslashes($content['longtitle']));?>" class="inputBox" style="width:300px;" onchange="" spellcheck="true" />&nbsp;&nbsp;<img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif" onmouseover="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02.gif';" onmouseout="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif';" alt="<?php echo $_lang['document_long_title_help']; ?>" onclick="alert(this.alt);" style="cursor:help;" /></td>
              </tr>
             
             
               <tr style="height: 10px;">
                <td align="left" colspan="2" height="10">
                <div class="stay"></div>
                </td>
              </tr>
              
             <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_item_date_issue']; ?></span></td>
                <td><input name="date_issue" readonly value="<?php echo $content['date_issue']=="0" || !isset($content['date_issue']) ? "" : strftime("%d-%m-%Y %H:%M:%S", $content['date_issue']);?>" onblur="" />
                <span id="ec_item_date_issue_show" style="display:none"></span>
                        <a onclick=" cal3.popup();" onmouseover="window.status='<?php echo $_lang['select_date']; ?>'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal.gif" width="16" height="16" border="0" alt="<?php echo $_lang['select_date']; ?>" /></a>
                        <a onclick="document.mutate.date_issue.value=''; document.getElementById('ec_item_date_issue_show').innerHTML='(<?php echo $_lang['not_set']?>)'; return true;" onmouseover="window.status='<?php echo $_lang['remove_date']?>'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="<?php echo $_lang['remove_date']; ?>" /></a>                   
                </td>
              </tr>
              <tr>
                  <td></td>
                  <td style="color: #555;font-size:10px"><em> dd-mm-YYYY HH:MM:SS</em></td>
              </tr>  
              


              <tr style="height: 10px;">
                <td align="left" colspan="2" height="10">
                <div class="stay"></div>
                </td>
              </tr>
             
                <tr style="height: 24px;">
                <td align="left"><span class='warning'>Производитель</span></td>
                <td><input name="producer" type="text" size="50" value="<?php echo htmlspecialchars(stripslashes($content['producer']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>
              
                <tr style="height: 24px;">
                <td align="left"><span class='warning'>Поставщик</span></td>
                <td><input name="vendor" type="text" size="50" value="<?php echo htmlspecialchars(stripslashes($content['vendor']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>
              
             
                <tr style="height: 24px;">
                <td align="left"><span class='warning'>Страна</span></td>
                <td><input name="country" type="text" size="50" value="<?php echo htmlspecialchars(stripslashes($content['country']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>
              
             
               <tr style="height: 10px;">
                <td align="left" colspan="2" height="10">
                <div class="stay"></div>
                </td>
              </tr>
             
                 <tr style="height: 24px;">
                <td align="left"><span class='warning'>Материал</span></td>
                <td><input name="material" type="text" size="50" value="<?php echo htmlspecialchars(stripslashes($content['material']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>
              
                <tr style="height: 24px;">
                <td align="left"><span class='warning'>Состав</span></td>
                <td><input name="composition" type="text" size="50" value="<?php echo htmlspecialchars(stripslashes($content['composition']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>
              
             
                <tr style="height: 24px;">
                <td align="left"><span class='warning'>Цвет</span></td>
                <td><input name="color" type="text" size="50" value="<?php echo htmlspecialchars(stripslashes($content['color']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>
             
             
             
             
               <tr style="height: 10px;">
                <td align="left" colspan="2" height="10">
                <div class="stay"></div>
                </td>
              </tr>
             
                   <!--tr style="height: 24px;">
                <td align="left"><span class='warning'>Рост</span></td>
                <td><input name="growth" type="text" size="20" value="<?php echo htmlspecialchars(stripslashes($content['growth']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr-->
              
             
                <tr style="height: 24px;">
                <td align="left"><span class='warning'>Размер</span></td>
                <td><input name="size" type="text" size="20" value="<?php echo htmlspecialchars(stripslashes($content['size']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>
             
             
             
                            <tr style="height: 10px;">
                <td align="left" colspan="2" height="10">
                <div class="stay"></div>
                </td>
              </tr>
             
             
              <tr style="height: 24px;">
                <td align="left"><span class='warning'><?php echo $_lang['ec_item_acc_id']; ?></span></td>
                <td><input name="acc_id" type="text" maxlength="32" size="32" value="<?php echo htmlspecialchars(stripslashes($content['acc_id']));?>" class="inputBox" style="" onchange="" spellcheck="true" />&nbsp;&nbsp;<img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif" onmouseover="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02.gif';" onmouseout="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif';" alt="<?php echo $_lang['ec_item_acc_id_help']; ?>" onclick="alert(this.alt);" style="cursor:help;" /></td>
              </tr>
              <tr style="height: 24px;">
                <td align="left"><span class='warning'>Код в 1С</span></td>
                <td><input name="1c_code" type="text" maxlength="32" size="32" value="<?php echo htmlspecialchars(stripslashes($content['1c_code']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>              
              
              <script>
              	function showTablePrices(type){
					var pricetable = document.getElementById('setprice-table_' + type);
					if(pricetable.style.display != 'none'){
						//return false;
					}
					pricetable.style.display = "table";
					var size_str = document.forms.mutate.size.value;
					var start_price = 0;
					if (type=='opt'){
						start_price = document.forms.mutate.price_opt.value;
					}
					else if (type=="retail"){
						start_price = document.forms.mutate.retail_price.value;
					}
					if(size_str){
						var size_arr = size_str.split(",");
						for (var i = 0; i < size_arr.length; i++) {
							var size = size_arr[i];
							//pricetable
							var input_sizes = $(pricetable).getElements("input[name^=sizes]");
							var exists = false;
							input_sizes.each(function(item, index){
								if ( item.value==size )
									exists = true;
							});
							if(!exists)
								pricetable.innerHTML = pricetable.innerHTML + '<tr><td align="center">'+size+'<input type="hidden" name="sizes[]" value="'+size+'" /></td><td><input size="11" value="'+start_price+'" class="inputBox" type="text" name="'+type+'_prices[]" /></td></tr>';
						}

						//pricetable.innerHTML = pricetable.innerHTML + '<tr><td colspan="2"><input id="setprices" type="submit" name="" value="OK" onclick="setPrices();return false;"/></td></tr>';
					}
					else{
						alert("Введите размеры");
						return false;
					}
              	}
              </script>
              
              <?php
              ##
				$sizes_str = $content['size'];
				$sizes = explode(",", $sizes_str);
				
              if($content['id']){
              	$prices = getPrices($content['id'], 'retail');//print_r($prices);die();
              	
              	$retail_pricetabletr = "";
              	$retail_price = $content['retail_price'];
              	foreach($sizes as $size_item){
              		//$size_item = trim($size_item);
              		$price_item = $retail_price;
              		if( $prices[$size_item] > 0){
              			$price_item = $prices[$size_item];
              		}
              		$retail_pricetabletr .= '<tr><td align="center">'.$size_item.'<input type="hidden" name="sizes[]" value="'.$size_item.'" /></td><td><input size="11" value="'.$price_item.'" class="inputBox" type="text" name="retail_prices[]" /></td></tr>';
              	}
              }
              
              ?>
              <tr style="height: 24px;">
                <td align="left"><span class='warning'><?php echo $_lang['ec_item_retail_price']; ?></span></td>
                <td><input name="retail_price" type="text" maxlength="11" size="11" value="<?php echo htmlspecialchars(stripslashes($content['retail_price']));?>" class="inputBox" style="" onchange="" spellcheck="true" />&nbsp; <a href="javascript:;" onclick="showTablePrices('retail');">установить в зависимости от размера</a></td>
              </tr>
              <tr>
              	<td></td>
              	<td>
              		<table cellspacing="0" cellpadding="5" style="<?php if(!$prices){echo 'display:none;';}?>" border="1" id="setprice-table_retail">
              			<tr>
              				<th width="80">Размер</th>
              				<th width="100">Цена</th>
              			</tr>
              			<?php echo $retail_pricetabletr; ?>
              		</table>
              	</td>
              </tr>
             
              
             
              
              <?php
              ##
              if($content['id']){
              	$prices = getPrices($content['id'], 'opt');
              	
                $opt_pricetabletr = "";
              	$price_opt = $content['price_opt'];
              	foreach($sizes as $size_item){
              		$price_item = $price_opt;
              		if($prices[$size_item] > 0){
              			$price_item = $prices[$size_item];
              		}
              		$opt_pricetabletr .= '<tr><td align="center">'.$size_item.'<input type="hidden" name="sizes[]" value="'.$size_item.'" /></td><td><input size="11" value="'.$price_item.'" class="inputBox" type="text" name="opt_prices[]" /></td></tr>';
              	}
              	              	
              }
              
              ?>        
              
              <tr style="height: 24px;">
                <td align="left"><span class='warning'><?php echo $_lang['ec_item_dealer_price']; ?></span></td>
                <td><input name="price_opt" type="text" maxlength="11" size="11" value="<?php echo htmlspecialchars(stripslashes($content['price_opt']));?>" class="inputBox" style="" onchange="" spellcheck="true" />&nbsp; <a href="javascript:;" onclick="showTablePrices('opt');">установить в зависимости от размера</a></td>
              </tr>
              <tr>
              	<td></td>
              	<td>
              		<table cellspacing="0" cellpadding="5" style="<?php if(!$prices){echo 'display:none;';}?>" border="1" id="setprice-table_opt">
              			<tr>
              				<th width="80">Размер</th>
              				<th width="100">Цена</th>
              			</tr>
              			<?php echo $opt_pricetabletr; ?>
              		</table>
              	</td>
              </tr>
              
          
              
               <tr style="height: 24px;">
                <td align="left"><span class='warning'><?php echo $_lang['ec_item_sku']; ?></span></td>
                <td><input name="sku" type="text" maxlength="11" size="11" value="<?php echo htmlspecialchars(stripslashes($content['sku']));?>" class="inputBox" style="" onchange="" spellcheck="true" /></td>
              </tr>
               <tr style="height: 10px;">
                <td align="left" colspan="2" height="10">
                <div class="stay"></div>
                </td>
              </tr>
              
              <tr style="height: 24px;">
                <td><span class='warning'>Количество единиц в упаковке</span></td>
                <td>
                	<input name="package_items" type="text" maxlength="11" size="11" value="<?php echo htmlspecialchars(stripslashes($content['package_items']));?>" class="inputBox" style="" onchange="" spellcheck="true" />
               </td>
              </tr>
              
              <?php
              ##
				$sizes_str = $content['size'];
				$sizes = explode(",", $sizes_str);
				
              if($content['id']){
              	$prices = getPrices($content['id'], 'package');//print_r($prices);die();
              	
              	$package_pricetabletr = "";
              	$package_price = $content['package_price'];
              	foreach($sizes as $size_item){
              		//$size_item = trim($size_item);
              		$price_item = $package_price;
              		if( $prices[$size_item] > 0){
              			$price_item = $prices[$size_item];
              		}
              		$package_pricetabletr .= '<tr><td align="center">'.$size_item.'<input type="hidden" name="sizes[]" value="'.$size_item.'" /></td><td><input size="11" value="'.$price_item.'" class="inputBox" type="text" name="package_prices[]" /></td></tr>';
              	}
              }
              
              ?> 
                           
              <tr style="height: 24px;">
                <td><span class='warning'>Цена за упаковку</span></td>
                <td>
                	<input name="package_price" type="text" maxlength="11" size="11" value="<?php echo htmlspecialchars(stripslashes($content['package_price']));?>" class="inputBox" style="" onchange="" spellcheck="true" />
                	&nbsp; <a href="javascript:;" onclick="showTablePrices('package');">установить в зависимости от размера</a>
               </td>
              </tr> 
              <tr>
              	<td></td>
              	<td>
              		<table cellspacing="0" cellpadding="5" style="<?php if(!$prices){echo 'display:none;';}?>" border="1" id="setprice-table_package">
              			<tr>
              				<th width="80">Размер</th>
              				<th width="100">Цена</th>
              			</tr>
              			<?php echo $package_pricetabletr; ?>
              		</table>
              	</td>
              </tr>                           
                            
               <tr style="height: 10px;">
                <td align="left" colspan="2" height="10">
                <div class="stay"></div>
                </td>
              </tr>              
              
              <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_item_sell']; ?></span></td>
                <td>
                <input type="hidden" name="ec_item_sell" value="0">
                <input name="sell" type="checkbox" value="1" class="checkbox" <?php echo (isset($content['sell']) && $content['sell']==1) || (!isset($content['sell']) && $def_sell==1) ? "checked" : "" ;?> />
               </td>
              </tr>	  
              
              <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_item_new']; ?></span></td>
                <td>
                <input type="hidden" name="ec_item_new" value="0">
                <input name="new" type="checkbox" value="1" class="checkbox" <?php echo (isset($content['new']) && $content['new']==1) || (!isset($content['new']) && $def_new==1) ? "checked" : "" ;?> />
                
               
              </tr>	
              
              <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_item_soon']; ?></span></td>
                <td>
                 <input type="hidden" name="ec_item_soon" value="0">
                <input name="soon" type="checkbox" value="1" class="checkbox" <?php echo (isset($content['soon']) && $content['soon']==1) || (!isset($content['soon']) && $def_soon==1) ? "checked" : "" ;?> />
                
               
              </tr>	
              
              <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_item_popular']; ?></span></td>
                <td> <input type="hidden" name="ec_item_popular" value="0">
                <input name="popular" type="checkbox" value="1" class="checkbox" <?php echo (isset($content['popular']) && $content['popular']==1) || (!isset($content['popular']) && $def_popular==1) ? "checked" : "" ;?> /></td>
              </tr>	 
               
             <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_item_recommended']; ?></span></td>
                <td><input type="hidden" name="ec_item_recommended" value="0">
                <input name="recommended" type="checkbox" value="1" class="checkbox" <?php echo (isset($content['recommended']) && $content['recommended']==1) || (!isset($content['recommended']) && $def_recommended==1) ? "checked" : "" ;?> /></td>
              </tr>	   
             
             <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_item_byorder']; ?></span></td>
                <td><input type="hidden" name="ec_item_byorder" value="0">
                <input name="byorder" type="checkbox" value="1" class="checkbox" <?php echo (isset($content['byorder']) && $content['byorder']==1) || (!isset($content['byorder']) && $def_byorder==1) ? "checked" : "" ;?> /></td>
              </tr>	 
              
 <tr style="height: 24px;">
                <td><span class='warning'>Акция</span></td>
                <td><input type="hidden" name="ec_item_action" value="0">
                <input name="action" type="checkbox" value="1" class="checkbox" <?php echo (isset($content['action']) && $content['action']==1) || (!isset($content['action']) && $def_action==1) ? "checked" : "" ;?> /></td>
              </tr>	     
                   
               
              
            <?php if($modx->hasPermission('ec_publish_item')): // Publish permission set?>
              <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['document_opt_published']; ?></span></td>
                <td><input type="hidden" name="published"  value="0">
                <input name="published" type="checkbox" value="1"  class="checkbox" <?php echo (isset($content['published']) && $content['published']==1) || (!isset($content['published']) && $def_publish==1) ? "checked" : "" ;?> /></td>
              </tr>     
             
                                                 
              <?php endif; // End publish?>
             
               <tr>
                <td colspan="2" height="20"><div class="stay"></div></td>
              </tr>
             
              <tr style="height: 24px;">
                <td align="left" style="width:100px;"><span class='warning'><?php echo $_lang['ec_item_menuindex']; ?></span></td>
                <td>
                <table border="0" cellspacing="0" cellpadding="0" style="width:325px;"><tr>
                <td><input name="menuindex" type="text" maxlength="3" value="<?php echo $content['menuindex'];?>" class="inputBox" style="width:30px;" onchange="" /><input type="button" class="button" value="&lt;" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();" /><input type="button" class="button" value="&gt;" onclick="var elm = document.mutate.menuindex;var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();" />&nbsp;&nbsp;<img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif" onmouseover="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02.gif';" onmouseout="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif';" alt="<?php echo $_lang['document_opt_menu_index_help']; ?>" onclick="alert(this.alt);" style="cursor:help;" /></td>
                
                </table>
                </td>
              </tr>
              
              <tr style="height: 24px;">
                <td><span class='warning'><?php echo $_lang['ec_item_template']; ?></span></td>
                <td>
            <?php
				$sql = "select templatename, id from $dbase.`".$table_prefix."site_templates` ORDER BY templatename ASC";
                $rs = mysql_query($sql);
            	?>
	            <select id="template" name="template" class="inputBox" onchange='templateWarning();' style="width:300px">
	            	<option value="0">(blank)</option>
	            <?php
	
	            while ($row = mysql_fetch_assoc($rs)) {
				    if (isset ($_REQUEST['newtemplate'])) {
				        $selectedtext = $row['id'] == $_REQUEST['newtemplate'] ? "selected='selected'" : "";
				    } else
			        if (isset ($content['template'])) {
			        	
			            $selectedtext = $row['id'] == $content['template'] ? "selected='selected'" : "";
			                } else {
			            $selectedtext = $row['id'] == $def_model ? "selected='selected'" : "";
	                }
		            ?>
	                <option value="<?php echo $row['id']; ?>" <?php echo $selectedtext; ?>><?php echo $row['templatename']; ?></option>
	            <?php
	
	            }
	            ?>
                </select>
                &nbsp;<img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif" onmouseover="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02.gif';" onmouseout="this.src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/b02_trans.gif';" alt="<?php echo $_lang['page_data_template_help']; ?>" onclick="alert(this.alt);" style="cursor:help;" />
                </td>
              </tr>           
             
              <tr style="height: 24px;">
                <td valign="top"><span class='warning'><?php echo $_lang['ec_item_parent']; ?></span></td>
                <td valign="top"><?php

	if (isset ($_REQUEST['id'])) {
	    if ($content['parent'] == 0) {
	        $parentname = $site_name;
	    } else {
	        $sql = "SELECT pagetitle FROM $dbase.`" . $table_prefix . "site_content` WHERE $dbase.`" . $table_prefix . "site_content`.id = " . $content['parent'] . ";";
	        $rs = mysql_query($sql);
	        $limit = mysql_num_rows($rs);
	    	if ($limit != 1) {
	            $e->setError(8);
	            $e->dumpError();
	        }
	        $parentrs = mysql_fetch_assoc($rs);
	        $parentname = $parentrs['pagetitle'];
	    }
	} elseif (isset ($_REQUEST['pid'])) {
        if ($_REQUEST['pid'] == 0) {
            $parentname = $site_name;
        } else {
            $sql = "SELECT pagetitle FROM $dbase.`" . $table_prefix . "site_content` WHERE $dbase.`" . $table_prefix . "site_content`.id = " . $_REQUEST['pid'] . ";";
            $rs = mysql_query($sql);
            $limit = mysql_num_rows($rs);
          	if ($limit != 1) {
                $e->setError(8);
                $e->dumpError();
            }
            $parentrs = mysql_fetch_assoc($rs);
            $parentname = $parentrs['pagetitle'];
        }
    } else {
        $parentname = $site_name;
       	$content['parent'] = 0;
    }
   
            ?>&nbsp;<img name="plock" src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folder.gif" width="18" height="18" onclick="enableParentSelection(!allowParentSelection);" style="cursor:pointer;" /><b><span id="parentName"><?php echo isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $content['parent']; ?> (<?php echo $parentname; ?>)</span></b><br />
            <span class="comment" style="width:300px;display:block;"><?php echo $_lang['document_parent_help'];?></span>
            <input type="hidden" name="parent" onchange="documentDirty=true;" value="<?php echo isset($_REQUEST['pid']) ? $_REQUEST['pid'] : $content['parent']; ?>" onchange="" />
                </td>
              </tr>
               <tr>
                <td colspan="2" height="20"><div class="stay"></div></td>
              </tr>
            </table>
            
            
        
            <h2 class="tab"><?php  echo $_lang["settings_item_templvars"] ?></h2>
           
            <?php
			
			    $template = $def_model;    
			    if (isset ($_REQUEST['newtemplate'])) {
			        $template = $_REQUEST['newtemplate'];
			        $content['template'] = $template;
			    } else {
			        if (isset ($content['template'])) {
			            $template = $content['template'];
			        } else {
			        	$content['template'] = $template;
			        }
			    }
			    
			    if (isset($content['parent'])) $parent = intval($content['parent']);
			    elseif (isset($_REQUEST['pid'])) $parent = intval($_REQUEST['pid']);
			    else  $parent = 0;
			    
			    $sql = "SELECT DISTINCT tv.*, IF(tvc.value!='',tvc.value,tv.default_text) as value ";
			    $sql .= "FROM $dbase.`" . $table_prefix . "site_tmplvars` tv ";
			    $sql .= "INNER JOIN $dbase.`" . $table_prefix . "site_tmplvar_templates` tvtpl ON tvtpl.tmplvarid = tv.id ";
			    $sql .= "LEFT JOIN $dbase.`" . $table_prefix . "site_tmplvar_ec_itemvalues` tvc ON tvc.tmplvarid=tv.id AND tvc.itemid = $id ";
			    $sql .= "LEFT JOIN $dbase.`" . $table_prefix . "site_tmplvar_access` tva ON tva.tmplvarid=tv.id  ";
			    $sql .= "WHERE tvtpl.templateid = " . $template . " AND (1='" . $_SESSION['mgrRole'] . "' OR ISNULL(tva.documentgroup)" . ((!$docgrp) ? "" : " OR tva.documentgroup IN ($docgrp)") . ") ORDER BY tvtpl.rank,tv.rank;";
			        $rs = mysql_query($sql);
			        $limit = mysql_num_rows($rs);
			    if ($limit > 0) {
			        echo "<table border='0' cellspacing='0' cellpadding='3' width='96%'>";
			        require ('tmplvars.inc.php');
			        require ('tmplvars.commands.inc.php');
			        for ($i = 0; $i < $limit; $i++) {
			                // go through and display all the document variables
			                $row = mysql_fetch_assoc($rs);
			            if ($row['type'] == 'richtext' || $row['type'] == 'htmlarea') { // htmlarea for backward compatibility
			                    if (is_array($replace_richtexteditor))
			                    $replace_richtexteditor = array_merge($replace_richtexteditor, array (
			                        "tv" . $row['name']
			                    ));
			                    else
			                    $replace_richtexteditor = array (
			                        "tv" . $row['name']
			                    );
			                }
			            // splitter
			            if ($i > 0 && $i < $limit)
			                echo '<tr><td colspan="2"><div class="split"></div></td></tr>';
			        ?>
			              <tr style="height: 24px;">
			                <td align="left" valign="top" width="150">
			                    <span class='warning'><?php echo $row['caption']; ?></span><br /><span class='comment'><?php echo $row['description']; ?></span>
			                </td>
			                <td valign="top" >
			                <?php
						
			            $tvPBV = $_POST['tv' . $row['name']]; // post back value
			            echo renderFormElement($row['type'], $row['name'], $row['default_text'], $row['elements'], ($tvPBV ? $tvPBV : $row['value']), ' style="width:300px;"');
			        ?>
			                </td>
			              </tr>
			        <?php
			
			        } //loop through all template variables
			        ?>
			        </table>
			        <?php
			
			    } else {
			            echo $_lang['tmplvars_novars'];
			    } //end check to see if there are template variables to display
			    
			?>
		 </div>
		 
		 <div class="tab-page" id="tabSimilarItems">
            <h2 class="tab">Рекомендуемые товары</h2>
            <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabSimilarItems" ) );</script>            
            <img name="plock1" src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folder.gif" width="18" height="18" onclick="enableParentSelectionForItemSelector(!allowParentSelectionForItemSelector);" style="cursor:pointer;" /><b><span id="similarParentName"><?php echo $_lang['ec_similar_folder_sel'];?></span></b><br />
            <span class="comment""><?php echo $_lang['document_parent_help'];?></span>
            <div id="itemselectorloading"></div>
            <script>
            function addItemTo(elem) {
            	if (elem.length == 0) return;
            	if (elem.selectedInde == -1) return;      
            	var opt = elem.options[elem.selectedIndex];
            	var selitems = $('selecteditems');
            	var add = true;            	
            	if (selitems.options[0].value == '0') selitems.options[0] = null;
            	for (var i = 0;i<selitems.length;i++) {            		
            		if (selitems[i].value == opt.value && opt.value == '0') add = false;            			
            	}            	
            	if (add) {
            		selitems.options[selitems.length] = new Option(opt.text,opt.value);  
            	}
            	prepareListData();         		
            }
            
            function removeItem(elem) {
            	if (elem.length == 0) return;
            	if (elem.selectedInde == -1) return;            	
            	elem.options[elem.selectedIndex] = null;
            	prepareListData();
            }
            
            function prepareListData() {
            	var selitems = $('selecteditems');
            	var similaritemsArr = new Array();
            	var similaritems = '';
            	for (var i = 0;i<selitems.length;i++) { 
            		similaritemsArr[i] = selitems.options[i].value;   	
            	}             
            	similaritems = similaritemsArr.join(',');
            	/*if (similaritems != '') */$('similaritems').value = similaritems;            	 	
            }
            </script>
            <input name="recommenditems" id="similaritems" type="hidden" size="1000">
            <table>
            	<tr>
            		<td valign="top">     
            		<strong><?php  echo $_lang["ec_list_for_similars"] ?></strong><br>  
            		<div id="itemselectorcontainer">
            		<select id="itemselectorlist" ondblclick="removeItem(this)" multiple style="height:250px;width:400px;">
            		<option value="0"><?php  echo $_lang['no_items'] ?></option>
            		</select>
            		</div>           		
            		</td>
            		<td valign="top">
            		<strong>Рекомендуемые товары</strong>
            		<br>        		
            		<select id="selecteditems" ondblclick="removeItem(this)" multiple style="height:250px;width:400px;">            		
            		<?php
            		$output = '';
					if (isset($content['recommenditems']) && !empty($content['recommenditems'])) {						
						$sql  =  "SELECT * FROM ".$modx->getFullTableName("site_ec_items")." ";
						$sql .=  "WHERE id IN ($content[recommenditems]) ORDER BY menuindex,pagetitle ";
						$rs = mysql_query($sql);
						$result_size = mysql_num_rows($rs);
						if ($result_size > 0) {
							while ($row = mysql_fetch_assoc($rs)) {
								$output .='<option value="'.$row['id'].'">'.$row['pagetitle'].'</option>';
							}
							echo $output;				
						} else {
							echo $output .='<option value="0">'.$_lang['no_items'].'</option>';
						}
					} else echo $output .='<option value="0">'.$_lang['no_items'].'</option>';
            		?>            		
            		</select>            		        		
            		<script>prepareListData()</script>
            		</td>
            	</tr>   
            	<tr>
            		<td><input type="button" onclick="addItemTo($('itemselectorlist'))" value="<?php  echo $_lang["add"] ?>"></td>
            		<td><input type="button" onclick="removeItem($('selecteditems'))" value="<?php  echo $_lang["remove"] ?>"></td>
            	</tr>         	
            </table>
            
            
            
        </div>
		
		 
		 <input type="submit" name="save" style="display:none" />		 
		 </form>	
		 	 
		 <!--Meta  tags-->
		 <?php if ($_REQUEST['a'] == 5002) {?>
		 <div class="tab-page" id="tabMetatags">
            <h2 class="tab"><?php echo $_lang['metatags'] ?></h2>
            <script type="text/javascript">
            	
				// meta tag rows
				var tagRows = []; // stores tag information in 2D array. 2nd array = 0-name,1-tag,2-value,3-http_equiv			
				function checkForm() {
					var requireConfirm=false;
					var deleteList="";
				<?php for($i=0;$i<$limit;$i++) {
					$row=mysql_fetch_assoc($rs);
					?>
					if(document.getElementById('delete<?php echo $row['id']; ?>').checked==true) {
						requireConfirm = true;
						deleteList = deleteList + "\n - <?php echo addslashes($row['keyword']); ?>";
			
					}
				<?php }	?>
					if(requireConfirm) {
						var agree=confirm("<?php echo $_lang['confirm_delete_keywords']; ?>\n" + deleteList);
						if(agree) {
							return true;
						} else {
							return false;
						}
					}
					return true;
				}
			
				function addTag() {
					var f=document.metatag;
					if(!f) return;
					if(!f.tagname.value) alert("<?php echo $_lang["require_tagname"];?>");
					else if(!f.tagvalue.value) alert("<?php echo $_lang["require_tagvalue"];?>");
					else {
						f.op.value=(f.cmdsavetag.value=="<?php echo $_lang["save_tag"];?>") ? 'edttag':'addtag';
						f.submit();
					}
				}
			
				function editTag(id){
					var opt;
					var f=document.metatag;
					if(!f) return;
					f.tagname.value = tagRows[id][0];
					f.tagvalue.value= tagRows[id][2];
					for(i=0;i<f.tag.options.length;i++) {
						opt = f.tag.options[i];
						tagkey = tagRows[id][1]+";"+tagRows[id][3]; // combine tag and style to make key
						if(opt.value==tagkey){
							opt.selected = true;
							break;
						}
					}
					f.id.value=id;
					f.cmdsavetag.value='<?php echo $_lang["save_tag"];?>';
					f.cmdcanceltag.style.visibility = 'visible';
					f.tagname.focus();
				}
			
				function cancelTag(id){
					var opt;
					var f=document.metatag;
					if(!f) return;
					f.tagname.value = '';
					f.tagvalue.value= '';
					f.tag.options[0].selected = true;
					f.id.value='';
					f.cmdsavetag.value='<?php echo $_lang["add_tag"];?>';
					f.cmdcanceltag.style.visibility = 'hidden';
				}
			
				function deleteTag() {
					var f=document.metatag;
					if(!f) return;
					else if(confirm("<?php echo $_lang['confirm_delete_tags']; ?>")) {
						f.op.value='deltag';
						f.submit();
					}
				}
			
				</script>
			
			<form name="metatag" method="post" action="index.php" onsubmit="return checkForm();">
			<input type="hidden" name="a" value="5006" />
			<input type="hidden" name="item_id" value="<?php echo $content["id"]?>" />
			<input type="hidden" name="op" value="" />
			<input type="hidden" name="id" value="" />
			<br />
			<!-- META tags -->
				<?php echo $_lang['metatag_intro'] ;?><br /><br />
				<div class="searchbara">
				<table border="0" width="100%" cellspacing="1">
				  <tr>
					<td width="70%">
					<table border="0" cellspacing="1">
					  <tr>
						<td valign="top"><?php echo $_lang['name'];?><br>
						<input type="text" name="tagname" size="31">
						<br>
						<?php echo $_lang['tag'];?><br>
						<select size="1" name="tag">
			        		<optgroup label="Named Meta Content">
			        			<option value="abstract;0">abstract</option>
			        			<option value="author;0">author</option>
			        			<option value="classification;0">classification</option>
			        			<option value="copyright;0">copyright</option>
			        			<option value="description;0">description</option>
			        			<option value="designer;0">designer</option>
			        			<option value="distribution;0">distribution</option>
			        			<option value="expires;1">expires</option>
			        			<option value="generator;0">generator</option>
			        			<option value="googlebot;0">googlebot</option>
			        			<option value="keywords;0">keywords</option>
			        			<option value="MSSmartTagsPreventParsing;0">MSSmartTagsPreventParsing</option>
			        			<option value="owner;0">owner</option>
			        			<option value="rating;0">rating</option>
			        			<option value="refresh;0">refresh</option>
			        			<option value="reply-to;0">reply-to</option>
			        			<option value="revisit-after;0">revisit-after</option>
			        			<option value="robots;0">robots</option>
			        			<option value="subject;0">subject</option>
			        			<option value="title;0">title</option>
			                </optgroup>
						    <optgroup label="HTTP-Header Equivalents">
			        			<option value="content-language;1">content-language</option>
			        			<option value="content-type;1">content-type</option>
			        			<option value="expires;1">expires</option>
			        			<option value="imagetoolbar;1">imagetoolbar</option>
			        			<option value="pics-label;1">pics-label</option>
			        			<option value="pragma;1">pragma</option>
			        			<option value="refresh;1">refresh</option>
			        			<option value="set-cookie;1">set-cookie</option>
			        		</optgroup>
						</select></td>
						<td valign="top" ><?php echo $_lang['value'];?><br>
						<textarea name="tagvalue" cols="30" rows="5"></textarea></td>
						<td nowrap="nowrap"><br>
						<input type="button" value="<?php echo $_lang["add_tag"];?>" name="cmdsavetag" onclick="addTag()" /> <input style="visibility:hidden" type="button" value="<?php echo $_lang["cancel"];?>" name="cmdcanceltag" onclick="cancelTag()" /></td>
					  </tr>
					  <tr>
					      <td colspan="4"><p><?php echo $_lang['metatag_notice'];?></p></td>
				      </tr>
					</table>
					</td>
				  </tr>
				</table>
				</div>
				<div>
				<?php
			
					$sql = "SELECT * " .
						   "FROM ".$modx->getFullTableName("site_ec_item_metatags")." st ".
						   "WHERE item_id = {$content['id']} ORDER BY name";
						  
					$ds = mysql_query($sql);
					include_once $base_path."manager/includes/controls/datagrid.class.php";
					$grd = new DataGrid('',$ds,$number_of_results); // set page size to 0 t show all items
					$grd->noRecordMsg = $_lang["no_records_found"];
					$grd->cssClass="grid";
					$grd->useJSFilter=true;
					$grd->columnHeaderClass="gridHeader";
					$grd->itemClass="gridItem";
					$grd->altItemClass="gridAltItem";
					$grd->fields="id,name,tag,tagvalue";
					$grd->columns=$_lang["delete"]." ,".$_lang["name"]." ,".$_lang["tag"]." ,".$_lang["value"];
					$grd->colWidths="40";
					$grd->colAligns="center";
					$grd->colTypes="template:<input name='tag[]' type='checkbox' value='[+id+]'/><img src='media/images/icons/comment.gif' width='16' height='16' align='absmiddle' /></a>||".
								   "template:<a href='#' title='".$_lang["click_to_edit_title"]."' onclick='editTag([+id+])'>[+value+]</a><span style='display:none;'><script type=\"text/javascript\"> tagRows['[+id+]']=[\"[+js.name+]\",\"[+tag+]\",\"[+js.tagvalue+]\",\"[+http_equiv+]\"];</script>";
					echo $grd->render();
				?>
				</div>
				<table border=0 cellpadding=2 cellspacing=0>
					<tr><td colspan="5">&nbsp;</td></tr>
					<tr>
						<td align="right">
							<input type="button" name="cmddeltag" value="<?php echo $_lang["delete_tags"];?>" onclick="deleteTag();" />
						</td>
					</tr>
				</table>
			  <!--meta-->
		 </div>
		 <?php } ?>
    </div>
</div>
</div>


<script type="text/javascript">
    var cal3 = new calendar1(document.forms['mutate'].elements['date_issue'], document.getElementById("ec_item_date_issue_show"));
    cal3.path="<?php echo str_replace("index.php", "media/", $_SERVER["PHP_SELF"]); ?>";

    cal3.year_scroll = true;
    cal3.time_comp = true;    
</script>


<?php
			/**
			 *  Initialize RichText 
			 *  orig MODIFIED BY S.BRENNAN for DocVars
			 */
			  //if ($use_editor == 1) {    	 
			        if (is_array($replace_richtexteditor)) {
			            // invoke OnRichTextEditorInit event
			            $evtOut = $modx->invokeEvent("OnRichTextEditorInit", array (
			                'editor' => $which_editor,
			                'elements' => $replace_richtexteditor
			                                            ));
			            if (is_array($evtOut))
			                echo implode("", $evtOut);
			        }
			// }			
		 ?>
