<?php
if(IN_MANAGER_MODE!="true") die('<b>' . $_lang['kiwee_include_order_error'] . '</b>');
if ($_SESSION['mgrPermissions']['settings'] != "1") { echo 'Insufficient permissions for this module.'; exit; }		
$theme = $manager_theme ? "$manager_theme/":"";
$_1c_data_dir = MODX_BASE_PATH.'manager/ecommerce/1cpricelist/data/';

function resetItemsCount(){
	global $modx;
	
	$sql = "UPDATE ".$modx->getFullTableName('site_ec_items')." SET sku='0'";
	return $modx->db->query($sql);
}

function upload1CItems() {
	global $theme;
	global $modx,$ec_settings;
	global $_lang;
    global $_1c_data_dir;
	global $_FILES;
	$updateError = '';
	$output = '';
	include_once(MODX_BASE_PATH.'manager/ecommerce/1cpricelist/fileUpload.class.php'); 
	$max_size = 1024*250*10000; // the max. size for uploading		
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
	$tv_id = 56;
	
	
	if ($fu->upload($new_name)) { 
		$full_path = $fu->upload_dir.$fu->file_copy;
		$uploadError = false;		
		$fp = fopen($full_path, "r");
		$total_quantity = 0;
		$model_quantity = 0;
		$codes = array();
		$added = 0;
		$updated = 0;
		$error = '';
		$acc_ids = array();	

		resetItemsCount();
		
		while(!feof($fp)) {
			$line = fgets($fp);			
			$row = explode("#",$line);
			$row = str_replace('\r\n','',$row);
			if ( count($row) < 16) {
				// $errors .= '<div>Could not export 5 the item - <b>'.$line.'</b></div>';
				$errors .= '';
				continue;
			}
			$code = trim($row[0]);	//каталог					
			if (!empty($code)) {	
			$code2= trim($row[1]);
				$country=  !empty($row[3]) ? mysql_escape_string($row[3]) : '';
				$acc_id =  !empty($row[2]) ? mysql_escape_string($row[2]) : '';
			if (in_array($acc_id,$acc_ids))	 echo 'DUBLICATED:'.$acc_id.'<br>';
				$acc_ids[] = $acc_id;
				$pagetitle =  !empty($row[5]) ? mysql_escape_string($row[5]) : '';				
			
				
			$producer =  !empty($row[4]) ? mysql_escape_string($row[4]) : '';
				$vendor =  !empty($row[6]) ? mysql_escape_string($row[6]) : '';
			
				$size =  !empty($row[7]) ? mysql_escape_string($row[7]) : '';
				//$growth =  !empty($row[8]) ? mysql_escape_string($row[8]) : '';
				$composition=  !empty($row[10]) ? mysql_escape_string($row[10]) : '';
				$material =  !empty($row[9]) ? mysql_escape_string($row[9]) : '';
				$color =  !empty($row[11]) ? mysql_escape_string($row[11]) : '';
				
				##
				$sizes_str = $row[7];
				$sizes = explode(",", $sizes_str);
				
				$retail_prices_str = $row[12];
				$retail_prices = explode(",", $retail_prices_str);
				$retail_price = floatval($retail_prices[0]); 
				
				$opt_prices_str = $row[13];
				$opt_prices = explode(",", $opt_prices_str);
				$price_opt = floatval($opt_prices[0]);
				
			$sku =  !empty($row[14]) ? floatval($row[14]): 0;
			
			$package_items =  !empty($row[15]) ? (int)$row[15]: 0;
			
				$package_prices_str = $row[16];
				$package_prices = explode(",", $package_prices_str);
				$package_price = floatval($package_prices[0]);	
				
				$retail_sizes_str = $row[17];
				$retail_sizes = explode(",", $retail_sizes_str);		
							
					
				if (!isset($codes[$code])) {  
					$sql = "SELECT contentid FROM ".$modx->getFullTableName('site_tmplvar_contentvalues');
					$sql.= " WHERE value = '$code' AND tmplvarid=$tv_id";					
			
					$rs = mysql_query($sql);
					$limit = mysql_num_rows($rs);					
					if($limit>0) {
						$item = mysql_fetch_assoc($rs);
						$codes[$code] = $item['contentid'];						  
					} else {	
						$codes[$code] = 227;			
					}
				} 
				
					
				$folder_id = $codes[$code];
				
				##
				$sql = "SELECT * FROM ".$modx->getFullTableName('site_ec_items')." WHERE (acc_id='".$acc_id."') ";
				$result = mysql_query($sql);
				if (mysql_num_rows($result)>0){
					$row_check = mysql_fetch_array($result);
					$item_id = $row_check['id']; 
					//die('update');
					//update
					$sql = "UPDATE ".$modx->getFullTableName('site_ec_items')." SET folder_code='$code', parent=$folder_id, 1c_code='$code2', 
						acc_id='$acc_id', sku='$sku', pagetitle='$pagetitle',retail_price='$retail_price',template='$ec_settings[def_model]',
						published=0,createdon=".time().",createdby=".$modx->getLoginUserID().", producer='$producer', 
						vendor='$vendor', country='$country',  size='$size', retail_size='$retail_sizes_str', color='$color', composition='$composition', material='$material',
						price_opt='$price_opt', package_items='$package_items', package_price='$package_price'  WHERE acc_id='".$acc_id."'";//die($sql);
					$rs = mysql_query($sql);
					if ($rs) $updated++;	
					else echo "<br>ERROR: " . mysql_error() . "<br><br>";
				}
				else{
					//die('insert');
					//insert
					$sql = "INSERT INTO ".$modx->getFullTableName('site_ec_items')."(folder_code, parent, 1c_code, acc_id, sku, pagetitle,retail_price,template,published,sell,createdon,createdby, producer, vendor, country, size, retail_size, composition, material, color, price_opt, package_items, package_price) ";
					$sql.= "VALUES('$code',$folder_id, '$code2','$acc_id', '$sku', '$pagetitle','$retail_price','$ec_settings[def_model]',0,'$ec_settings[def_sell]',".time().",".$modx->getLoginUserID().",'$producer','$vendor','$country','$size','$retail_sizes_str','$composition','$material','$color','$price_opt','$package_items','$package_price')";
					$rs = mysql_query($sql);				
					if ($rs) $added++;
					else echo(mysql_error());
					$item_id = mysql_insert_id();					
				}
				
				##
				if($rs && $item_id){
					$sql = "DELETE FROM ".$modx->getFullTableName('site_ec_prices')." WHERE item_id='$item_id'";
					mysql_query($sql);	
					//set opt prices				
					foreach($sizes as $i => $size_item){

						if (floatval($opt_prices[$i]) != $price_opt){
							$price_value = floatval($opt_prices[$i]);
							
							setPrice($item_id, 'opt', $size_item, $price_value);
						}
						if (floatval($package_prices[$i]) != $package_price){
							$price_value = floatval($package_prices[$i]); 
							
							setPrice($item_id, 'package', $size_item, $price_value);
						}						
								
					}
					//set retail prices
					foreach($retail_sizes as $i => $size_item){

						if (floatval($retail_prices[$i]) != $retail_price){
							$price_value = floatval($retail_prices[$i]); 
							
							setPrice($item_id, 'retail', $size_item, $price_value);
						}					
								
					}
					
					//set colors
					$sql = "DELETE FROM ".$modx->getFullTableName('site_ec_colors')." WHERE item_id='$item_id'";
					mysql_query($sql);
					
					$colors = parseColors($color);	
					foreach((array)$colors as $itemColor){
						if($itemColor['code'] && $itemColor['name']){
							$sql = "INSERT INTO ".$modx->getFullTableName('site_ec_colors')." (item_id, code, name) 
								VALUES ('$item_id', '$itemColor[code]', '$itemColor[name]')";
								
							mysql_query($sql);
						}
					}
																				
				}
				
												
								
			}
		}	
		fclose($fp);		
		$error == '';		
	} else {
		$uploadError = true;
		$upload_errors = $fu->message;
	} 
	
	if ($error == '' && $uploadError === false) {
		$output .= '<h2>' . $_lang['1C_update_done'].'<br>';
		$output .= $_lang['1C_added'].' '.$added.' '.$_lang['1C_items'].'';
		$output .= 'Обновлено '.$updated.' '.$_lang['1C_items'].'';
		$output .= '</h2>';	
	} else {
		$output .= '<h2>' . $_lang[18] . '</br>';				
		foreach ($upload_errors as $upload_error) {
			$output.= ''.$upload_error.'</br>';
		}
		$output.= '</h2>';	
	}	
	resetSale();
	return $output.$errors;
}


