<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
// Includes TreeView State Saver added by Jeroen:Modified by Raymond
$id = mysql_escape_string($_REQUEST['id']);
// get document groups for current user
$sql = " SELECT so.*, IF(paid = 1,'".$_lang['ec_order_paid']."','".$_lang['ec_order_notpaid']."') as 'paid_status'," .
       " os.name as status_name, pt.name as payment_m, so.id as order_id,pt.auto as isauto ".
	   " FROM ".$modx->getFullTableName("site_ec_orders") . " so ".  
	   " INNER JOIN" .$modx->getFullTableName("ec_order_status"). " os ON  os.id = so.status ".
	   " INNER JOIN" .$modx->getFullTableName("site_ec_payment_methods"). " pt ON  so.payment_type = pt.id ". 	    
       " WHERE so.id = '$id'";
$rs = mysql_query($sql);
$limit = mysql_num_rows($rs);
if($limit>1) {
    echo " Internal System Error...<p>";
    print "More results returned than expected. <p>Aborting.";
    exit;
}
else if($limit==0){
    //$e->setError(3);
    //$e->dumpError();
}
$order = mysql_fetch_assoc($rs);


?>
<script type="text/javascript">
    function deleteOrder() {
        if(confirm("<?php echo $_lang['confirm_delete_order'] ?>")==true) {
            document.location.href="index.php?id=<?php echo $_REQUEST['id']; ?>&a=5502";
        }
    }
    function changeOrderStatus(orderid,status) {
        document.location.href="index.php?&a=5503&order_id="+orderid+"&status="+status;
    }   
</script>

<div class="subTitle">
    <span class="right"><?php echo $_lang["ec_order_details_hdr"]; ?></span>

    <table cellpadding="0" cellspacing="0" class="actionButtons">       
        <td id="Button3"><a href="javascript:void(0)" onclick="deleteOrder();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/delete.gif" align="absmiddle"> <?php echo $_lang["delete"]; ?></a></td>
         <td id="Button2"><a href="index.php?a=5500"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" align="absmiddle"> <?php echo $_lang["cancel"]; ?></a></td>
    </table>
</div>
<div class="sectionHeader"><?php echo $_lang["ec_order_details_title"]; ?></div>
<div class="sectionBody">

<!-- helio : changed here, add tab support -->

