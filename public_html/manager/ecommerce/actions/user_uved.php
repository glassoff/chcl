<?php

if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

switch($_REQUEST['a']) {
  case 5088:
    if(!$modx->hasPermission('edit_web_user')) {
      $e->setError(3);
      $e->dumpError();
    }
    break;

    break;
  default:
    $e->setError(3);
    $e->dumpError();
}

$disc = mysql_escape_string($_REQUEST['id']);
 $sql4 = "select sname, lname, fname from $dbase.`".$table_prefix."notice`,  $dbase.`".$table_prefix."web_user_attributes` 
       WHERE     user_id=internalKey  and disc_id =$disc and active=1 ";
	$rs4 = mysql_query($sql4);
	$row4 = mysql_fetch_assoc($rs4);

?>


<div class="subTitle">
    <span class="right">Подписавшиеся на уведомления</span>

    <table cellpadding="0" cellspacing="0" class="actionButtons">       
         <td id="Button2"><a href="index.php?a=5500"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" align="absmiddle"> <?php echo $_lang["cancel"]; ?></a></td>
    </table>
</div>
<div class="sectionHeader"></div>
<div class="sectionBody">

<!-- helio : changed here, add tab support -->

<script type="text/javascript" src="media/script/tabpane.js"></script>   

    <div class="tab-pane" id="childPane">
        <script type="text/javascript">
            docSettings = new WebFXTabPane( document.getElementById( "childPane" ) );
        </script>        
        <!-- General -->
        <div class="tab-page" id="tabdocGeneral">
            <h2 class="tab">Подписавшиеся на уведомления</h2>
            <script type="text/javascript">docSettings.addTabPage( document.getElementById( "tabdocGeneral" ) );</script>
			<!-- end change -->        
			<div class="sectionBody">
			<br>
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
	
             <?php  do { ?> 
              <tr><td><br><?php echo $row4["fname"] ?> &nbsp; <?php echo $row4["sname"] ?>&nbsp; <?php echo $row4["lname"] ?></td></tr>
               <tr> <td ><br><div class='split'></div></td>
          </tr>

 <?php } while ($row4 = mysql_fetch_assoc($rs4)); ?>
     	  
			  
			  
			 
		
			
          
			</table>
			</div><!-- ent div tab -->
		    </div>
			
		 	
</div>
</div><!-- end sectionBody -->