function upload1CFolders() {
	global $theme;
	global $modx,$ec_settings;
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


		$root_folder_id = 5;
		$tv_id = 56;		
		$i = 0;
		$added = 0;
		$updated = 0;
		$acc_ids = array();	
		// updating tree.	
		while(!feof($fp)) {
			$line = fgets($fp);		
			if (!stristr($line,"#"))
				continue;	
			$row = explode("#",$line);
			if (count($row) != 2) {
				$output .= '<h2>' . $_lang['1C_update_wrong_format'].'</h2>';				
				return $output;
				break;
			}
			$codes = $row[0];
			$folder_title = $row[1];
			$code_arr = explode("/",$codes);
			
			if (count($code_arr)>1) $code = $code_arr[count($code_arr)-1];//береться последний
			else $code = $codes;
						
			$code = trim($code);		
			if (!empty($code) ) {
				$pagetitle =  mysql_escape_string(trim($folder_title));				
				$sql = "SELECT * FROM ".$modx->getFullTableName('site_tmplvar_contentvalues');
				$sql.= " WHERE value = '$code' AND tmplvarid='$tv_id'";					
			
				echo "<br>".$sql."<br>";
				$rs = mysql_query($sql);
				$limit = mysql_num_rows($rs);					
				if($limit>0) {
					//если уже есть страница с таким кодом, то обновляем у нее название
					$item = mysql_fetch_assoc($rs);
					$contentid = $item['contentid'];
					
					
					$sql = "UPDATE ".$modx->getFullTableName('site_content')."SET "; 
					$sql.= "pagetitle = '$pagetitle' WHERE id = '$contentid' LIMIT 1;";	
					echo "<br>".$sql."<br>";			
					$rs = mysql_query($sql);				
						
					if ($rs) $updated++;							  
				} else {//если нет, то добавляем						
					$introtext = '';
					$content = '';
					$pagetitle = $pagetitle; //replace apostrophes with ticks :(
					$description = '';
					$alias = '';
					$link_attributes = '';
					$isfolder = 1;
					$richtext = 0;
					$published = 1;
					
					$parent = $root_folder_id;
					$template = 8;
					$menuindex = 0;
					$searchable = 1;
					$cacheable = 0;
					$syncsite = 1;
					$pub_date = 0;
					$unpub_date = 0;
					$document_groups = 0;
					$type = 'document';
					$keywords = 0;
					$metatags = 0;
					$contentType = 'text/html';
					$contentdispo = 0;
					$longtitle = $pagetitle;
					$donthit = 0;
					$menutitle = '';
					$hidemenu = 0;						
					
					$sql = "INSERT INTO ".$modx->getFullTableName('site_content')." (introtext,content, pagetitle, longtitle, type, description, alias, link_attributes, isfolder, richtext, published, parent, template, menuindex, searchable, cacheable, createdby, createdon, editedby, editedon, publishedby, publishedon, pub_date, unpub_date, contentType, content_dispo, donthit, menutitle, hidemenu)
						    VALUES('" . $introtext . "','" . $content . "', '" . $pagetitle . "', '" . $longtitle . "', '" . $type . "', '" . $description . "', '" . $alias . "', '" . $link_attributes . "', '" . $isfolder . "', '" . $richtext . "', '" . $published . "', '" . $parent . "', '" . $template . "', '" . $menuindex . "', '" . $searchable . "', '" . $cacheable . "', '" . $modx->getLoginUserID() . "', " . time() . ", '" . $modx->getLoginUserID() . "', " . time() . ", " . $modx->getLoginUserID() . ", " . time() . ", '$pub_date', '$unpub_date', '$contentType', '$contentdispo', $donthit, '$menutitle', $hidemenu)";
					echo "<br>".$sql."<br>";
					$rs = mysql_query($sql);		
					$contentid = mysql_insert_id();
					
					$sql = "UPDATE ".$modx->getFullTableName('site_ec_items')."SET "; 
					$sql.= "parent = $contentid WHERE folder_code = '$code';";	
					echo "<br>".$sql."<br>";			
					$rs = mysql_query($sql);								 
					
					$sql = "INSERT INTO ".$modx->getFullTableName('site_tmplvar_contentvalues')." (tmplvarid,contentid, value)
						    VALUES(" . $tv_id . "," .$contentid . ", '" . $code . "')";
					if ($rs) {
						echo "<br>".$sql."<br>";
						$rs1 = mysql_query($sql);
						if ($rs1) $added++;
					}
											
				} 				
				if (!empty($code) && !empty($contentid)) $ids[$code] = $contentid;			
			}
		}
		
		// building parent relation.
		//var_dump($ids);
		fseek($fp, 0);
		while(!feof($fp)) {
			$line = fgets($fp);	
			if (!stristr($line,"#"))
				continue;					
			$row = explode("#",$line);
			$codes = $row[0];
			$folder_title = $row[1];
			$code_arr = explode("/",$codes);			
			if (count($code_arr)>1) {
				$code = trim($code_arr[count($code_arr)-1]);
				$parent_code = trim($code_arr[count($code_arr)-2]);
				$id = isset($ids[$code]) ? $ids[$code] : 0;
				$parent = isset($ids[$parent_code]) ? $ids[$parent_code] : 0;
			} else { 
				$code = trim($codes);
				$id = isset($ids[$code]) ? $ids[$code] : 0;
				$parent = $root_folder_id;				
			}			
			
			if (($id != 0 && $parent != 0) ) {										
				$sql = "UPDATE ".$modx->getFullTableName('site_content')."SET "; 
				$sql.= "parent = '$parent',editedon='".time()."',editedby='".$modx->getLoginUserID()."' WHERE id = '$id' LIMIT 1;";	
				echo "<br>".$sql."<br>";			
				$rs = mysql_query($sql); 			 									
			}
		}
		
		fseek($fp, 0);
		while(!feof($fp)) {
			$line = fgets($fp);	
			if (!stristr($line,"#"))
				continue;					
			$row = explode("#",$line);
			$codes = $row[0];
			$folder_title = $row[1];
			$code_arr = explode("/",$codes);
			
			if (count($code_arr)>1) {
				$code = trim($code_arr[count($code_arr)-1]);
				$parent_code = trim($code_arr[count($code_arr)-2]);
				$id = isset($ids[$code]) ? $ids[$code] : 0;
				$parent = isset($ids[$parent_code]) ? $ids[$parent_code] : 0;
			} else { 
				$code = trim($codes);
				$id = isset($ids[$code]) ? $ids[$code] : 0;
				$parent = $root_folder_id;				
			}			
			if ($id != 0 && $parent != 0) {					
				$sql = "SELECT * FROM ".$modx->getFullTableName('site_content');
				$sql.= " WHERE parent= $id";
				echo "<br>".$sql."<br>";		
				$rs = mysql_query($sql);
				$limit = mysql_num_rows($rs);									
				if ($limit > 0) $isfolder = 1;	else $isfolder = 0;			
				$sql = "UPDATE ".$modx->getFullTableName('site_content')."SET "; 
				$sql.= "isfolder='$isfolder',editedon=".time().",editedby=".$modx->getLoginUserID()." WHERE id = $id LIMIT 1;";	
				echo "<br>".$sql."<br>";			
				$rs = mysql_query($sql); 			 									
			}
		}			
		fclose($fp);		
		$error == '';			
	} else {
		$uploadError = true;
		$upload_errors = $fu->message;
	} 
	
	if ($error == '' && $uploadError === false) {
		$output .= '<h2>' . $_lang['1C_update_done'].'<br>';
		$output .= $_lang['1C_added'].' '.$added.' '.$_lang['1C_items'].'';
		$output .= '</h2>';	
	} else {
		$output .= '<h2>' . $_lang[18] . '</br>';				
		foreach ($upload_errors as $upload_error) {
			$output.= ''.$upload_error.'</br>';
		}
		$output.= '</h2>';	
	}	
		
	return $output;
}