<script type="text/javascript" src="media/script/tabpane.js"></script>   

    <div class="tab-pane" id="childPane">
        <script type="text/javascript">
            docSettings = new WebFXTabPane( document.getElementById( "childPane" ) );
        </script>        
        <!-- General -->
        <div class="tab-page" id="tabdocGeneral">
            <h2 class="tab"><?php echo $_lang["settings_general"] ?></h2>
            <script type="text/javascript">docSettings.addTabPage( document.getElementById( "tabdocGeneral" ) );</script>
			<!-- end change -->        
			<div class="sectionBody">
			
			<table width="100%" border="0" cellspacing="0" cellpadding="0">
			  <tr>
			    <td colspan="2"><b><?php echo $_lang["order_general_data"]; ?></b></td>
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["order_id"]; ?>: </td>
			    <td><?php echo $order['order_id']; ?></td>
			  </tr>			  
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["order_bonus_code"]; ?>: </td>
			    <td><?php echo $order['bonus_code']; ?></td>
			  </tr>
			  <tr>
			    <td width="200" valign="top">Бонусный код: </td>
			    <td><?php echo $order['bonuscode']; ?></td>
			  </tr>	
			  <tr>
			  	<td></td>
			    <td class="actionButtons">
			    	<a style="width:200px;" href="index.php?a=5500&importorders=1&order_id=<?php echo $order['id']; ?>" target="_blank">
							<img align="absmiddle" src="media/style/MODxLight/images/icons/save.gif"> 
							Выгрузка заказа с сайта в 1С							
					</a>
			    </td>
			  </tr>			  		  
			  <tr>
			    <td colspan="2" height="20" valign="middle">
			    <div class="stay"></div>
			    </td>			   
			  </tr>			  
			  
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_paid_status"]; ?> </td>
			    <td>			    
			    <form action="index.php?a=5503" name='order_status' method="POST"> 
			    <input type="hidden" name="cmd" value="paid"/> 
				<input type="hidden" name="order_id" value="<?php echo $id?>"/> 
				<input type="hidden" name="customer_id" value="<?php echo $order['customer_id']?>"/> 	
				<table class="actionButtons" cellpadding="2" cellspacing="0">
			    	<tr>
			    		<td align="left" valign="top" colspan="2">
			    		<strong>
			    		 <?php if ($order['paid'] == 1) echo $_lang["ec_order_paid"]; else echo $_lang["ec_order_notpaid"];?>
			    		</strong>
			    		</td>			    		
			    	</tr>		    
			    	<tr>
			    		<td style="padding-right:5px;"><?php echo $_lang['ec_order_paid_change']; ?></td>
			    		<td style="padding-right:5px;" colspan="2">
			    			<select name="order_paid">
			    				<option value="1" <?php if ($order['paid'] == 1) echo "selected";?>><?php echo $_lang["ec_order_paid"];?></option>
			    				<option value="0" <?php if ($order['paid'] == 0) echo "selected";?>><?php echo $_lang["ec_order_notpaid"];?></option>
			    			</select>			    						    			
			    		</td>			    		
			    	</tr>		
			    	<?php if ($order['isauto'] == 0) { ?>
			    	<tr>
			    		<td style="padding-right:5px;" valign="top"><?php echo $_lang['ec_order_paidin']; ?></td>
			    		<td style="padding-right:5px;" colspan="2">
			    			<textarea cols="38" rows="6" name="paidin"><?php echo stripslashes($order['paidin'])?></textarea>
			    		</td>			    		
			    	</tr>
			    	<?php }?>	    	
			    	<!--
					<tr>
			    		<td colspan="2" id="Button1">			    		
			    		<input type="checkbox" name="notifyuser" value="1">
			    		<?php echo $_lang["ec_order_done_inform_user"]?>
			    		</td>
					</tr>   	-->
			    	<tr>
			    		<td colspan="2" id="Button1">
			    		<input type="submit" size="100" value="<?php echo $_lang["save"];?>">
			    		</td>
			    	</tr>
			    </table>
			    </form>
			    
			    </td>
			  </tr>	
              
              
              
               <tr>
			    <td colspan="2" height="20" valign="middle">
			    <div class="stay"></div>
			    </td>			   
			  </tr>			  
			  
			  <tr>
			    <td width="200" valign="top">Подтвержден/не подтвержден </td>
			    <td>			    
			    <form action="index.php?a=5503" name='order_status' method="POST"> 
			    <input type="hidden" name="cmd" value="confirmed"/> 
				<input type="hidden" name="order_id" value="<?php echo $id?>"/> 
				<input type="hidden" name="customer_id" value="<?php echo $order['customer_id']?>"/> 	
				<table class="actionButtons" cellpadding="2" cellspacing="0">
			    	<tr>
			    		<td align="left" valign="top" colspan="2">
			    		<strong>
			    		 <?php if ($order['confirmed'] == 1) echo $_lang["ec_order_confirmed"]; else echo $_lang["ec_order_notconfirmed"];?>
			    		</strong>
			    		</td>			    		
			    	</tr>		    
			    	<tr>
			    		<td style="padding-right:5px;">Изменить статус &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;       </td>
			    		<td style="padding-right:5px;" colspan="2">
			    			<select name="order_confirmed">
			    				<option value="1" <?php if ($order['confirmed'] == 1) echo "selected";?>><?php echo $_lang["ec_order_confirmed"];?></option>
			    				<option value="0" <?php if ($order['confirmed'] == 0) echo "selected";?>><?php echo $_lang["ec_order_notconfirmed"];?></option>
			    			</select>			    						    			
			    		</td>			    		
			    	</tr>		
			 
			    	<tr>
			    		<td colspan="2" id="Button1">
			    		<input type="submit" size="100" value="<?php echo $_lang["save"];?>">
			    		</td>
			    	</tr>
			    </table>
			    </form>
			    
			    </td>
			  </tr>	
              
              
              
              
              
              
              
			  <tr>
			    <td colspan="2" height="20" valign="middle">
			    <div class="stay"></div>
			    </td>			   
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_status"]; ?>: </td>
			    <td>	    
			    <form action="index.php?a=5503" name='order_status' method="POST">
			    <input type="hidden" name="cmd" value="status"/> 
				<input type="hidden" name="order_id" value="<?php echo $id?>"/> 
				<input type="hidden" name="customer_id" value="<?php echo $order['customer_id']?>"/> 	
				<input type="hidden" name="order_old_status" value="<?php echo $order['status']?>"/> 
			    <table class="actionButtons" cellpadding="2" cellspacing="0">
			    	<tr>
			    		<td align="left" valign="top" colspan="3"><strong><?php echo $order['status_name']; ?></strong></td>
			    		
			    	</tr>
			    	<tr>    		
			    		<td style="padding-right:5px;"><?php echo $_lang['ec_order_change_status']; ?></td>
			    		<td style="padding-right:5px;">    	
			    		<?php
			    		    
							$sql = 'SELECT * FROM ' . $modx->getFullTableName("ec_order_status") . ' order by listindex';
							$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
							$lines[] = '<select name="order_status">';	
							if ($rs && mysql_num_rows($rs)>0) {
								while ($row = mysql_fetch_assoc($rs)) {							
									if ($order['status'] == $row['id']) $lines[] = '<option value="'.$row['id'].'"  selected>'.$row['name'].'</option>';					else $lines[] = '<option value="'.$row['id'].'">'.$row['name'].'</option>';					
								}	
							}				
							$lines[] = '</select>';		
							echo implode("\n", $lines);
						?>   		
						</td>
			    		<td style="padding-right:5px;">
			    			
			    		</td>			    		
			    	</tr>	    	
			    	
			    	<tr>
			    		<td style="padding-right:5px;" valign="top"><?php echo $_lang['ec_order_admin_comments']; ?></td>
			    		<td style="padding-right:5px;" colspan="2">
			    			<textarea cols="38" rows="6" name="admin_comments"><?php echo stripslashes($order['admin_comments'])?></textarea>
			    		</td>			    		
			    	</tr>	
					
				
				
					
			    	<!--	
			    	<tr>
			    		<td colspan="3" id="Button1">
			    		
			    		<input type="checkbox" name="notifyuser" value="1">
			    		<?php echo $_lang["ec_order_done_inform_user"]?>
			    		</td>
			    	</tr>	    	
					-->
			    	<tr>
			    		<td colspan="3" id="Button1">
			    		<input type="submit" size="100" value="<?php echo $_lang["save"];?>">
			    		</td>
			    	</tr>
			    </table>
			    </form>
			    
			    </td>
			  </tr>
			  <tr>
			    <td colspan="2" height="20" valign="middle">
			    <div class="stay"></div>
			    </td>			   
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_date"]; ?>: </td>
			    <td><?php echo datetime($order['order_date']); ?> </td>
			  </tr>			  
			   <tr>
			    <td width="200" valign="top"><?php echo $_lang["order_bonus"]; ?>: </td>
			    <td>			    	
			    	<?php 
			    	echo $order['bonus']."%";
			    	?>			    	 
			    </td>
			  </tr>	
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_discount"]; ?>: </td>
			    <td><?php echo $order['discount']."%"; ?></td>
			  </tr>
			 <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_delivery_amount"]; ?>: </td>
			    <td>
			    <?php 
			    $ar = array('self'=>'Самовывоз', 'onaddress'=>'Доставка по указанному адресу','curer_v_mkad' => 'Курьер по москве в пределах МКАД', 'curer_za_mkad' => 'Курьер по москве за пределы МКАД', '1class' => 'Отправление "1 класса"', 'basic' => 'Наземная почта', 'outsea'=>'Доставка в Ближнее и Дальнее зарубежье');
			    echo money($order['delivery_amount']).'<b>['.@$ar[$order["delivery_type"]].']</b>';?> 
				</td>
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_quantity"]; ?>: </td>
			    <td><?php echo quantity($order['quantity']); ?></td>
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_amount"]; ?>: </td>
			    <td><?php echo money($order['amount']); ?></td>
			  </tr>
			  
			   <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_paid_status"]; ?>: </td>
			    <td><?php  echo  $order['paid'] == 1 ? $_lang["ec_order_paid"] : $_lang["ec_order_notpaid"]; ?></td>
			  </tr>
			   <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_payment_type"]; ?>: </td>
			    <td><?php echo $order['payment_m']; ?></td>
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_paidin"]; ?>: </td>
			    <td><?php  echo  $order['paid'] == 1 ? $order['paidin'] : "(<i>".$_lang["notset"]."</i>)"; ?></td>
			  </tr>
			 
			  
			   <tr>
			    <td colspan="2" height="20" valign="middle">
			    <div class="stay"></div>
			    </td>			   
			  </tr>
			  
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_cust_comment"]; ?>: </td>
			    <td valign="top"><?php echo $order['customer_comment']; ?></td>
			  </tr> 
			  
			  
			 
			  
			    <tr>
			    <td colspan="2" height="20" valign="middle">
			    <div class="stay"></div>
			    </td>			   
			  </tr>
			  
			  <tr>
			    <td valign="top">Если каких-либо позиций из заказа не будет в наличии, то: </td>
			    <td valign="top"><b>
			    		<?php echo $order['customer_sku_comment1']; ?> затем <?php echo $order['customer_sku_comment']; ?> 
			    </b></td>
			  </tr> 
			  
			    <tr>
			    <td colspan="2" height="20" valign="middle">
			    <div class="stay"></div>
			    </td>			   
			  </tr>
			  <tr>
			    <td colspan="2"><b><?php echo $_lang["user_address"]; ?></b></td>			   
			  </tr>
			    <form action="index.php?a=5555" name='order_details' method="POST"> 
			  
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_region1"]; ?>: </td>
			    <td> <input type="text" name="region" value="<?php echo $order['customer_region']; ?>"/> </td>
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_state"]; ?>: </td>
			    <td><input type="text" name="town" value="<?php echo $order['customer_state']!='' ? $order['customer_state'] : "" ; ?>"/></td>
			  </tr>  
			   <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_postcode1"]; ?>: </td>
			    <td><input type="text" name="postcode1" value="<?php echo $order['customer_postcode1']!='' ? $order['customer_postcode1'] : "" ; ?>"/></td>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_street"]; ?>: </td>
			    <td><input type="text" name="street" value="<?php echo $order['customer_street']!='' ? $order['customer_street'] : "" ; ?>"/></td>
			  </tr>
			   <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_house"]; ?>: </td>
			    <td><input type="text" name="house" value="<?php echo $order['customer_dom']!='' ? $order['customer_dom'] : "" ; ?>"/></td>
			  </tr>
			   <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_housing"]; ?>: </td>
			    <td><input type="text" name="korpus" value="<?php echo $order['customer_korpus']!='' ? $order['customer_korpus'] : "" ; ?>"/></td>
			  </tr>
			 
			   <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_apartament"]; ?>: </td>
			    <td><input type="text" name="kvartira" value="<?php echo $order['customer_kvartira']!='' ? $order['customer_kvartira'] : "" ; ?>"/></td>
			  </tr>
			   <tr>
			    <td width="200" valign="top">Удаленность от МКАД (км): </td>
			    <td><input type="text" name="km" value="<?php echo $order['km']!='' ? $order['km'] : "" ; ?>"/></td>
			  </tr>
			
			   <tr>
			    <td colspan="2">&nbsp;</td>
			  </tr>
			  
			  <tr>
			    <td colspan="2"><b><?php echo $_lang["order_customer_contacts"]; ?></b></td>
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["ec_order_cust_name"]; ?>: </td>
			    <td><b>
			    	<?php echo $order['customer_fname']." ".$order['customer_sname']." ".$order['customer_lname']." ".$order['customer_type']." ".$order['customer_company']; ?>
			    </b></td> 
			  </tr>
			  <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_phone"]; ?>: </td>
			    <td><input type="text" name="phone" value="<?php echo $order['customer_phone']!='' ? $order['customer_phone'] : "" ; ?>"/></td>
			  </tr>
			   <tr>
			    <td width="200" valign="top"><?php echo $_lang["user_email"]; ?>: </td>
			    <td><input type="text" name="email" value="<?php echo $order['customer_email']!='' ? $order['customer_email'] : "" ; ?>"/></td>
			  </tr>
			   <tr>
			    <td colspan="2">&nbsp;</td>
			  </tr>			  
			  <tr>
			    <td colspan="2"><b>Дополнительно</b></td>
			  </tr>
			  <tr>
			    <td width="200" valign="top">Источник информации о компании: </td>
			    <td>
			    	<?php echo $order['infosource']; ?>
			    </td> 
			  </tr>			  
			   <tr>
			    <td colspan="2">&nbsp;</td>
			  </tr>  
              	<tr>
			    		<td colspan="2">
                        <input type="hidden" name="order_id" value="<?php echo $id?>"/> 
				        	
			    		<input type="submit" size="100" value="<?php echo $_lang["save"];?>">
			    		</td>
			    	</tr>
			</table>
			</div><!-- ent div tab -->
		    </div>
			<div class="tab-page" id="tabOrderItems">
            <h2 class="tab"><?php echo $_lang["ec_order_items"] ?></h2>
            <script type="text/javascript">docSettings.addTabPage( document.getElementById( "tabOrderItems" ) );</script>
			<?php
				$sql = " SELECT oi.*,si.*".
					   " FROM ".$modx->getFullTableName("site_ec_order_items") . " oi ".  
					   " LEFT JOIN" .$modx->getFullTableName("site_ec_items"). " si ON  si.id = oi.item_id ".
					   " WHERE order_id = '$id'";
				//echo $sql;
				$number_of_results = 0;		
				$ds = mysql_query($sql);
				$result_size = mysql_num_rows($ds);
				include_once $base_path."manager/includes/controls/datagrid.class.php";
				$grd = new DataGrid('',$ds,$number_of_results); // set page size to 0 t show all items
				$grd->noRecordMsg = $_lang["no_records_found"];
				$grd->cssClass="grid";
				$grd->showRecordInfo=true;
				$grd->columnHeaderClass="gridHeader";
				$grd->itemClass="gridItem";
				$grd->altItemClass="gridAltItem";
				$grd->fields="num,pagetitle,acc_id,color_z,size_z,item_id,quantity,price";
				
				$grd->columns = $_lang["listnum"].",";
				$grd->columns .= $_lang["ec_order_itemname"].",";
				$grd->columns .= "Артикул,";
				$grd->columns .= "Цвет,";
				$grd->columns .= "Размер,";
				$grd->columns .= "Цена,";
				$grd->columns .= $_lang["ec_order_quantity"].","; 
				$grd->columns.= "Стоимость"; 
				//$grd->columns .= $_lang["ec_order_itemid"].",";
				//$grd->columns .= $_lang["ec_order_accid"].",";
					
								
					
				$grd->colWidths="20,150,70,70,70,70,70,70";
				$grd->colAligns="center,left,center,left,center,left,left,left";
				
				$grd->colTypes ="template:[+num+]";				
				$grd->colTypes.="||template:<a href=\"index.php?a=5004&id=[+item_id+]\" title=\"".$_lang['ec_click_to_view']."\">[+pagetitle+]</a>";
				$grd->colTypes.="||template:[+acc_id+]";
				$grd->colTypes.="||template:[+color_z+]";
				$grd->colTypes.="||template:[+size_z+]";
				$grd->colTypes.="||php:echo money(\$row['price']);";
				$grd->colTypes.="||php:echo quantity1(\$row['quantity'], \$row);";
				$grd->colTypes.="||php:echo money(\$row['price']*\$row['quantity']);";
				//$grd->colTypes.="||template:[+item_id+]";
				//$grd->colTypes.="||template:[+acc_id+]";
				
								
				
				if($listmode=='1') $grd->pageSize=0;
				if($_REQUEST['op']=='reset') $grd->pageNumber = 1;
				echo $grd->render();
				
			?>		
			
		 	</div>	
		 	
</div>
</div><!-- end sectionBody -->





