<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

// Includes TreeView State Saver added by Jeroen:Modified by Raymond
$id = $_REQUEST['id'];
// Jeroen posts SESSION vars :Modified by Raymond
if (isset($_GET['opened'])) $_SESSION['openedArray'] = $_GET['opened'];

//helio: required for makeTable class => table pagination

$url = $modx->config[(site_url)];

$tblsc = $dbase.".".$table_prefix."site_ec_items";
// get document groups for current user

$sql = "SELECT DISTINCT sc.*
        FROM $tblsc sc        
        WHERE sc.id = $id";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);
if($limit>1) {
    echo " Internal System Error...<p>";
    print "More results returned than expected. <p>Aborting.";
    exit;
}
else if($limit==0){
    $e->setError(3);
    $e->dumpError();
}
$content = mysql_fetch_assoc($rs);

$createdby = $content['createdby'];
$sql = "SELECT username FROM $dbase.".$table_prefix."manager_users WHERE id='$createdby';";
$rs = mysql_query($sql);

$row=mysql_fetch_assoc($rs);
$createdbyname = $row['username'];

$editedby = $content['editedby'];
$sql = "SELECT username FROM $dbase.".$table_prefix."manager_users WHERE id=$editedby;";
$rs = mysql_query($sql);

$row=mysql_fetch_assoc($rs);
$editedbyname = $row['username'];

$templateid = $content['template'];
$sql = "SELECT templatename FROM $dbase.".$table_prefix."site_templates WHERE id=$templateid;";
$rs = mysql_query($sql);

$row=mysql_fetch_assoc($rs);
$templatename = $row['templatename'];
$_SESSION['itemname']=$content['pagetitle'];
//I've also moved the <script> part first because of cookie management (tabPane) but no more used in this version (error: output already started header.inc.php)
?>
<script type="text/javascript">
    function deleteItem() {
        if(confirm("<?php echo $_lang['confirm_ec_delete_item'] ?>")==true) {
            document.location.href="index.php?id=<?php echo $_REQUEST['id']; ?>&a=5007";
        }
    }
    function removedItem() {
        if(confirm("<?php echo $_lang['confirm_ec_remove_item'] ?>")==true) {
            document.location.href="index.php?id=<?php echo $_REQUEST['id']; ?>&a=5009";
        }
    }
    function editItem() {
        document.location.href="index.php?id=<?php echo $_REQUEST['id']; ?>&a=5002";
    }   
</script>

<div class="subTitle">
    <span class="right"><?php echo $_lang["ec_item_preview_hdr"]; ?></span>
    <table cellpadding="0" cellspacing="0" class="actionButtons">
        <td id="Button1"><a href="#" onclick="editItem();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" align="absmiddle"> <?php echo $_lang["edit"]; ?></a></td>
	<?php if ($content['deleted'] == 1) {?>
        <td id="Button4"><a href="#" onclick="removeItem();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/trash.png" align="absmiddle"> <?php echo $_lang["ec_remove_items"]; ?></a></td>
    <?php }?>  
    <?php if ($content['deleted'] == 0) {?>  
        <td id="Button3"><a href="#" onclick="deleteItem();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/delete.gif" align="absmiddle"> <?php echo $_lang["delete"]; ?></a></td>
    <?php }?>    
    </table>
</div>