function upload1CPriceList() {
	global $theme;
	global $modx;
	global $_lang;
    global $_1c_data_dir;
	global $_FILES,$ec_settings;
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
		$updated = 0;
		
		resetItemsCount();
		
		while(!feof($fp)) {
			$line = fgets($fp);			
			$row = explode("#",$line);
			if (count($row) >20) {
				$output .= '<h2>' . $_lang['1C_update_wrong_format'].'</h2>';				
				return $output;
			}
			
			$code2= trim($row[1]);

			if (!empty($code2)) {	
			
				$country=  !empty($row[3]) ? mysql_escape_string($row[3]) : '';
				$acc_id =  !empty($row[2]) ? mysql_escape_string($row[2]) : '';
	
				$pagetitle =  !empty($row[5]) ? mysql_escape_string($row[5]) : '';				
			
				
			$producer =  !empty($row[4]) ? mysql_escape_string($row[4]) : '';
				$vendor =  !empty($row[6]) ? mysql_escape_string($row[6]) : '';
			
				$size =  !empty($row[7]) ? mysql_escape_string($row[7]) : '';
				//$growth =  !empty($row[8]) ? mysql_escape_string($row[8]) : '';
				$composition=  !empty($row[10]) ? mysql_escape_string($row[10]) : '';
				$material =  !empty($row[9]) ? mysql_escape_string($row[9]) : '';
				$color =  !empty($row[11]) ? mysql_escape_string($row[11]) : '';
					//$retail_price = !empty($row[12]) ? floatval($row[12]) : 0;	
				//$price_opt = !empty($row[13]) ? floatval($row[13]): 0;
				
				##
				$sizes_str = $row[7];
				$sizes = explode(",", $sizes_str);
				
				$retail_prices_str = $row[12];

				$retail_prices = explode(",", $retail_prices_str);
				$retail_price = floatval($retail_prices[0]); 
				
				$opt_prices_str = $row[13];
				$opt_prices = explode(",", $opt_prices_str);
				$price_opt = floatval($opt_prices[0]);
								
			$sku =  !empty($row[14]) ? floatval($row[14]): 0;
			
			$package_items =  !empty($row[15]) ? (int)($row[15]): 0;
			
				$package_prices_str = $row[16];
				$package_prices = explode(",", $package_prices_str);
				$package_price = floatval($package_prices[0]);			
			
				$retail_sizes_str = $row[17];
				$retail_sizes = explode(",", $retail_sizes_str);		
			
			
		
				$sql = "UPDATE ".$modx->getFullTableName('site_ec_items')."SET "; 
				$sql.= "sku=$sku, color='$color', price_opt=$price_opt, size='$size', retail_size='$retail_sizes_str', retail_price=$retail_price,package_items=$package_items,package_price=$package_price, editedon=".time().",editedby=".$modx->getLoginUserID()." ";//DEL growth='$growth', , size='$size' 
				$sql.= "WHERE acc_id='$acc_id';";				
				$rs = mysql_query($sql);
				if ($rs) $updated++;
							##
				/*if($acc_id==2035){
					die($sql);
				}	*/			
				##
				$sql = "SELECT * FROM ".$modx->getFullTableName('site_ec_items')." WHERE (acc_id='".$acc_id."') ";
				$result = mysql_query($sql);
				$row_check = mysql_fetch_array($result);
				$item_id = $row_check['id'];
				
				if($rs && $item_id){
					$sql = "DELETE FROM ".$modx->getFullTableName('site_ec_prices')." WHERE item_id='$item_id'";
					mysql_query($sql);					
					//set opt prices				
					foreach($sizes as $i => $size_item){

						if (floatval($opt_prices[$i]) != $price_opt){
							$price_value = floatval($opt_prices[$i]);
							
							setPrice($item_id, 'opt', $size_item, $price_value);
						}
						if (floatval($package_prices[$i]) != $package_price){
							$price_value = floatval($package_prices[$i]); 
							
							setPrice($item_id, 'package', $size_item, $price_value);
						}						
								
					}
					//set retail prices
					foreach($retail_sizes as $i => $size_item){

						if (floatval($retail_prices[$i]) != $retail_price){
							$price_value = floatval($retail_prices[$i]);
							
							setPrice($item_id, 'retail', $size_item, $price_value);
						}					
								
					}
					
					//set colors
					$sql = "DELETE FROM ".$modx->getFullTableName('site_ec_colors')." WHERE item_id='$item_id'";
					mysql_query($sql);
					
					$colors = parseColors($color);	
					foreach((array)$colors as $itemColor){
						if($itemColor['code'] && $itemColor['name']){
							$sql = "INSERT INTO ".$modx->getFullTableName('site_ec_colors')." (item_id, code, name) 
								VALUES ('$item_id', '$itemColor[code]', '$itemColor[name]')";
								
							mysql_query($sql);
						}
					}				
				}
								
			}
		}		
		fclose($fp);		
		$error == '';		
	} else {
		$uploadError = true;
		$upload_errors = $fu->message;
	} 
	
	if ($error == '' && $uploadError === false) {
		$output .= '<h2>' . $_lang['1C_update_done'].'<br>';
		$output .= $_lang['1C_updated'].' '.$updated.' '.$_lang['1C_items'].'';
		$output .= '</h2>';	
	} else {
		$output .= '<h2>' . $_lang[18] . '</br>';				
		foreach ($upload_errors as $upload_error) {
			$output.= ''.$upload_error.'</br>';
		}
		$output.= '</h2>';	
	}	
	resetSale();	
	return $output;
}

