<?php
if (IN_MANAGER_MODE != "true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
// check permissions

if(!$modx->hasPermission('ec_manage_taxes')) {
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
	    "WHERE action='5306' AND id='$id'";
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
    $tblsc = $dbase . ".`" . $table_prefix . "site_ec_regions`";
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
?>
<script>
function deletedregion() {
    if(confirm("<?php echo $_lang['ec_region_delete_confirm']; ?>")==true) {
        document.location.href="index.php?id=" + document.mutate.id.value + "&a=5304";
    }
}
</script>
<form name="mutate" method="post" action="index.php">
<input type="hidden" name="a" value="5307" />
<input type="hidden" name="id" value="<?php echo $content['id'];?>" />
<input type="hidden" name="mode" value="<?php echo $_REQUEST['a'];?>" />
<input type="submit" name="save" style="display:none" />
<div class="subTitle">
   <span class="right">
   <?php echo $_lang['ec_edit_region_hdr']; ?></span>
   <table cellpadding="0" cellspacing="0" class="actionButtons">
        <tr>
            <td id="Button1"><a href="#" onclick="documentDirty=false; document.mutate.save.click();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" /> <?php echo $_lang['save']; ?></a></td>
             <?php if ($_REQUEST['a'] != 5305) {?>
            <td id="Button2"><a href="#" onclick="deletedregion();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/delete.gif" /> <?php echo $_lang['delete']; ?></a></td>
            <?php }?>
            <td id="Button5"><a href="#" onclick="documentDirty=false;document.location.href='index.php?a=5300';"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" /> <?php echo $_lang['cancel']; ?></a></td>
        </tr>
    </table>
    <script type="text/javascript">
        <?php if($_REQUEST['a']=='5301') { ?>document.getElementById("Button2").className='disabled';<?php } ?>
    </script>
    <div class="stay">
    <table border="0" cellspacing="1" cellpadding="1">
    <tr>
        <td><span class="comment">&nbsp;<?php echo $_lang["after_saving"];?>:</span></td>
        <?php if ($modx->hasPermission('ec_manage_regiones')) { ?>
            <td><input name="stay" id="stay1" type="radio" class="radio" value="1" <?php echo $_REQUEST['stay']=='1' ? "checked='checked'":'' ?> /></td><td><label for="stay1" class="comment"><?php echo $_lang['stay_new']; ?></label></td>
        <?php } ?>
        <td><input name="stay" id="stay2" type="radio" class="radio" value="2" <?php echo $_REQUEST['stay']=='2' ? "checked='checked'":'' ?> /></td><td><label for="stay2" class="comment"><?php echo $_lang['stay']; ?></label></td>
        <td><input name="stay" id="stay3" type="radio" class="radio" value="" <?php echo $_REQUEST['stay']=='' ? "checked='checked'":'' ?> /></td><td><label for="stay3" class="comment"><?php echo $_lang['close']; ?></label></td>
    </tr>
    </table>
    </div>
</div>

<div class="sectionHeader"><?php echo $_lang['ec_region_form_hdr']; ?></div>
<div class="sectionBody">


            <?php
?>
            <table width="500" border="0" cellspacing="0" cellpadding="0">
              <tr style="height: 24px;">
                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_region_name']; ?></span></td>
                <td><input name="name" type="text" maxlength="255" value="<?php echo htmlspecialchars(stripslashes($content['name']));?>" class="inputBox" style="width:300px;" onchange="" spellcheck="true" />
                </td>
              </tr>
              
               <tr style="height: 24px;">
                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_region_postcode']; ?></span></td>
                <td><input name="postcode" type="text" maxlength="50" value="<?php echo htmlspecialchars(stripslashes($content['postcode']));?>" class="inputBox" style="width:50px;" onchange=""/>
                </td>
              </tr>
            
              <tr style="height: 24px;">
                <td width='150' align="left"><span class='warning'><?php echo $_lang['ec_region_rate']; ?></span></td>
                <td><input name="rate" type="text" maxlength="10" value="<?php echo htmlspecialchars(stripslashes($content['rate']));?>" class="inputBox" style="width:50px;" onchange=""/>
                </td>
              </tr>
            
              
              <tr style="height: 24px;">
                <td valign="top" width="100" align="left"><span class='warning'><?php echo $_lang['ec_region_desc']; ?></span></td>
                <td valign="top"><textarea name="description" class="inputBox" rows="3" style="width:300px;" onchange=""><?php echo htmlspecialchars(stripslashes($content['description']));?></textarea></td>
              </tr>
             
           
                <tr style="height: 24px;">
                <td valign="top" width="100" align="left"><span class='warning'><?php echo $_lang['ec_region_note']; ?></span></td>
                <td valign="top"><textarea name="note" class="inputBox" rows="3" style="width:300px;" onchange=""><?php echo htmlspecialchars(stripslashes($content['note']));?></textarea></td>
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
                <td><span class='warning'><?php echo $_lang['ec_region_active']; ?></span></td>
                <td>
                  <input type="hidden" name="active" value="0" onchange=""/>
                <input name="active" type="checkbox" class="checkbox" value="1" <?php echo (isset($content['active']) && $content['active']==1) ? "checked" : "" ;?>  <?php echo (!isset($content['active'])) ? "checked" : "" ;?>/>
              
                </td>
              </tr>	      
              
            
                
              
         
          
          
          
              
</div>



