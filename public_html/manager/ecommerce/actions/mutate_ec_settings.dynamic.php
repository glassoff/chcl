<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('settings')) {
	$e->setError(3);
	$e->dumpError();
}

// check to see the edit settings page isn't locked
$sql = "SELECT internalKey, username FROM $dbase.`".$table_prefix."active_users` WHERE $dbase.`".$table_prefix."active_users`.action=5200";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);
if($limit>1) {
	for ($i=0;$i<$limit;$i++) {
		$lock = mysql_fetch_assoc($rs);
		if($lock['internalKey']!=$modx->getLoginUserID()) {
			$msg = sprintf($_lang["lock_settings_msg"],$lock['username']);
			$e->setError(5, $msg);
			$e->dumpError();
		}
	}
}
// end check for lock

// reload system settings from the database.
// this will prevent user-defined settings from being saved as system setting
$settings = array();
$sql = "SELECT setting_name, setting_value FROM $dbase.`".$table_prefix."ec_settings`";
$rs = mysql_query($sql);
$number_of_settings = mysql_num_rows($rs);
while ($row = mysql_fetch_assoc($rs)) $settings[$row['setting_name']] = $row['setting_value'];
extract($settings, EXTR_OVERWRITE);

$displayStyle = ( ($_SESSION['browser']=='mz') || ($_SESSION['browser']=='op') ) ? "table-row" : "block" ;

?>

<script type="text/javascript">
function checkIM() {
	im_on = document.settings.im_plugin[0].checked; // check if im_plugin is on
	if(im_on==true) {
		showHide(/imRow/, 1);
	}
};

function checkCustomIcons() {
	if(document.settings.editor_toolbar.selectedIndex!=3) {
		showHide(/custom/,0);
	}
};

function showHide(what, onoff){

	var all = document.getElementsByTagName( "*" );
	var l = all.length;
	var buttonRe = what;
	var id, el, stylevar;

	if(onoff==1) {
		stylevar = "<?php echo $displayStyle; ?>";
	} else {
		stylevar = "none";
	}

	for ( var i = 0; i < l; i++ ) {
		el = all[i]
		id = el.id;
		if ( id == "" ) continue;
		if (buttonRe.test(id)) {
			el.style.display = stylevar;
		}
	}
};

function addOption(list,ct,txt){
	var i,o,exists=false;
	var txt = document.settings.elements[txt];
	var lst = document.settings.elements[list];
	for(i=0;i<lst.options.length;i++)
	{
		if(lst.options[i].value==txt.value) {
			exists=true;
			break;
		}
	}
	if (!exists) {
		o = new Option(txt.value,txt.value);
		lst.options[lst.options.length]= o;
		updateOptions(list,ct);
	}
	txt.value='';
}
function removeOption(list,ct){
	var i;
	var lst = document.settings.elements[list];
	for(i=0;i<lst.options.length;i++) {
		if(lst.options[i].selected) {
			lst.remove(i);
			break;
		}
	}
	updateOptions(list,ct);
}
function updateOptions(list,ct){
	var i,o,ol=[];
	var ct = document.settings.elements[ct];
	var lst = document.settings.elements[list];
	while(lst.options.length) {
		ol[ol.length] = lst.options[0].value;
		lst.options[0]= null;
	}
	if(ol.sort) ol.sort();
	ct.value = ol.join(",");
	for(i=0;i<ol.length;i++) {
		o = new Option(ol[i],ol[i]);
		lst.options[lst.options.length]= o;
	}
	documentDirty = true;
}

</script>
<div class="subTitle">
	<span class="right"><?php echo $_lang['ec_settings_title']; ?></span>

	<table cellpadding="0" cellspacing="0" class="actionButtons">
		<tr>
			<td id="Button1"><a href="#" onclick="documentDirty=false;document.settings.submit();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" /> <?php echo $_lang['save']; ?></a></td>
			<td id="Button5"><a href="#" onclick="documentDirty=false;document.location.href='index.php?a=2';"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" /> <?php echo $_lang['cancel']; ?></a></td>
		</tr>
	</table>