function upload1COrders() {
	global $theme;
	global $modx;
	global $_lang;
    global $_1c_data_dir;
	global $_FILES,$ec_settings;
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
		$updated = 0;
		while(!feof($fp)) {
			$line = fgets($fp);			
			$row = explode("#",$line);
			if (count($row) != 3 ) {
				$output .= '<h2></h2>';				
				return $output;
			}
			
			$order_id = $row[0];
			$order_id = trim($order_id);
			
			$sql = "SELECT * FROM ".$modx->getFullTableName('site_ec_orders')." WHERE id = '$order_id' LIMIT 1";					
			$rs = mysql_query($sql);
			$limit = mysql_num_rows($rs);					
			if($limit===1) {
				$status = $row[1];
				$status = trim($status);
				
				$paid = $row[2];
				$paid = trim($paid);
				
				$order = mysql_fetch_assoc($rs);
				$paid = $order['paid'];
				
				if ($order['informcust'] == 1 && $status == 6 && $order['status']!=6) {
					include_once $modx->config['base_path']."assets/snippets/ecart/ecart.inc.php";
					$ec = new eCart();
					$ec->init();
					//$ec->sendOrderSentMessage($order);
					
			$sql5 = "SELECT * FROM ".$modx->getFullTableName('ec_settings')." WHERE setting_name = 'ec_email_order_done_mgs' LIMIT 1";					
			$rt = mysql_query($sql5);	
			$text = mysql_fetch_assoc($rt);
			$message = $text['setting_value'];
		
		
		
		$cust_name = $order['customer_fname'].' '.$order['customer_sname'].' '.$order['customer_lname'];				
		$message = str_replace('[+uname+]', $cust_name, $message);
		
		
		$order['order_date'] = datetime($order['order_date']);	
		$message = str_replace('[+order_fdate+]', $order['order_date'], $message);
		$message = str_replace('[+id+]', $order_id, $message);
	
		$email = $order['customer_email'];
		
$headers  = "Content-type: text/html; charset=windows-1251 \r\n";
$headers .= "From: orders@chcl.ru\r\n";

mail("$email", "", "$message",  $headers);
			 
			 
					
					
					
				}		
				if ($status==7) { $paid=1;}					
				$sql = " UPDATE ".$modx->getFullTableName('site_ec_orders')." SET "; 
				$sql.= " status='$status', paid='$paid' WHERE id='$order_id'; ";				
				$rs = mysql_query($sql);
				if ($rs) $updated++;			
			}			
			
		}		
		fclose($fp);		
		$error == '';		
	} else {
		$uploadError = true;
		$upload_errors = $fu->message;
	} 
	
	if ($error == '' && $uploadError === false) {
		$output .= '<h2>' . $_lang['1C_update_done'].'<br>';
		$output .= $_lang['1C_updated'].' '.$updated.' '.$_lang['1C_orders'].'';
		$output .= '</h2>';	
	} else {
		$output .= '<h2>' . $_lang[18] . '</br>';				
		foreach ($upload_errors as $upload_error) {
			$output.= ''.$upload_error.'</br>';
		}
		$output.= '</h2>';	
	}	
	
	return $output;
}

