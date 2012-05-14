<?php
/**
 * Document Manager Module - process.inc.php
 * 
 * Purpose: Contains the main form processing functions for the module
 * Author: Garry Nutting (Mark Kaplan - Menu Index functionalty, Luke Stokes - Document Permissions concept)
 * For: MODx CMS (www.modxcms.com)
 * Date:29/09/2006 Version: 1.6
 * 
 */
/**
 * changeTemplateVariables
 * 
 * @input - whether 'tree' or 'range' has been used
 * @pids - the Document IDs for processing
 */
function upload1CData() {
	global $theme;
	global $modx;
	global $_lang;
    global $_1c_data_dir;
	global $_FILES;
	global $basePath;
	
	$updateError = '';
	$output = '';

	include_once($basePath.'assets/modules/1cuploader/includes/fileUpload.class.php'); 
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
	
	$output .= updateHeader();
	if ($error != '') {
	
	} elseif ($uploadError === false) {
		$output .= '<p>' . $_lang[0].'</p>';
	} else {
		$output .= '<p>' . $_lang[18] . '</p>';				
		foreach ($upload_errors as $upload_error) {
			$output.= '<p>'.$upload_error.'</p>';
		}	
	}
	
	$output .= ' 
			<form name="back" method="post"><input type="submit" name="back" value="' . $_lang['1C_process_back'] . '" />
			</form>												 
		</div> 
	</body></html>';

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
	$output .= updateHeader();
	if ($error === '') {
		$output .= '<p>' . $_lang['1C_process_update_success'].'</p>';
	} else {
		$output .= '<p>' . $_lang['1C_process_update_error'] . '</p>';	
		$output .= '<p>' . $error . '</p>';		
	}
	
	$output .= ' 
			<form name="back" method="post"><input type="submit" name="back" value="' . $_lang['1C_process_back'] . '" />
			</form>												 
		</div> 
	</body></html>';

	return $output;
}


if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");

if(!$modx->hasPermission('ec_manage_1c')) {
	$e->setError(3);
	$e->dumpError();	
}
// check the document doesn't have any children
$id=intval($_GET['id']);
$deltime = time();
$sql = "UPDATE $dbase.`".$table_prefix."site_ec_items` SET deleted=1, deletedby=".$modx->getLoginUserID().", deletedon=$deltime WHERE id=$id;";
$rs = mysql_query($sql);
if(!$rs) {
	echo "Something went wrong while trying to set the document to deleted status...";
	exit;
} else {
	$header="Location: index.php?r=1&a=5000";
	header($header);
}

?>