</div>

<!--
<form name="email" action="index.php?a=5222" method="post" />

<div style="margin: 0 10px 0 20px">
<b>Отправить письма неподтвердившим заказ и не сделавшим</b>
<br> 
<input type="submit" value="Отправить">  &nbsp; &nbsp; &nbsp;



 <?php 
$timesend = date("M d Y H:i:s", $timesend_nobuy ); 
 echo $timesend;  ?>
</form><br>
-->

<!--
<br><b>Рассылка</b>
<form name="sending" action="index.php?a=5444" method="post">
Тестовый режим - <input type="checkbox" value="1" name="test" checked>

 <select name="color" class="inputBox" onchange='documentDirty=true;' style="width:200px">
			
					<option value="1" >Бордовый</option>
					<option value="2" >Синий</option>
					<option value="3" >Желтый</option>
					<option value="4" >Оранжевый</option>
					<option value="5" >Зеленый</option>
					<option value="6" >Красный</option>
				
				
 			 </select>
  <select name="sending" class="inputBox" onchange='documentDirty=true;' style="width:200px">
			
					<option value="1" >Компьютерные игры</option>
					<option value="2" >ДВД-фильмы</option>
					<option value="3" >Приставочные игры</option>
				
				
 			 </select>&nbsp;&nbsp;&nbsp;
 			 <input type="submit" value="Отправить">


</form>
<br>
            <br><b>Отправить поздравление с праздником</b><br> 
			 <form name="pozdr" action="index.php?a=5333" method="post" >

			  <select name="pozdr" class="inputBox" onchange='documentDirty=true;' style="width:150px">
			
					<option value="1" >Новый год</option>
					<option value="2" >23 февраля</option>
					<option value="3" >8 марта</option>
					<option value="4" >12 апреля</option>
					<option value="5" >9 мая</option>
					<option value="6" >1 сентября</option>
					<option value="7" >7 ноября</option>
				
 			 </select>&nbsp;&nbsp;&nbsp;
 			 <input type="submit" value="Отправить">
			 	</form>
</div>
<BR> -->