function uploadImages() {
	global $theme;
	global $modx;
	global $_lang;
    global $_1c_data_dir;
	global $_FILES,$ec_settings;
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
	$new_name = '1c_images';
	if ($fu->upload($new_name)) { 
		$full_path = $fu->upload_dir.$fu->file_copy;
		$uploadError = false;		
		$fp = fopen($full_path, "r");
		$total_quantity = 0;
		$model_quantity = 0;
		$updated = 0;
		
		$images_dir = $_POST['images_dir'];
		
		// tovar image tv var id
		$tvIds = array(40, 41, 42, 50, 51);
		
		while(!feof($fp)) {
			$line = fgets($fp);			
			$row = explode("#",$line);
			//print_r($row);die();
			/*if (count($row) > 2) {
				$output .= '<h2>' . $_lang['1C_update_wrong_format'].'</h2>';				
				return $output;
			}*/
			
			$code2 = $row[0];
			$acc_id = $row[1];	
			$images = $row[2];
			
			if($images && $code2){

				$items = array();
				
				$imgArr = explode(',', $images);
				
				// get item id
				$sql = "SELECT id FROM `modx_site_ec_items` WHERE (1c_code='$code2')";
				$result = $modx->db->query($sql);
				while($rowsql = $modx->db->getRow($result)){
					$items[] = $rowsql['id']; 
				}
				
				foreach($items as $itemid){
					$modx->db->query("DELETE FROM `modx_site_tmplvar_ec_itemvalues` WHERE (tmplvarid IN (".implode(',', $tvIds).") AND itemid = '$itemid')");
					
					for($i = 0; $i < count($imgArr); $i++){
						$image = trim($imgArr[$i]);
						$value = $images_dir . $image;
						
						$tvId = $tvIds[$i]; 
						
						if($tvId && $value){
							$sql = "INSERT INTO `modx_site_tmplvar_ec_itemvalues` (tmplvarid, itemid, value) VALUES ('$tvId', '$itemid', '$value')";
							$result = $modx->db->query($sql);
						}
					}
					$updated++;	
				}
			}
		}		
		fclose($fp);		
		$error == '';		
	} else {
		$uploadError = true;
		$upload_errors = $fu->message;
	} 
	
	if ($error == '' && $uploadError === false) {
		$output .= '<h2>' . $_lang['1C_update_done'].'<br>';
		$output .= $_lang['1C_updated'].' '.$updated.' '.$_lang['1C_items'].'';
		$output .= '</h2>';	
	} else {
		$output .= '<h2>' . $_lang[18] . '</br>';				
		foreach ($upload_errors as $upload_error) {
			$output.= ''.$upload_error.'</br>';
		}
		$output.= '</h2>';	
	}	
		
	return $output;
}