<div class="sectionHeader"><?php echo $_lang["ec_item_preview_title"]; ?></div>
<div class="sectionBody">
<!-- helio : changed here, add tab support -->
<script type="text/javascript" src="media/script/tabpane.js"></script>
<div class="tab-pane" id="FilterPane" style="border:0">
			<script type="text/javascript">
		    	tpSettings = new WebFXTabPane( document.getElementById( "FilterPane" ) );
		    </script>
		    <div class="tab-page" id="tabMain">
	       	<h2 class="tab"><?php echo $_lang["ec_item_preview_title"] ?></h2>
	       	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabMain" ) );</script>	
			<!-- end change -->        
			<div class="sectionBody">			
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <td colspan="2"><b><?php echo $_lang["page_data_general"]; ?></b></td>
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_item_title"]; ?>: </td>
			    <td><b><?php echo $content['pagetitle']; ?></b></td>
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_item_long_title"]; ?>: </td>
			    <td><?php echo $content['longtitle']!='' ? $content['longtitle'] : "(<i>".$_lang["notset"]."</i>)" ; ?></td>
			  </tr>
			  <tr>
			    <td><?php echo $_lang["ec_item_template"]; ?>: </td>
			    <td><?php echo $templatename ?></td>
			  </tr>
			  
			   <tr>
			    <td valign="top"><?php echo $_lang["ec_item_id"]; ?>: </td>
			    <td><?php echo $content['id']!='' ? $content['id'] : "(<i>".$_lang["notset"]."</i>)" ; ?></td>
			  </tr> 
			  
			  <tr>
			    <td valign="top"><?php echo $_lang["ec_item_acc_id"]; ?>: </td>
			    <td><?php echo $content['acc_id']!='' ? $content['acc_id'] : "(<i>".$_lang["notset"]."</i>)" ; ?></td>
			  </tr> 		  
			   <tr>
			    <td valign="top"><?php echo $_lang["ec_item_retail_price"]; ?>: </td>
			    <td>
			    <strong>
			    <?php echo $content['retail_price']!='' ? money($content['retail_price']) : "(<i>".$_lang["notset"]."</i>)" ; ?>
			    </strong>				
			    </td>
			  </tr> 
			   <tr>
			    <td valign="top"><?php echo $_lang["ec_item_mdealer_price"]; ?>: </td>
			    <td>
			    <strong>
			    <?php echo $content['mdealer_price']!='' ? money($content['mdealer_price']) : "(<i>".$_lang["notset"]."</i>)" ; ?>
			    </strong>
			    </td>
			  </tr> 
			  <tr>
			    <td valign="top"><?php echo $_lang["ec_item_dealer_price"]; ?>: </td>
			    <td>
			    <strong>
			    <?php echo $content['dealer_price']!='' ? money($content['dealer_price']) : "(<i>".$_lang["notset"]."</i>)" ; ?>
			    </strong>
			    </td>
			  </tr>
			  <?php if($content['package_items']>0){?> 
			  <tr>
			    <td valign="top">Количество в упаковке: </td>
			    <td>
			    <strong>
			    <?php echo $content['package_items']; ?>
			    </strong>
			    </td>
			  </tr>	
			  <?php 
			  	$allprices = array($content['package_price']);
			  	$prices = getPrices($content['id'], 'package');
				$min_price = 0;
				$max_price = 0;
				foreach($prices as $size_item => $price_item){
					$allprices[] = $price_item;
				}
				
				sort($allprices);
				
				$min_price = $allprices[0];
				$max_price = $allprices[count($allprices)-1];	

				$package_price_str = $content['package_price'];
				if($prices){
					$package_price_str = "$min_price - $max_price";
				}
			  ?>
			  <tr>
			    <td valign="top">Цена за упаковку: </td>
			    <td>
			    <strong>
			    <?php echo $package_price_str ?> руб.
			    </strong>
			    </td>
			  </tr>			  		  
			  <?php }?>
			   <tr>
			    <td valign="top"><?php echo $_lang["ec_item_sku"]; ?>: </td>
			    <td>
			    <strong>
			    <?php echo $content['sku']!='' ? quantity($content['sku']) : "(<i>".$_lang["notset"]."</i>)" ; ?>
			    </strong>
			    </td>
			  </tr> 			  
			  <tr>
			    <td valign="top"><?php echo $_lang["document_description"]; ?>: </td>
			    <td><?php echo $content['description']!='' ? $content['description'] : "(<i>".$_lang["notset"]."</i>)" ; ?></td>
			  </tr>
			  <tr>
			  <tr>
			    <td valign="top"><?php echo $_lang["document_summary"]; ?>: </td>
			    <td><?php echo $content['introtext']!='' ? $content['introtext'] : "(<i>".$_lang["notset"]."</i>)" ; ?></td>
			  </tr>  
			   <tr>
			    <td><?php echo $_lang['ec_item_menuindex']; ?>: </td>
			    <td><?php echo $content['menuindex']; ?></td>
			  </tr>			  
			  <tr>
			    <td colspan="2">&nbsp;</td>
			  </tr>
			  <tr>
			    <td colspan="2"><b><?php echo $_lang["page_data_changes"]; ?></b></td>
			  </tr>
			  <tr>
			    <td><?php echo $_lang["page_data_created"]; ?>: </td>
			    <td><?php echo strftime("%d/%m/%y %H:%M:%S", $content['createdon']+$server_offset_time); ?> (<b><?php echo $createdbyname ?></b>)</td>
			  </tr>
				<?php
				if($editedbyname!='') {
				?>
				  <tr>
				    <td><?php echo $_lang["page_data_edited"]; ?>: </td>
				    <td><?php echo strftime("%d/%m/%y %H:%M:%S", $content['editedon']+$server_offset_time); ?> (<b><?php echo $editedbyname ?></b>)</td>
				  </tr>
				<?php
				}
				?>
			  <tr>
			    <td colspan="2">&nbsp;</td>
			  </tr>
			  <tr>
			    <td colspan="2"><b><?php echo $_lang["page_data_status"]; ?></b></td>
			  </tr>
			  <tr>
			    <td><?php echo $_lang["page_data_status"]; ?>: </td>
			    <td><?php echo $content['published']==0 ? "<span class='unpublishedDoc'>".$_lang['page_data_unpublished']."</span>" : "<span class='publishedDoc'>".$_lang['page_data_published']."</span>"; ?>
			    </td>
			  </tr>			  
			  <tr>
			    <td><?php echo $_lang["ec_item_sell"]; ?>: </td>
			    <td><?php echo $content['sell']==0 ? $_lang['no'] : $_lang['yes']; ?></td>
			  </tr>
			   <tr>
			    <td><?php echo $_lang["ec_item_byorder"]; ?>: </td>
			    <td><?php echo $content['byorder']==0 ? $_lang['no'] : $_lang['yes']; ?></td>
			  </tr>
			  <tr>
			    <td><?php echo $_lang["ec_item_new"]; ?>: </td>
			    <td><?php echo $content['new']==0 ? $_lang['no'] : $_lang['yes']; ?></td>
			  </tr>
			  <tr>
			    <td><?php echo $_lang["ec_item_soon"]; ?>: </td>
			    <td><?php echo $content['soon']==0 ? $_lang['no'] : $_lang['yes']; ?></td>
			  </tr>
			  <tr>
			    <td><?php echo $_lang["ec_item_popular"]; ?>: </td>
			    <td><?php echo $content['popular']==0 ? $_lang['no'] : $_lang['yes']; ?></td>
			  </tr>
			  <tr>
			    <td><?php echo $_lang["ec_item_recommended"]; ?>: </td>
			    <td><?php echo $content['recommended']==0 ? $_lang['no'] : $_lang['yes']; ?></td>
			  </tr>
			  <tr>
			    <td><?php echo $_lang["ec_item_delete"]; ?>: </td>
			    <td><?php echo $content['deleted']==0 ? $_lang['no'] : $_lang['yes']; ?></td>
			  </tr>
			 
			 <tr>
			    <td colspan="2">&nbsp;</td>
			  </tr>
			  <tr>
			    <td colspan="2"><b><?php echo $_lang["ec_item_stat"]; ?></b></td>
			  </tr>
			  <tr>
			    <td valign="top"><?php echo $_lang["ec_item_rating"]; ?>: </td>
			    <td><?php echo $content['rating']!='' ? $content['rating'] : "(<i>".$_lang["notset"]."</i>)" ; ?></td>
			  </tr>
			 <tr>
			    <td valign="top"><?php echo $_lang["ec_item_views"]; ?>: </td>
			    <td><?php echo $content['views']!='' ? $content['views'] : "(<i>".$_lang["notset"]."</i>)" ; ?></td>
			  </tr>
			  
			  <tr>
			    <td valign="top"><?php echo $_lang["ec_item_votes"]; ?>: </td>
			    <td><?php echo $content['votes']!='' ? $content['votes'] : "(<i>".$_lang["notset"]."</i>)" ; ?></td>
			  </tr>
			  
			</table>
		    </div><!-- ent div tab -->
		</div><!-- end section body -->
	</div><!-- end documentPane -->
</div><!-- end sectionBody -->
<!--BEGIN SHOW HIDE PREVIEW WINDOW MOD-->