<form name="settings" action="index.php?a=5201" method="post" />
<div style="margin: 0 10px 0 20px">
    <input type="hidden" name="settings_version" value="<?php echo $version; ?>" />
    <!-- this field is used to check site settings have been entered/ updated after install or upgrade -->
    <?php if(!isset($settings_version) || $settings_version!=$version) { ?>
    <div class='sectionBody'><?php echo $_lang['settings_after_install']; ?></div>
    <?php } ?>
    <script type="text/javascript" src="media/script/tabpane.js"></script>
    <div class="tab-pane" id="settingsPane">
      <script type="text/javascript">
		tpSettings = new WebFXTabPane( document.getElementById( "settingsPane" ) );
	</script>

	<!-- Site Settings -->
      <div class="tab-page" id="tabPage2">
        <h2 class="tab"><?php echo $_lang["basic_hdr"] ?></h2>
        <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabPage2" ) );</script>
        <h2><?php echo $_lang["shopping_title"] ?></h2>
        <table border="0" cellspacing="0" cellpadding="3"  width="100%">
         
          <tr>
            <td colspan="2"> <h2><?php echo $_lang["ec_general_hdr"] ?></h2> </td>
          </tr>
         <!--
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_online_payment"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_online_payment" value="<?php echo isset($delivery_online_payment) ? $delivery_online_payment : '' ; ?>" /></td>
          </tr>
          
           <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
         
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_1class_online_price"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_1class_online_price" value="<?php echo isset($delivery_1class_online_price) ? $delivery_1class_online_price : '' ; ?>" /></td>
          </tr>
          
           <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_1class_offline_price"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_1class_offline_price" value="<?php echo isset($delivery_1class_offline_price) ? $delivery_1class_offline_price : '' ; ?>" /></td>
          </tr>
          
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          -->
        
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_curer_za_mkad_price"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_curer_za_mkad_price" value="<?php echo isset($delivery_curer_za_mkad_price) ? $delivery_curer_za_mkad_price : '' ; ?>" /></td>
          </tr>
          
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
        
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_curer_v_mkad_price"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_curer_v_mkad_price" value="<?php echo isset($delivery_curer_v_mkad_price) ? $delivery_curer_v_mkad_price : '' ; ?>" /></td>
          </tr>
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_curer_v_mkad_price2"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_curer_v_mkad_price2" value="<?php echo isset($delivery_curer_v_mkad_price2) ? $delivery_curer_v_mkad_price2 : '' ; ?>" /></td>
          </tr><tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_curer_v_mkad_price3"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_curer_v_mkad_price3" value="<?php echo isset($delivery_curer_v_mkad_price3) ? $delivery_curer_v_mkad_price3 : '' ; ?>" /></td>
          </tr><tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_curer_v_mkad_price4"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_curer_v_mkad_price4" value="<?php echo isset($delivery_curer_v_mkad_price4) ? $delivery_curer_v_mkad_price4 : '' ; ?>" /></td>
          </tr>
          
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
        
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_samovivoz"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_samovivoz" value="<?php echo isset($delivery_samovivoz) ? $delivery_samovivoz : '' ; ?>" /></td>
          </tr>
          
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
         <!--  
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["delivery_vstrecha"] ?></b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_vstrecha" value="<?php echo isset($delivery_vstrecha) ? $delivery_vstrecha : '' ; ?>" /></td>
          </tr>
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
           <tr>
            <td nowrap class="warning"><b>Доставка в Ближнее Зарубежье</b></td>
            <td><input onchange="documentDirty=true;"  type='text' maxlength='10' size='5' name="delivery_zarubezh" value="<?php echo isset($delivery_zarubezh) ? $delivery_zarubezh : '' ; ?>" /></td>
          </tr>
          
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
         
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["is_dealer_price_active"] ?></b></td>
            
            
            <td> 
              <input onchange="documentDirty=true;" type="radio" name="is_dealer_price_active" value="1" <?php echo $is_dealer_price_active=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="is_dealer_price_active" value="0" <?php echo ($is_dealer_price_active=='0' || !isset($is_dealer_price_active)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> 
            </td>
            
            
            </tr>
          
          
           <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["is_mdealer_price_active"] ?></b></td>
            <td> 
              <input onchange="documentDirty=true;" type="radio" name="is_mdealer_price_active" value="1" <?php echo $is_mdealer_price_active=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="is_mdealer_price_active" value="0" <?php echo ($is_mdealer_price_active=='0' || !isset($is_mdealer_price_active)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> 
            </td>
          
           <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
          
              <tr>
            <td nowrap class="warning"><b><?php echo $_lang["new_year"] ?></b></td>
            
            
            <td> 
              <input onchange="documentDirty=true;" type="radio" name="new_year" value="1" <?php echo $new_year=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="new_year" value="0" <?php echo ($new_year=='0' || !isset($new_year)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> 
            </td>

            </tr>
          
           <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          -->
          
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["ec_notconfirmed_order_delete_days"] ?></b></td>
            <td><input onchange="documentDirty=true;" type='text' maxlength='10' size='5' name="ec_notconfirmed_order_delete_days" value="<?php echo isset($ec_notconfirmed_order_delete_days) ? $ec_notconfirmed_order_delete_days : '' ; ?>" /></td>
          </tr>
          
           <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning"><b><?php echo $_lang["ec_notpaid_order_delete_days"] ?></b></td>
            <td><input onchange="documentDirty=true;" type='text' maxlength='10' size='5' name="ec_notpaid_order_delete_days" value="<?php echo isset($ec_notpaid_order_delete_days) ? $ec_notpaid_order_delete_days : '' ; ?>" /></td>
          </tr>
          
           <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          <!--
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["ec_over_weight_message"] ?></b></td>
           <td> <textarea name="ec_over_weight_message" style="width:100%; height: 120px;"><?php echo isset($ec_over_weight_message) ? $ec_over_weight_message : "" ; ?></textarea> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_order_confirm_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
          
          <tr>
            <td nowrap class="warning" valign="top"><?php echo $_lang["ec_mediums"] ?></td>
            <td>
            
            <input name="txt_option5" type="text" maxlength="100" style="width: 200px;" value="" /> 
            <input type="button" value="<?php echo $_lang["add"]; ?>" style="width:60px" onclick='addOption("options3","ec_mediums","txt_option5")' /><br />
            <table border="0" cellspacing="0" cellpadding="0"><tr><td valign="top">
            <select name="options3" style="width:200px;" size="5">
            <?php
	            $ec_mediums = (isset($ec_mediums) ?  $ec_mediums : "");
            	$ct = explode(",", $ec_mediums);
            	for($i=0;$i<count($ct);$i++) {
            		echo "<option value=\"".$ct[$i]."\">".$ct[$i]."</option>";
            	}
            ?>
            </select>
            <input name="ec_mediums" type="hidden" value="<?php echo  $ec_mediums;?>" />
            </td>
            <td valign="top">
            &nbsp;<input name="" type="button" value="<?php echo $_lang["remove"]; ?>" style="width:60px" onclick='removeOption("options3","ec_mediums")' /></td></tr></table>
            </td>
          </tr>
          
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          -->
         
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["incomplete_order"] ?></b></td>
            <td>
            <b><?php echo $_lang["incomplete_order_wait_msg"] ?></b><br>
            <input name="txt_option" type="text" maxlength="100" style="width: 200px;" value="" /> 
            <input type="button" value="<?php echo $_lang["add"]; ?>" style="width:60px" onclick='addOption("options1","incomplete_order_wait","txt_option")' /><br />
            <table border="0" cellspacing="0" cellpadding="0"><tr><td valign="top">
            <select name="options1" style="width:200px;" size="5">
            <?php
	            $incomplete_order_wait = (isset($incomplete_order_wait) ? $incomplete_order_wait : "");
            	$ct = explode(",",$incomplete_order_wait);
            	for($i=0;$i<count($ct);$i++) {
            		echo "<option value=\"".$ct[$i]."\">".$ct[$i]."</option>";
            	}
            ?>
            </select>
            <input name="incomplete_order_wait" type="hidden" value="<?php echo $incomplete_order_wait; ?>" />
            </td>
            <td valign="top">
            &nbsp;<input name="" type="button" value="<?php echo $_lang["remove"]; ?>" style="width:60px" onclick='removeOption("options1","incomplete_order_wait")' /></td></tr></table>
            </td>
          </tr>
         
          
           <tr>
            <td nowrap class="warning" valign="top"></td>
            <td>
            <b><?php echo $_lang["incomplete_order_do_msg"] ?></b><br>
            <input name="txt_option1" type="text" maxlength="100" style="width: 200px;" value="" /> 
            <input type="button" value="<?php echo $_lang["add"]; ?>" style="width:60px" onclick='addOption("options2","incomplete_order_do","txt_option1")' /><br />
            <table border="0" cellspacing="0" cellpadding="0"><tr><td valign="top">
            <select name="options2" style="width:200px;" size="5">
            <?php
	            $incomplete_order_do = (isset($incomplete_order_do) ? $incomplete_order_do : "");
            	$ct = explode(",",$incomplete_order_do);
            	for($i=0;$i<count($ct);$i++) {
            		echo "<option value=\"".$ct[$i]."\">".$ct[$i]."</option>";
            	}
            ?>
            </select>
            <input name="incomplete_order_do" type="hidden" value="<?php echo $incomplete_order_do; ?>" />
            </td>
            <td valign="top">
            &nbsp;<input name="" type="button" value="<?php echo $_lang["remove"]; ?>" style="width:60px" onclick='removeOption("options2","incomplete_order_do")' /></td></tr></table>
            </td>
          </tr>
         
           
          <tr>
              <td colspan="2"><div class='split'>&nbsp;</div></td>
          </tr>
          
         
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["order_def_status"] ?></b></td>
            <td>
			<?php
				$sql = "select * from $dbase.`".$table_prefix."ec_order_status` ORDER BY listindex";
				$rs = mysql_query($sql);
			?>
			  <select name="order_def_status" class="inputBox" onchange='documentDirty=true;' style="width:150px">
				<?php
				while ($row = mysql_fetch_assoc($rs)) {
					$selectedtext = $row['id']==$order_def_status ? "selected='selected'" : "" ;
					if ($selectedtext) {
						$oldTmpId = $row['id'];
						$oldTmpName = $row['name'];
					}
				?>
					<option value="<?php echo $row['id']; ?>" <?php echo $selectedtext; ?>><?php echo $row['name']; ?></option>
				<?php
				}
				?>
 			 </select>
 			 	
			</td>
          </tr>        
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["order_complate_status"] ?></b></td>
            <td>
			  <?php
				$sql = "select * from $dbase.`".$table_prefix."ec_order_status` ORDER BY listindex";
				$rs = mysql_query($sql);
			  ?>
			  <select name="order_complate_status" class="inputBox" onchange='documentDirty=true;' style="width:150px">
				<?php
				while ($row = mysql_fetch_assoc($rs)) {
					$selectedtext = $row['id']==$order_complate_status ? "selected='selected'" : "" ;
					if ($selectedtext) {
						$oldTmpId = $row['id'];
						$oldTmpName = $row['name'];
					}
				?>
					<option value="<?php echo $row['id']; ?>" <?php echo $selectedtext; ?>><?php echo $row['name']; ?></option>
				<?php
				}
				?>
 			 </select>
 			 	
			</td>
          </tr>        
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["order_confirmed_status"] ?></b></td>
            <td>
			  <?php
				$sql = "select * from $dbase.`".$table_prefix."ec_order_status` ORDER BY listindex";
				$rs = mysql_query($sql);
			  ?>
			  <select name="order_confirmed_status" class="inputBox" onchange='documentDirty=true;' style="width:150px">
				<?php
				while ($row = mysql_fetch_assoc($rs)) {
					$selectedtext = $row['id']==$order_confirmed_status ? "selected='selected'" : "" ;
					if ($selectedtext) {
						$oldTmpId = $row['id'];
						$oldTmpName = $row['name'];
					}
				?>
					<option value="<?php echo $row['id']; ?>" <?php echo $selectedtext; ?>><?php echo $row['name']; ?></option>
				<?php
				}
				?>
 			 </select>
 			 	
			</td>
          </tr>        
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["order_notconfirmed_status"] ?></b></td>
            <td>
			  <?php
				$sql = "select * from $dbase.`".$table_prefix."ec_order_status` ORDER BY listindex";
				$rs = mysql_query($sql);
			  ?>
			  <select name="order_notconfirmed_status" class="inputBox" onchange='documentDirty=true;' style="width:150px">
				<?php
				while ($row = mysql_fetch_assoc($rs)) {
					$selectedtext = $row['id']==$order_notconfirmed_status ? "selected='selected'" : "" ;
					if ($selectedtext) {
						$oldTmpId = $row['id'];
						$oldTmpName = $row['name'];
					}
				?>
					<option value="<?php echo $row['id']; ?>" <?php echo $selectedtext; ?>><?php echo $row['name']; ?></option>
				<?php
				}
				?>
 			 </select>
 			 	
			</td>
          </tr>        
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
         
          <tr>
            <td colspan="2"> <h2><?php echo $_lang["ec_email_hdr"] ?></h2> </td>
          </tr>
     
           <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["webemail_signature"] ?></b></td>
            <td> <textarea name="ec_webemail_signature" style="width:100%; height: 120px;"><?php echo $ec_webemail_signature;?></textarea> </td>
          </tr>
          
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["ec_email_payment_done_subject"] ?></b></td>
            <td><input onchange="documentDirty=true;" type='text'  size='50' name="ec_email_payment_done_subject" value="<?php echo isset($ec_email_payment_done_subject) ? $ec_email_payment_done_subject : '' ; ?>" /></td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_payment_done_subject_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr> 
         
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["ec_email_payment_done_msg"] ?></b></td>
           <td> <textarea name="ec_email_payment_done_msg" style="width:100%; height: 120px;"><?php echo isset($ec_email_payment_done_msg) ? $ec_email_payment_done_msg : "" ; ?></textarea> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_payment_done_msg_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
     	  <tr>
            <td nowrap class="warning"><b><?php echo $_lang["ec_email_order_done_subject"] ?></b></td>
            <td><input onchange="documentDirty=true;" type='text'  size='50' name="ec_email_order_done_subject" value="<?php echo isset($ec_email_order_done_subject) ? $ec_email_order_done_subject : '' ; ?>" /></td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_order_done_subject_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>                  
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["ec_email_order_done_msg"] ?></b></td>
           <td> <textarea name="ec_email_order_done_mgs" style="width:100%; height: 120px;"><?php echo isset($ec_email_order_done_mgs) ? $ec_email_order_done_mgs : "" ; ?></textarea> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_order_done_mgs_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["ec_email_order_confirm_subject"] ?></b></td>
            <td><input onchange="documentDirty=true;" type='text'  size='50' name="ec_email_order_confirm_subject" value="<?php echo isset($ec_email_order_confirm_subject) ? $ec_email_order_confirm_subject : '' ; ?>" /></td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_order_confirm_subject_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["ec_email_order_confirm_msg"] ?></b></td>
           <td> <textarea name="ec_email_order_confirm_msg" style="width:100%; height: 120px;"><?php echo isset($ec_email_order_confirm_msg) ? $ec_email_order_confirm_msg : "" ; ?></textarea> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_order_confirm_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
          <tr>
            <td nowrap class="warning"><b><?php echo $_lang["ec_email_order_details_subject"] ?></b></td>
            <td><input onchange="documentDirty=true;" type='text'  size='50' name="ec_email_order_details_subject" value="<?php echo isset($ec_email_order_details_subject) ? $ec_email_order_details_subject : '' ; ?>" /></td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_order_details_subject_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["ec_email_order_details_msg"] ?></b></td>
           <td> <textarea name="ec_email_order_details_msg" style="width:100%; height: 120px;"><?php echo isset($ec_email_order_details_msg) ? $ec_email_order_details_msg : "" ; ?></textarea> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["ec_email_order_details_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
          <tr>
            <td nowrap class="warning" valign="top"><b>Реквизиты</b></td>
           <td> <textarea name="ec_email_bank_account" style="width:100%; height: 120px;"><?php echo isset($ec_email_bank_account) ? $ec_email_bank_account : "" ; ?></textarea> </td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
          
          <tr>
            <td nowrap class="warning"><b>Email администратора заказав</b></td>
            <td><input onchange="documentDirty=true;" type='text'  size='50' name="ec_order_admin_email" value="<?php echo isset($ec_order_admin_email) ? $ec_order_admin_email : '' ; ?>" /></td>
          </tr>
          
           <tr>
            <td nowrap class="warning"><b>Тема письма - оповещение о заказе</b></td>
            <td><input onchange="documentDirty=true;" type='text'  size='50' name="ec_order_email_subject" value="<?php echo isset($ec_order_email_subject) ? $ec_order_email_subject : '' ; ?>" /></td>
          </tr>
         
 
         
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          
          <tr>
            <td nowrap class="warning" valign="top"><b>Письмо - оповещение о заказе</b></td>
           <td> <textarea name="ec_order_email_text" style="width:100%; height: 120px;"><?php echo isset($ec_order_email_text) ? $ec_order_email_text : "" ; ?></textarea> </td>
          </tr>
         
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          <!--
                <tr>
            <td nowrap class="warning" valign="top"><b>Оповещение. Диск появился в продаже</b></td>
           <td> <textarea name="disc_notice" style="width:100%; height: 120px;"><?php echo isset($disc_notice) ? $disc_notice : "" ; ?></textarea> </td>
          </tr>
         
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
           
           
               <tr>
            <td nowrap class="warning" valign="top"><b>Письмо для не подтвердивших заказ</b></td>
           <td> <textarea name="no_confirm24" style="width:100%; height: 120px;"><?php echo isset($no_confirm24) ? $no_confirm24 : "" ; ?></textarea> </td>
          </tr>
         
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
              <tr>
            <td nowrap class="warning" valign="top"><b>Письмо для не сделавших заказ</b></td>
           <td> <textarea name="no_buy24" style="width:100%; height: 120px;"><?php echo isset($no_buy24) ? $no_buy24 : "" ; ?></textarea> </td>
          </tr>
          -->
         
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
                  <tr>
            <td nowrap class="warning" valign="top"><b>Email для писем пользователям</b></td>
           <td><input onchange="documentDirty=true;" type='text'  size='50' name="email_sender" value="<?php echo isset($email_sender) ? $email_sender : '' ; ?>" />
           
 </td>
          </tr>
         
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>         
        </table>
      </div>

      

      <!-- User settings -->
      <div class="tab-page" id="tabPage4">
        <h2 class="tab"><?php echo $_lang["new_item_defs_hdr"] ?></h2>
        <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabPage4" ) );</script>
        <h2><?php echo $_lang["new_item_defs_tit"] ?></h2>
        <table border="0" cellspacing="0" cellpadding="3" width="100%">
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["def_model"] ?></b></td>
            <td>
			<?php
				$sql = "select templatename, id from $dbase.`".$table_prefix."site_templates`";
				$rs = mysql_query($sql);
			?>
			  <select name="def_model" class="inputBox" onchange='documentDirty=true;' style="width:150px">
				<?php
				while ($row = mysql_fetch_assoc($rs)) {
					$selectedtext = $row['id']==$def_model ? "selected='selected'" : "" ;
					if ($selectedtext) {
						$oldTmpId = $row['id'];
						$oldTmpName = $row['templatename'];
					}
				?>
					<option value="<?php echo $row['id']; ?>" <?php echo $selectedtext; ?>><?php echo $row['templatename']; ?></option>
				<?php
				}
				?>
 			 </select>
 			 	
			</td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["def_model_message"] ?></td>
          </tr>
          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["def_publish"] ?></b></td>
            <td> <input onchange="documentDirty=true;" type="radio" name="def_publish" value="1" <?php echo $def_publish=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="def_publish" value="0" <?php echo ($def_publish=='0' || !isset($def_publish)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["def_publish_message"] ?></td>
          </tr>

          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["def_new"] ?></b></td>
            <td> <input onchange="documentDirty=true;" type="radio" name="def_new" value="1" <?php echo $def_new=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="def_new" value="0" <?php echo ($def_new=='0' || !isset($def_new)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["def_new_message"] ?></td>
          </tr>

          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["def_sell"] ?></b></td>
            <td> <input onchange="documentDirty=true;" type="radio" name="def_sell" value="1" <?php echo $def_sell=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="def_sell" value="0" <?php echo ($def_sell=='0' || !isset($def_sell)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["def_sell_message"] ?></td>
          </tr>

          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["def_soon"] ?></b></td>
            <td> <input onchange="documentDirty=true;" type="radio" name="def_soon" value="1" <?php echo $def_soon=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="def_soon" value="0" <?php echo ($def_soon=='0' || !isset($def_soon)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["def_soon_message"] ?></td>
          </tr>

          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
         
          <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["def_popular"] ?></b></td>
            <td> <input onchange="documentDirty=true;" type="radio" name="def_popular" value="1" <?php echo $def_popular=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="def_popular" value="0" <?php echo ($def_popular=='0' || !isset($def_popular)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["def_popular_message"] ?></td>
          </tr>

          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["def_recommended"] ?></b></td>
            <td> <input onchange="documentDirty=true;" type="radio" name="def_recommended" value="1" <?php echo $def_recommended=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="def_recommended" value="0" <?php echo ($def_recommended=='0' || !isset($def_recommended)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["def_recommended_message"] ?></td>
          </tr>

          <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
           <tr>
            <td nowrap class="warning" valign="top"><b><?php echo $_lang["def_byorder"] ?></b></td>
            <td> <input onchange="documentDirty=true;" type="radio" name="def_byorder" value="1" <?php echo $def_byorder=='1' ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["yes"]?><br />
              <input onchange="documentDirty=true;" type="radio" name="def_byorder" value="0" <?php echo ($def_byorder=='0' || !isset($def_byorder)) ? 'checked="checked"' : "" ; ?> />
              <?php echo $_lang["no"]?> </td>
          </tr>
          <tr>
            <td width="200">&nbsp;</td>
            <td class='comment'><?php echo $_lang["def_byorder_message"] ?></td>
          </tr>

          <tr>
            <td colspan="2"><div class='split'></div></td> 
          </tr>
          
        </table>
      </div>   
      
      <!-- Discounts -->
      <div class="tab-page" id="tabPage4">
        <h2 class="tab"><?php echo $_lang["role_view_ec_manage_discounts"] ?></h2>
        <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabPage4" ) );</script>
        <h2><?php echo $_lang["new_item_defs_tit"] ?></h2>  
      </div>    
      
 <!--     
      <div class="tab-page" id="tabPage4"> <h2 class="tab">Поздравления с праздниками</h2>
      <table border="0" cellspacing="0" cellpadding="3" width="100%"><tr><td></td></tr>



       

  <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>

 <tr>
            <td><b>Новый год</b></td>
 <td> <textarea name="newyear" style="width:100%; height: 100px;"><?php echo isset($newyear) ? $newyear: "" ; ?></textarea> </td>
          </tr>
            <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          <tr>
            <td><b>23 февраля</b></td>
       <td> <textarea name="feb23" style="width:100%; height: 100px;"><?php echo isset($feb23) ? $feb23 : "" ; ?></textarea> </td>
   </tr>
            <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
            <tr>
            <td><b>8 марта</b></td>
         <td> <textarea name="march8" style="width:100%; height: 100px;"><?php echo isset($march8) ? $march8 : "" ; ?></textarea> </td>
          </tr>
            <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          <tr>
            <td><b>12 апреля</b></td>
           <td> <textarea name="apr12" style="width:100%; height: 100px;"><?php echo isset($apr12) ? $apr12 : "" ; ?></textarea> </td>
          </tr>
            <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
        
          <tr>
            <td><b>9 мая</b></td>
           <td> <textarea name="may9" style="width:100%; height: 100px;"><?php echo isset($may9) ? $may9 : "" ; ?></textarea> </td>
          </tr>
            <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          <tr>
            <td><b>1 сентярбя</b></td>
            <td> <textarea name="sep1" style="width:100%; height: 100px;"><?php echo isset($sep1) ? $sep1: "" ; ?></textarea> </td>
          </tr>
            <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          <tr>
            <td><b>7 ноября</b></td>
       <td> <textarea name="nov7" style="width:100%; height: 100px;"><?php echo isset($nov7) ? $nov7 : "" ; ?></textarea> </td>
          </tr>
            <tr>
            <td colspan="2"><div class='split'></div></td>
          </tr>
          
          

      </table>
      </div>
     --> 
    
    </div>
</div>
</form>
