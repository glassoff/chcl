<?php
if(IN_MANAGER_MODE!="true") die('<b>' . $_lang['kiwee_include_order_error'] . '</b>');
if ($_SESSION['mgrPermissions']['settings'] != "1") { echo 'Insufficient permissions for this module.'; exit; }		
$theme = $manager_theme ? "$manager_theme/":"";
$_1c_data_dir = MODX_BASE_PATH.'manager/ecommerce/1cpricelist/data/';
$info_file = $_1c_data_dir."info.php";
function upload1CData() {
	global $theme;
	global $modx;
	global $_lang;
    global $_1c_data_dir;
	global $_FILES;
	$updateError = '';
	$output = '';

	include_once(MODX_BASE_PATH.'manager/ecommerce/1cpricelist/fileUpload.class.php'); 
	$max_size = 1024*250; // the max. size for uploading		
	$fu = new fileUpload($_lang);
	$fu->upload_dir = $_1c_data_dir; 
	$fu->extensions = array(".txt"); 	
	$fu->max_length_filename = 100; 
	$fu->rename_file = true;
	$fu->the_temp_file = $_FILES['upload_file']['tmp_name'];
	$fu->the_file = $_FILES['upload_file']['name'];	
	$fu->http_error = $_FILES['upload_file']['error'];
	$fu->replace = 'y'; 
	$fu->do_filename_check = "y"; 
	$new_name = '1c_data';
	if ($fu->upload($new_name)) { 
		$full_path = $fu->upload_dir.$fu->file_copy;
		$uploadError = false;		
		$fp = fopen($full_path, "r");
		$total_quantity = 0;
		$model_quantity = 0;
		while(!feof($fp)) {
			$line = fgets($fp);			
			$cmd = substr($line,0,1);			
			if ($cmd == '[') { // region begin
				$region_ = substr($line,1);				
			} elseif ($cmd == ']') { // region end				
				$all_info['regions'][] = array('region' => $region_, 'shops'=>$shops);
				$shops = array();
			} elseif ($cmd == '{') { // shop begin
				$shop_ = substr($line,1);
				$shop_arr = explode('~',$shop_);
				$shop['id'] = $shop_arr[0];				
				$shop['name'] = $shop_arr[1];
				$shop_file = $_1c_data_dir."shop_".$shop['id'].".php";
				$shop_fh = fopen($shop_file,'w');	
				if ($shop_fh === false) {
					$error = $_lang['_1C_file_opening_warning'];
					break(1);
				}
				$start_code = "<?php \n";
				if (fwrite($shop_fh, $start_code) === FALSE) {
					$error = $_lang['_1C_file_writing_warning'];
					break(1);
				}		
			} elseif ($cmd == '}') { //shop end
				$shop['model_quantity'] = $model_quantity;
				$shop['total_quantity'] = $total_quantity;				
				$shops[] = $shop;	
				$end_code = "?> \n";
				if (fwrite($shop_fh, $end_code) === FALSE) {
					$error = $_lang['_1C_file_writing_warning'];
					break(1);
				}	
				fclose($shop_fh);	
				$total_quantity = 0;
				$model_quantity = 0;
			} else { // item
				$item_ar = explode('~', $line);
				$total_quantity += $item_ar[1];
				$model_quantity++;
				$item_code = '$PriceList1C[\''.$item_ar[0].'\']=array(\'id\'=>'.$item_ar[0].', \'quantity\'=>'.$item_ar[1].', \'price\'=>'.substr($item_ar[2],0,strlen($item_ar[2])-2).');'."\n";
				if (fwrite($shop_fh, $item_code) === FALSE) {
					$error = $_lang['_1C_file_writing_warning'];
					break(1);
				}								
			}			
		}
		//unlink($full_path);
		$settings_file = $_1c_data_dir."info.php";
		$set_fh = fopen($settings_file,'w');					
		if ($set_fh === false) {
			$error = $_lang['_1C_file_opening_warning'];
			break(1);
		}
		$all_info['last_upload'] = date("m.d.Y");
		$all_info = serialize($all_info);
		if (fwrite($set_fh, $all_info) === FALSE) {
			$error = $_lang['_1C_file_writing_warning'];
			break(1);
		}
		fclose($set_fh);		
		$error == '';
		
	} else {
		$uploadError = true;
		$upload_errors = $fu->message;
	} 
	if ($error != '') {
	
	} elseif ($uploadError === false) {
		$output .= '<p>' . $_lang[0].'</p>';
	} else {
		$output .= '<p>' . $_lang[18] . '</p>';				
		foreach ($upload_errors as $upload_error) {
			$output.= '<p>'.$upload_error.'</p>';
		}	
	}
	
	return $output;
}

function changeSettings1C() {
	global $theme;
	global $modx;
	global $_lang;
    global $_1c_data_dir;
	global $_POST;
	$error = '';
	$output = '';    
	$settings_file = $_1c_data_dir."settings.php";
	$settings_fh = fopen($settings_file,'w');	
	if ($shop_fh === false) {
		$error = $_lang['_1C_file_opening_warning'];		
	} else {		
		$_code = "<?php \n";
		$_code .= '$settings1C[\'default_pl_id\'] = '. @$_POST['settings1C']['default_pr_id'] .";\n";	
		$_code .= "?> \n";
		if (fwrite($settings_fh, $_code) === FALSE) {
			$error = $_lang['_1C_file_writing_warning'];
		} else {
			$error == '';	
		}	
		fclose($settings_fh);	
	}	
	if ($error === '') {
		$output .= '<p>' . $_lang['1C_process_update_success'].'</p>';
	} else {
		$output .= '<p>' . $_lang['1C_process_update_error'] . '</p>';	
		$output .= '<p>' . $error . '</p>';		
	}
	return $output;
}

if ($_REQUEST['a'] == 5101) {
   	$upload_msg = upload1CData();
}   
if ($_REQUEST['a'] == 5102) {
	$change_stg_msg = changeSettings1C();
}	
?>
<script type="text/javascript">

	//delivery	
	function delivery_add() {
		window.location.href='index.php?a=5301';	
	}
	
	function delivery_delete(id){
		if (confirm("<?php echo $_lang["ec_delivery_delete_confirm"]; ?>") == true) {
			window.location.href='index.php?a=5304&id='+id;
		}
	}
	
	function delivery_properties(id) {
		window.location.href='index.php?a=5302&id='+id;
	}
	
</script>
<br>
<div class="sectionHeader"><?php echo $_lang["ec_manage_1c"]; ?></div>
<div class="sectionBody">
<script type="text/javascript" src="media/script/tabpane.js"></script>
<!-- load modules -->
<div class="tab-pane" id="TaxPane" style="border:0">
	<script type="text/javascript">
	   	tpSettings = new WebFXTabPane( document.getElementById( "TaxPane" ) );
	</script>		  
	<div class="tab-page" id="tabInfo">
   		<h2 class="tab"><?php echo $_lang['ec_report_info'] ?></h2>
   		<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabInfo" ) );</script>
   		
	</div>
	<div class="tab-page" id="tabUpload">
   		<h2 class="tab"><?php echo $_lang['1C_tab_upload_data']?></h2>
   		<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabUpload" ) );</script>
   		
	</div>
	<div class="tab-page" id="tabConfig">
   		<h2 class="tab"><?php echo $_lang['1C_tab_change_settings']  ?></h2>
   		<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabConfig" ) );</script>
   		
	</div>
</div>
</div>