function resetSale(){
	global $modx;
	$modx->db->query("UPDATE `modx_site_ec_items` SET package_price='0', package_items='0' WHERE parent=3119");
}

function parseColors($color){
	$color= array_map('trim', explode(",", $color));
	
	$return = array();
	
	foreach($color as $colorItem){
		preg_match('#^(.+?)\s*\((.+?)\)#is', $colorItem, $match);
		
		$return[] = array(
			'code' => $match[1],
			'name' => $match[2]
		);
	}

	return $return; 	
}

if ($_REQUEST['a'] == 5101) {
   	$upload_msg3 = upload1CPricelist();
}   
if ($_REQUEST['a'] == 5102) {
	$upload_msg2 = upload1CItems();
}	
if ($_REQUEST['a'] == 5103) {
	$upload_msg1 = upload1CFolders();
}	
if ($_REQUEST['a'] == 5104) {
	$upload_msg4 = upload1COrders();
}	
if ($_REQUEST['a'] == 5105) {
	$upload_msg5 = uploadImages();
}
?>
<script type="text/javascript">
</script>
<br>
<div class="sectionHeader"><?php echo $_lang["ec_manage_1c"]; ?></div>
<div class="sectionBody">

   		
   		<?php   		
   		
   		$output_str  =  '<br /><p>' . $_lang['1C_upload_folders_form'] . '</p>';
		$output_str .= $upload_msg1;				   		
		$output_str .= ' 
					<form action="index.php?a=5103" name=\'upload_data\' method="POST" enctype="multipart/form-data"> 
					<input type="hidden" name="tabAction" value="_1c_upload_data" /> 										
					<input name="upload_file" type="file" size="60"/>';	
		$output_str .= '<div class="go" style="margin-top:10px;">
					<input type="submit" value="' . $_lang['1C_go'] . '"/>
					</div></form><br><div class="stay"></div>';
   		
   		$output_str .=  '<br /><p>' . $_lang['1C_upload_data_form'] . '</p>';
		$output_str .= $upload_msg2;			   		
		$output_str .= ' 
					<form action="index.php?a=5102" name=\'upload_data\' method="POST" enctype="multipart/form-data"> 
					<input type="hidden" name="tabAction" value="_1c_upload_data" /> 						
					<input name="upload_file" type="file" size="60"/>';	
		$output_str .= '<div class="go" style="margin-top:10px;">
					<input type="submit" value="' . $_lang['1C_go'] . '"/>
					</div></form><br><div class="stay"></div>';
		
		
		$output_str .=  '<br /><p>' . $_lang['1C_upload_pricelist_form'] . '</p>';
   		$output_str .= $upload_msg3;		   		
		$output_str .= ' 
					<form action="index.php?a=5101" name=\'upload_data\' method="POST" enctype="multipart/form-data"> 
					<input type="hidden" name="tabAction" value="_1c_upload_data" /> 						
					<input name="upload_file" type="file" size="60"/>';	
		$output_str .= '<div class="go" style="margin-top:10px;">
					<input type="submit" value="' . $_lang['1C_go'] . '"/>
					</div></form><br>';	
		
		
		$output_str .=  '<br /><p>' . $_lang['1C_upload_orders_form'] . '</p>';
   		$output_str .= $upload_msg4;		   		
		$output_str .= ' 
					<form action="index.php?a=5104" name=\'upload_data\' method="POST" enctype="multipart/form-data"> 
					<input type="hidden" name="tabAction" value="_1c_upload_data" /> 						
					<input name="upload_file" type="file" size="60"/>';	
		$output_str .= '<div class="go" style="margin-top:10px;">
					<input type="submit" value="' . $_lang['1C_go'] . '"/>
					</div></form><br>';	
		
		##upload images
		$output_str .=  '<div class="stay"></div><br /><p><b>Выберите (txt) файл импорта, чтобы загрузить фотографии товаров</b></p>';
   		$output_str .= $upload_msg5;		   		
		$output_str .= ' 
					<form action="index.php?a=5105" name=\'upload_data\' method="POST" enctype="multipart/form-data"> 
					<input type="hidden" name="tabAction" value="_1c_upload_data" /> 						
					<input name="upload_file" type="file" size="60"/>
					<div style="margin-top:10px;">Каталог с изображениями: <input name="images_dir" size="60" type="text" value="assets/images/tovars/" /></div>';	
		$output_str .= '<div class="go" style="margin-top:10px;">
					<input type="submit" value="' . $_lang['1C_go'] . '"/>
					</div></form><br>';
		
		echo $output_str;   		

   		?>
</div>
