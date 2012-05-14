<?php
/*
::::::::::::::::::::::::::::::::::::::::
 Snippet name: Wayfinder
 Short Desc: builds site navigation
 Version: 2.0
 Authors: 
	Kyle Jaebker (muddydogpaws.com)
	Ryan Thrash (vertexworks.com)
 Date: February 27, 2006
::::::::::::::::::::::::::::::::::::::::
*/

class eCList {
	var $params;
	var $config;	
	var $templates;	
	var $lang = array();
	var $phs = array(
		'tvs' => array()
		);
	var $itemNeighbours = array();
  	
		
	function init() {
		global $modx;
		$this->itemCount = $this->getListItemCount();
		//$this->setTVList();	
		$this->loadConfig();		
	}
	
	function loadConfig() {
		global $modx;
		$sql = "SELECT setting_name, setting_value FROM ".$modx->getFullTableName("ec_settings");
		$rs = mysql_query($sql);
		$number_of_ec_settings = mysql_num_rows($rs);
		while ($row = mysql_fetch_assoc($rs)) {
			$this->config[$row['setting_name']] = $row['setting_value'];
		}			
	}
	
	function buildList($start = 0, $stop = 0) {
		global $modx;		
		$output = '';		
		$rows = '';		
		$cells = '';	
		$messageTpl = $this->getTemplate($this->templates['message1Tpl']);
		$outerTpl = $this->getTemplate($this->templates['outerTpl']);
		$rowTpl = $this->getTemplate($this->templates['rowTpl']);
		$cellTpl = $this->getTemplate($this->templates['cellTpl']);	
		$pagerTpl = $this->getTemplate($this->templates['pagerTpl']);		
		$list_items = $this->getListItems($start, $stop);//print_r($list_items);die();
				
		if (count($list_items) == 0) {
			$message = str_replace('[+message+]',$this->lang[0], $messageTpl);				
			return $outerTpl.$message;
		}						
		
		if ($this->params['columns'] == 1) {						 	
			foreach($list_items as $item) {	
				$row = $rowTpl;			
				
				if ($item['retail_price'] != 0 && $item['sell'] == 1) 
				$ok_to_sell = 1;
				else 
				$ok_to_sell = 0;				
		
				$row = str_replace('[+ok_to_sell+]', $ok_to_sell, $row);
				
				
				
			    foreach($item as $k => $v) {		
				

					    	
					$row = str_replace('[+'.$k.'+]', $v, $row);	
					

								
				} 
				
				
				
				$row = str_replace('[+itemscreenshotshomeid+]',$this->params['itemscreenshotshomeid'], $row);
				$row = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $row);
				if (isset($_SESSION['rozn'])&&$_SESSION['rozn']==1) $rozn=1; else $rozn=0;
				$row = str_replace('[+rozn+]', $rozn, $row);	
				
				$rows .= $row;														
			}					 
			$output = str_replace('[+ecl.wrapper+]',$rows, $outerTpl);	
			$output = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $output);			
		} else {
			if (isset($_SESSION['rozn'])&&$_SESSION['rozn']==1) $rozn=1; else $rozn=0;
			
			$columns = intval($this->params['columns']);
			$size = (count($list_items) > $columns) ? ceil(count($list_items)/$columns) : 1;	
			$l = 0;
			$item_ids = array_keys($list_items);
			for($j=0; $j<$size; $j++) {
				for($i=0; $i<$columns; $i++) {
					$cell = $cellTpl;			
					$cell_item = @$list_items[@$item_ids[$l]];
					$l++;		
					if (is_array($cell_item)) {
						##
						$prices = getPrices($cell_item['id'], 'opt');
						if($prices){
							if($rozn)
								$allprices = array($cell_item['retail_price']);
							else
								$allprices = array($cell_item['price_opt']);
							$min_price = 0;
							$max_price = 0;
							foreach($prices as $size_item => $price_item){
								$allprices[] = $price_item;
							}
							
							sort($allprices);
							
							$min_price = $allprices[0];
							$max_price = $allprices[count($allprices)-1];

							if($rozn)
								$cell = str_replace('[+fretail_price+]', "$min_price - $max_price", $cell);
							else
								$cell = str_replace('[+price_opt+]', "$min_price - $max_price", $cell);
						}
						
												
						foreach($cell_item as $k => $v) {						
							$cell = str_replace('[+'.$k.'+]', $v, $cell);
						} 
						$cell = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $cell);
						
						$cells .= $cell;
					}
				}				
				$rows .= str_replace('[+ecl.wrapper+]',$cells, $rowTpl);				
				$cells = '';
			}
			//echo $l;	
			$output = str_replace('[+ecl.wrapper+]',$rows, $outerTpl);						
		}		
		if (sizeof($this->pager)>0) {
			foreach($this->pager as $k => $v) {						
				$pagerTpl = str_replace('[+'.$k.'+]', $v, $pagerTpl);
			} 
			$output = str_replace('[+ecl.pager+]',$pagerTpl, $output);
		}		
		
		
		if (isset($_SESSION['rozn'])&&$_SESSION['rozn']==1) $rozn=1; else $rozn=0;
	    $output = str_replace('[+rozn+]', $rozn, $output);	
	    
	    
		return $output;
	}
	
	function buildItemScreenshots($id) {
		global $modx;	
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);
		$itemTpl = $this->getTemplate($this->templates['itemScreenshotsTpl']);		
		$item_ = $this->getItem($id);		
		if ($item_ == false) {		
			$url = $modx->makeUrl($modx->config['site_start']);		
			$modx->sendRedirect($url,0,'REDIRECT_HEADER');			
		}					
		$output = $itemTpl;	
		$item = current($item_);	
		foreach($item as $k => $v) {			 	
			$output = str_replace('[+'.$k.'+]', $v, $output);
			
		}			
		//
		$modx->setPlaceholder('itemtitle',$item['pagetitle']);
        $modx->setPlaceholder('metatags',$metas);
		$output = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $output);			
		return $output;		
	}
	
	function buildItemMovie($id) {
		global $modx;	
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);
		$itemTpl = $this->getTemplate($this->templates['itemScreenshotsTpl']);		
		$item_ = $this->getItem($id);		
		if ($item_ == false) {		
			$url = $modx->makeUrl($modx->config['site_start']);		
			$modx->sendRedirect($url,0,'REDIRECT_HEADER');			
		}					
		$output = $itemTpl;	
		$item = current($item_);	
		foreach($item as $k => $v) {			 	
			$output = str_replace('[+'.$k.'+]', $v, $output);
		}			
		  $modx->setPlaceholder('itemtitle',$item['pagetitle']);
        $modx->setPlaceholder('metatags',$metas);
		//
		$output = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $output);			
		return $output;		
	}
	
	function buildItemAccessories($ids) {
		global $modx;		
		$output = '';		
		$rows = '';		
		$cells = '';	
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);
		$outerTpl = $this->getTemplate($this->templates['accOuterTpl']);
		$rowTpl = $this->getTemplate($this->templates['accRowTpl']);
		$cellTpl = $this->getTemplate($this->templates['accCellTpl']);			
		$list_items = $this->getItemAccessories($ids);				
		if (count($list_items) == 0) {
			$message = str_replace('[+message+]',$this->lang[1], $messageTpl);		
			return $message;
		}								
		if ($this->params['columns'] == 1) {			
			foreach($list_items as $item) {	
				$row = $rowTpl;		
					
				if ($item['retail_price'] != 0 && $item['sell'] == 1) 
				$ok_to_sell = 1;
				else 
				$ok_to_sell = 0;				
		
				$row = str_replace('[+ok_to_sell+]', $ok_to_sell, $row);	
					
			    foreach($item as $k => $v) {			    	
					$row = str_replace('[+'.$k.'+]', $v, $row);	
									
				} 
				$rows .= $row;													
			}					 
			$output = str_replace('[+ecl.wrapper+]',$rows, $outerTpl);	
			$output = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $output);			
		} else {
			$columns = intval($this->params['columns']);
			$size = (count($list_items) > $columns) ? ceil(count($list_items)/$columns) : 1;	
			$a = 0;			
			for($j=0; $j<$size; $j++) {
				for($i=0; $i<$columns; $i++) {
					$cell = '';
					foreach($list_items[$a] as $k => $v) {						
						$cell = str_replace('[+ecl.'.$k.'+]', $v, $cellTpl);
					} 
					$a++;
					$cells .= $cell;
				}				
				$rows .= str_replace('[+ecl.wrapper+]',$cells, $rowTpl);				
				$cells = '';
			}	
			$output = str_replace('[+ecl.wrapper+]',$rows, $outerTpl);	
			$output = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $output);
			$output = str_replace('[+itemscreenshotshomeid+]',$this->params['itemscreenshotshomeid'], $output);			
								
		}			
		foreach($this->tvList as $tvName) {						
			$output = str_replace("[+$tvName+]", "", $output);
		} 
		return $output;
	}
	
	function buildItem($id) {		
		global $modx;	
		
		$canvote = true;
		$voted = false;
		
		if (isset($_REQUEST['itemvote']) && isset($_REQUEST['itemid'])) {
			$rating = intval($_REQUEST['itemvote']);
			if (isset($_COOKIE['cssStarRating' . $id])) {
				$canvote = false;	
				$dublevoted = true;
			} elseif ($rating > 0) {
				$sql = "UPDATE ".$modx->getFullTableName("site_ec_items")." SET votes = votes+1,rating=rating+$rating WHERE id = $id LIMIT 1";	        				$run = $modx->dbQuery($sql);	
				$ovtime = 60*60*24;		
				setcookie('cssStarRating' . $id, 'novote', time() + $ovtime);
				$canvote = false;
				$voted = true;
			} else {
				$canvote = true;	
				$selectrate = true;
			}
		}	
		
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);
		$itemTpl = $this->getTemplate($this->templates['itemTpl']);	
		$similarItemRowTpl = $this->getTemplate($this->templates['similarItemRowTpl']);	
		$item_ = $this->getItem($id);		
		
		if ($item_ == false) {		
			$url = $modx->makeUrl($modx->config['site_start']);		
			$modx->sendRedirect($url,0,'REDIRECT_HEADER');			
		}					
		
		$output = $itemTpl;		
		$item = current((array)$item_);//print_r($item);die();
		if(!is_array($item)){
			$url = $modx->makeUrl($modx->config['site_start']);		
			$modx->sendRedirect($url,0,'REDIRECT_HEADER');			
		}
		
		## get prev and next
		if(!$this->params['parents'] && $item['parent']){
			$this->params['parents'] = $item['parent']; 
		}
		if($_REQUEST['type']=='search'){
			$search = $this->processSearchText();//print_r($search);die();
			$this->getSearchResultsCount($search);
			$result = $this->getSearchResults($search, 0 ,0, true); //print_r($result);die();
		}
		else{
			$result = $this->getListItems(0, 0, true);
		}
		
		$prevItemId = 0;
		
		while($row = $modx->fetchRow($result)){
			$item_id = $row['id'];
			
			if($item_id==$id){
				$prev_id = $prevItemId;
				$row = $modx->fetchRow($result);
				$next_id = $row['id'] ? $row['id'] : 0;
				break;
			}
		
			$prevItemId = $item_id;
		}		
		
		$output = str_replace('[+previd+]', $prev_id, $output);
		$output = str_replace('[+nextid+]', $next_id, $output);

		
		$color = $item['color'];
		$size= $item['size'];
		
			$color1 = $item['color'];
		$size1= $item['size'];
		
		$retail_size1 = $item['retail_size'];
		$retail_size = 	array_map('trim', explode(",", $retail_size1));
		$count_retail_size = count($retail_size);

				//$color= array_map('trim', explode(",", $color));
				$size= array_map('trim', explode(",", $size));

                $count_color = count($color);
				$count_size = count($size);
				
		$c = '<ul>';
		$ci = 1;
		
		$colors = $color->get();
		foreach($colors as $colorItem){
			//preg_match('#^(.+?)\s*\((.+?)\)#is', $colorItem, $match);
			
			$cclass = $ci==1 ? 'act' : '';
			
			$c .= '<li rel="'.$colorItem['name'].'" class="'.$cclass.'" style="background-color: #'.$colorItem['code'].';" title="'.$colorItem['name'].'">'.$colorItem['name'].'</li>';
			
			$ci++;
		}
		$c .= '</ul>';		
/*				
$c='<select name="items[0][color_z]">'; 
$i=0;
               for ($i=0; $i<$count_color; $i++)
               {$c.='<option value="'.$color[$i].'">'.$color[$i].'</option>'; }

	$c.='</select>';
*/	
	$s='<select name="items[0][size_z]">'; 
               for ($i=0; $i<$count_retail_size; $i++)
               {$s.='<option value="'.$retail_size[$i].'">'.$retail_size[$i].'</option>'; }

	$s.='</select>';
	
		
		$output = str_replace('[+color+]', $c, $output);
		
		$output = str_replace('[+retail_size+]', $s, $output);
		
		$output = str_replace('[+color1+]', $color1, $output);
		
		$output = str_replace('[+size1+]', $size1, $output);
		
		$output = str_replace('[+retail_size1+]', $retail_size1, $output);
		
		
		if (isset($_SESSION['rozn'])&&$_SESSION['rozn']==1) $rozn=1; else $rozn=0;//die("ROZN:".$_SESSION['rozn']);
	    $output = str_replace('[+rozn+]', $rozn, $output);	

	    ## opt table
	    ##XXX
	    /*if($item['parent']==3134){
	    	$item['package_items'] = $item['package_price'] = 0;	
	    }*/
	    
	    if($item['package_items'] > 0)
	    	$countName = "упаковок";
	    else
	    	$countName = "единиц";
	    $opt_table = '<table width="100%" class="opt-table"><tr><th>Размер</th><th>Цвет</th><th>Количество<br>'.$countName.'</th><th>Стоимость</th></tr>';
	    $n_opt = 0;
	    $i = 0;
	    
		$prevSize = '';
		$_colors = count($colors) ? $colors : array(array('name' => ''));
    	foreach($size as $size_name){
    		foreach($_colors as $colorItem){
    			$color_name = $colorItem['name'];
				
	    		$i++;
	    		if ($i % 2)
	    			$css_tr = "opt_row1";
	    		else
	    			$css_tr = "opt_row2";
	    		$opt_table .= '
	    		<tr class="'.$css_tr.'">';
	    		
				if($size_name != $prevSize){
					$opt_table .= '
	    				<td rowspan="'.count($_colors).'" align="center">'.$size_name.'</td>';
				}
	    		
				$opt_table .= '	
	    			<td align="center" valign="middle">
	    				<div class="color-item '.(!$color_name ? "nocolor" : "").'" title="'.($color_name ? $color_name : "Выбор цвета недоступен").'" style="background-color: #'.$colorItem['code'].';">'.$color_name.'</div>
	    				<input type="hidden" name="items['.$n_opt.'][id]" value="'.$id.'" />
	    				<input type="hidden" name="items['.$n_opt.'][size_z]" value="'.$size_name.'" />
	    				<input type="hidden" name="items['.$n_opt.'][color_z]" value="'.$color_name.'" />
	    			</td>
	    			<td align="center">
	    				<input type="text" maxlength="3" value="0" class="cart-count" name="items['.$n_opt.'][quantity]" size="2" />
	    			</td>
	    			<td>
	    				<span id="cost_'.$n_opt.'">0</span> руб.
	    			</td>
	    		</tr>
	    		';
	    		$n_opt++;
				
				$prevSize = $size_name;
			}
    	}
		
		$opt_table .= '</table>';
		$output = str_replace('[+opt_table+]', $opt_table, $output);
		
		##
		/*if (isset($_SESSION['rozn'])&&$_SESSION['rozn']==1){
			$type="retail";
		}
		else{
			$type="opt";
			
			if($item['package_items'] > 0 && $type=="opt"){
				$type = "package";
			}			
		}*/		
		$prices = getPrices($id, 'opt');
		$retail_prices = getPrices($id, 'retail');
		if($item['package_items'] > 0){
			$pack_prices = getPrices($id, 'package');
		}
		if($prices){
			$by_prices = array();
			
			$prices_js = "0: 0";
			
			//$other_sizes = $size;//размеры для которых стандартная цена
			foreach($prices as $size_item => $price_item){
				$by_prices[$price_item][] = $size_item;
				
				$prices_js .= ", '$size_item': $price_item";
				//unset($other_sizes);					
			}
			$prices = "";
			foreach($by_prices as $price_item => $sizes){
				$sizes_str = implode(",", $sizes);
				$prices .= '<div>размеры '.$sizes_str.' - <span class="price">'.$price_item.'</span> руб.</div>';
			}
			
			
			$output = str_replace('[+prices_opt_str+]', $prices, $output);
			$output = str_replace('[+prices_opt+]', $prices_js, $output);
			
		}
		if($retail_prices){
			$prices = $retail_prices;
			$by_prices = array();
			
			//$prices_js = "0: 0";
			
			//$other_sizes = $size;//размеры для которых стандартная цена
			foreach($prices as $size_item => $price_item){
				$by_prices[$price_item][] = $size_item;
				
				//$prices_js .= ", '$size_item': $price_item";
			}
			$prices = "";
			foreach($by_prices as $price_item => $sizes){
				$sizes_str = implode(",", $sizes);
				$prices .= '<div>размеры '.$sizes_str.' - <span class="price">'.$price_item.'</span> руб.</div>';
			}
			
			
			$output = str_replace('[+prices_retail_str+]', $prices, $output);
			//$output = str_replace('[+prices_opt+]', $prices_js, $output);
			
		}		
		if($pack_prices){
			$package_prices_js = "0: 0";
			
			//$other_sizes = $size;//размеры для которых стандартная цена
			foreach($pack_prices as $size_item => $price_item){
				
				$package_prices_js .= ", '$size_item': $price_item";
				//unset($other_sizes);					
			}
						
			$output = str_replace('[+package_prices+]', $package_prices_js, $output);
		}
		
		//set images
		$images_list = '';
		$imagesLinks = array();
		for($i = 1; $i < 10; $i++)
		{
			$tvimage = $item['image'.$i];
			if($tvimage)
			{
				$imagesLinks[] = '<a href="'.$tvimage.'" class="cloud-zoom-gallery '.($i==1 ? 'zoomThumbActive' : '').'" rel="{gallery: \'tovar-zoom\', smallimage: \'[+phx:phpthumb=`w=300#'.$tvimage.'`+]\',largeimage: \''.$tvimage.'\'}"><i></i><img src="[+phx:phpthumb=`h=50#'.$tvimage.'`+]" /></a>';				
			}
		}
		$imagesItems = array_chunk($imagesLinks, 5);
		foreach($imagesItems as $imageItem)
		{
			$images_list .= '<div>'.implode('', $imageItem).'</div>';
		}
		
		$output = str_replace('[+images_list+]', $images_list, $output);
		
		foreach((array)$item as $k => $v) {	
		
	 	
			$output = str_replace('[+'.$k.'+]', $v, $output);
			
		
			
		}			
		
			$time_now= time();
				$timestamp=$item['date_issue']-3888000;
				
				if ($item['retail_price'] != 0 && $item['sell'] == 1)
				$ok_to_sell = 1; //пїЅ пїЅпїЅпїЅпїЅ
				elseif ($item['sell'] == 0 && ($time_now<$timestamp or $item['date_issue']<1)) 
				$ok_to_sell = 2; //пїЅпїЅпїЅпїЅпїЅ пїЅпїЅ пїЅпїЅпїЅпїЅпїЅ
				elseif ($item['sell'] == 0 && $time_now>=$timestamp && $time_now<=$item['date_issue'])
				$ok_to_sell = 3; //пїЅпїЅпїЅпїЅпїЅ пїЅ пїЅпїЅпїЅпїЅпїЅпїЅпїЅ
				
				else 
				$ok_to_sell = 0;			
		
		$output = str_replace('[+ok_to_sell+]', $ok_to_sell, $output);			
		$rows = '';		
		
		$similaritems = $this->getSimilarItemsList($item['id']/*$item['similaritems']*/);	        
		
		foreach($similaritems as $similaritem) {	
			$output1 = $similarItemRowTpl;
			foreach($similaritem as $k => $v) {			 	
				$output1 = str_replace('[+'.$k.'+]', $v, $output1);
			}		 	
			$rows .= $output1;
		}	
		
		$output = str_replace('[+similaritemsrows+]', $rows, $output);	

		//recommend
		$recommenditems = $this->getRecommendItemsList($item['recommenditems']);	        
		$rows = '';
		foreach($recommenditems as $recommenditem) {	
			$output1 = $similarItemRowTpl;
			foreach($recommenditem as $k => $v) {			 	
				$output1 = str_replace('[+'.$k.'+]', $v, $output1);
			}		 	
			$rows .= $output1;
		}	
		
		$output = str_replace('[+recommenditemsrows+]', $rows, $output);	
		
		
		$tags = $this->getMETATags($id);			
		$metas = '';
		
        foreach ($tags as $n => $col) {
        	$tag= strtolower($col['tag']);
            $tagvalue= $col['tagvalue'];
            $tagstyle= $col['http_equiv'] ? 'http-equiv' : 'name';
            $metas .= "<meta $tagstyle=\"$tag\" content=\"$tagvalue\" />\n";
        }			
        
        $modx->setPlaceholder('itemtitle',$item['pagetitle']);
        $modx->setPlaceholder('metatags',$metas);		
		
        if ($canvote) $output = str_replace('[+canvote+]',1, $output);
        if ($voted) $output = str_replace('[+voted+]',1, $output);     
        if ($selectrate) $output = str_replace('[+selectrate+]',1, $output); 
        if ($dublevoted) $output = str_replace('[+dublevoted+]',1, $output);        
        
        $output = str_replace('[+itemscreenshotshomeid+]',$this->params['itemscreenshotshomeid'], $output);
		$output = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $output);	

		$output = str_replace('[+activetab+]', $this->getActiveTab($item), $output);
		
		return $output;		
	}
	
	function getActiveTab($item){
		$tab = $_COOKIE['buytab'] ? $_COOKIE['buytab'] : 'opt-tab';
		
		if($tab=='opt-tab' && (!$item['price_opt'] || $item['price_opt']=='0.00')){
			$tab = 'retail-tab';
		}
		elseif($tab=='retail-tab' && (!$item['retail_price'] || $item['retail_price']=='0.00')){
			$tab = 'opt-tab';
		}
		return $tab;
	}
	
	function getMETATags($id= 0) {     
		global $modx;
        $sql = "SELECT * FROM " . $modx->getFullTableName("site_ec_item_metatags") ." WHERE item_id = '$id'";        
        $ds = $modx->db->query($sql);         
        $limit= $modx->db->getRecordCount($ds);
        $metatags= array ();
        if ($limit > 0) {
            for ($i= 0; $i < $limit; $i++) {
                $row= $modx->db->getRow($ds);
                $metatags[$row['name']]= array (
                    "tag" => $row['tag'],
                    "tagvalue" => $row['tagvalue'],
                    "http_equiv" => $row['http_equiv']
                );
            }
        }
        return $metatags;
    }
	
	function getListItemCount() {
		global $modx;
		if(isset($_POST['ucf_price']))	$price_1=$_POST['ucf_price'];
if(isset($_POST['ucf_price2'])) $price_2=$_POST['ucf_price2']; 
		$resourceArray = array();$tempResults =  array();		
		$fields = "count(id) as cnt";
	    $filter_sql =str_replace(':','=',$this->params['filter']);
	    $filter_price_sql =str_replace(':','=',$this->params['filter_price']);
	    $filter_price2_sql =str_replace(':','=',$this->params['filter_price2']);
	    $filter_gr_sql =str_replace(':','=',$this->params['filter_gr']);
	    $filter_gr2_sql =str_replace(':','=',$this->params['filter_gr2']);
	    
		
		if(isset($_POST['ucf_gr']) && $_POST['ucf_gr'])	$gr_1=2894; else $gr_1=0;
if(isset($_POST['ucf_gr2']) && $_POST['ucf_gr2']) $gr_2=2902; else $gr_2=0;





		
		//Get the table names
	    $tblsc = $modx->getFullTableName("site_ec_items");	         
		// build query
	    $sql = "SELECT {$fields} FROM {$tblsc} si WHERE published=1 AND deleted=0 ";
		
	  //  $sql.= !empty($this->params['parents']) ? " AND parent IN (".$this->params['parents'].")" : ""; 	
		
		if ($gr_1>0&&$gr_2=='0') 
	  { $sql.= !empty($this->params['parents']) ? " AND parent IN (".$this->getchild($gr_1).")" : "";  }
	  else if ($gr_2>0&&$gr_1=='0') 
	  { $sql.= !empty($this->params['parents']) ? " AND parent IN (".$this->getchild($gr_2).")" : ""; 	 }
	  	else
	   { $sql.= !empty($this->params['parents']) ? " AND parent IN (".$this->params['parents'].")" : ""; 	 }
		
		
	    $sql.= !empty($filter_sql) ? " AND {$filter_sql} " : " ";  
	    $sql.= (!empty($price_1)) ? "AND si.price_opt>$price_1  " : " ";
	    $sql.= (!empty($price_2)) ? "AND  si.price_opt<$price_2  " : " ";  
	    
	##
	$f_keys = array_keys($_POST);
	$fkey_exist = false;
	foreach($f_keys as $f_key){
		if ( preg_match('/^f\d+$/i', $f_key) ){
			$fkey_exist = true;
		}
	}
	    
	    if ($fkey_exist) {


$sql.= "AND (  ";
$i=1;
  for ($i=1; $i<50; $i++)
	  {
	  $a="f".$i; 
	  
	 if (isset($_POST[$a]))
	  {
	   $words=$_POST[$a]; 
	 $sql.=  " si.pagetitle LIKE '%$words%'  OR ";    
}


} 
	  	  
$sql.= "si.pagetitle='111111' ) ";

}
	    
	   
		$result = $modx->dbQuery($sql);	 //die($sql);       
		$numResults = @$modx->recordCount($result);
		if ($numResults == 1) {
			$row = $modx->fetchRow($result);			
			return $row['cnt'];
		} else return 0;
	}
	// search	
	function getSearchResultsCount($search) {
		global $modx;
		$resourceArray = array();$tempResults =  array();		
		//Get the table names
	    $tblsc = $modx->getFullTableName("site_ec_items");	         
		$tblsc1 = $modx->getFullTableName("site_tmplvar_ec_itemvalues");  

			
		$search_mode = '';
		$search_mode = 'WITH QUERY EXPANSION';
		$search_mode = 'IN BOOLEAN MODE';
		
		$search_arr  = str_word_count($search, 1);
		$search_words_count = count($search_arr);
		
		if (strlen($search) == 3 || $search_words_count = 1) {
			$sql = "SELECT id ";
	        $sql.= "FROM $tblsc WHERE ( pagetitle LIKE '%$search%' OR acc_id LIKE '%$search%' OR producer='$search' OR vendor='$search' OR country='$search' OR material='$search') AND published=1 AND deleted=0 ";
	        
	        $result1 = $modx->dbQuery($sql);	        
			$numResults1 = @$modx->recordCount($result1);		
			
	        $sql = "SELECT itemid ";
	        $sql.= "FROM $tblsc1 tv INNER JOIN $tblsc si ON tv.itemid = si.id WHERE si.published=1 AND si.deleted=0 AND tv.value LIKE '%$search%'";        
	        
	        $result2 = $modx->dbQuery($sql);	        
			$numResults2 = @$modx->recordCount($result2);
		} else {
		    $sql = "SELECT id, MATCH (pagetitle) AGAINST ('$search' $search_mode) AS score ";
	        $sql.= "FROM $tblsc WHERE MATCH (pagetitle) AGAINST ('$search' $search_mode) AND published=1 AND deleted=0 ";
	        
	        $result1 = $modx->dbQuery($sql);	        
			$numResults1 = @$modx->recordCount($result1);		
			
	        $sql = "SELECT itemid, MATCH (tv.value) AGAINST ('$search' $search_mode) AS score ";
	        $sql.= "FROM $tblsc1 tv INNER JOIN $tblsc si ON tv.itemid = si.id WHERE si.published=1 AND si.deleted=0 AND MATCH (tv.value) AGAINST ('$search' $search_mode)";        
	        
	        $result2 = $modx->dbQuery($sql);	        
			$numResults2 = @$modx->recordCount($result2);
		}
		$result_ids = array();
		
		for($i=0;$i<$numResults1;$i++)  {
			$temp = $modx->fetchRow($result1);
			$result_ids[] = $temp['id'];  	
		}
		
		for($i=0;$i<$numResults2;$i++)  {
			$temp = $modx->fetchRow($result2);
			if (!in_array($temp['itemid'],$result_ids)) $result_ids[] = $temp['itemid'];  	
		}
		
		$numResults = count($result_ids); 
		$this->searchResultIDs = implode(',',$result_ids);
		$this->searchResultsCount = count($result_ids);
		return $numResults;		
	}
		
	function buildSearch($search,$start = 0, $stop = 0) {
		//die($search);
		global $modx;		
		$output = '';		
		$rows = '';		
		$cells = '';	
		$messageTpl = $this->getTemplate($this->templates['messageTpl']);
		$outerTpl = $this->getTemplate($this->templates['searchOuterTpl']);
		$rowTpl = $this->getTemplate($this->templates['searchRowTpl']);
		$pagerTpl = $this->getTemplate($this->templates['pagerTpl']);
		
		
		$searchtxt = htmlspecialchars(stripslashes(@$_SESSION['searchtxt']));
		
		
		if (isset($_SESSION['search']['sort'])) $search_sort = $_SESSION['search']['sort']; else $search_sort = 'si.retail_price';	
		$outerTpl = str_replace('[+search.sort+]',$search_sort, $outerTpl);			
		
		if (strlen($search) < 3) {
			$message = str_replace('[+search_text+]', $searchtxt, $this->lang['min_word']);	
			$message = str_replace('[+message+]',$message, $messageTpl); 		
			$output = str_replace('[+search.message+]',$message, $outerTpl);
			$output = str_replace('[+search.text+]', $searchtxt, $output);		
			return $output;
		}	
		
		if (!empty($search)) $list_items = $this->getSearchResults($search,$start, $stop);
		
		if (empty($search)) {
			return $outerTpl;
		}		
		
		
		
		if (count($list_items) == 0) {
			$message = str_replace('[+search_text+]', $searchtxt, $this->lang['no_entries']);			
			$output = str_replace('[+search.message+]',$message, $outerTpl);
			$output = str_replace('[+search.text+]', $searchtxt, $output);		
			return $output;
		}					

		foreach($list_items as $item) {	
			//print_r($item);die();
			$row = $rowTpl;		

			##XXX
			if (isset($_SESSION['rozn'])&&$_SESSION['rozn']==1) $rozn=1; else $rozn=0;
			
			$prices = getPrices($item['id'], 'opt');
			if($prices){
				if($rozn)
					$allprices = array($item['retail_price']);
				else
					$allprices = array($item['price_opt']);
				$min_price = 0;
				$max_price = 0;
				foreach($prices as $size_item => $price_item){
					$allprices[] = $price_item;
				}
				
				sort($allprices);
				
				$min_price = $allprices[0];
				$max_price = $allprices[count($allprices)-1];

				if($rozn)
					$row = str_replace('[+fretail_price+]', "$min_price - $max_price", $row);
				else
					$row = str_replace('[+price_opt+]', "$min_price - $max_price", $row);
			}
			if($rozn)
				$row = str_replace('[+price_opt+]', "0.00", $row);
			else
				$row = str_replace('[+fretail_price+]', "0.00", $row);
				
		    foreach($item as $k => $v) {			    	
				$row = str_replace('[+'.$k.'+]', $v, $row);			
			} 
			$row = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $row);
			$rows .= $row;													
		}					 
		
		$output = str_replace('[+ecl.wrapper+]',$rows, $outerTpl);	
		$output = str_replace('[+itemscreenshotshomeid+]',$this->params['itemscreenshotshomeid'], $output);
		$output = str_replace('[+itemhomeid+]',$this->params['itemhomeid'], $output);			
		$output = str_replace('[+search.text+]',$searchtxt, $output);		
		$search_message = str_replace('[+found+]',$this->searchResultsCount,$this->lang['found_entries']);
		
		foreach($this->pager2 as $k => $v) {						
			$pagerTpl = str_replace('[+'.$k.'+]', $v, $pagerTpl);
		} 
		
		$output = str_replace('[+ecl.pager+]',$pagerTpl, $output);
		$output = str_replace('[+search.message+]',$search_message, $output);		
		if (sizeof($this->pager2)>0) {
			foreach($this->pager2 as $k => $v) {						
				$pagerTpl = str_replace('[+'.$k.'+]', $v, $pagerTpl);
			} 
			$output = str_replace('[+ecl.pager+]',$pagerTpl, $output);
		}			
		return $output;
	}
	
	function processSearchText() {
		global $modx;
		$search = '';
		
		if (isset($_REQUEST['searchtxt']) && !empty($_REQUEST['searchtxt'])) {
			$_SESSION['searchtxt'] = $_REQUEST['searchtxt'];
		}
		
		if (isset($_REQUEST['searchsort']) && !empty($_REQUEST['searchsort'])) {
			$_SESSION['search']['sort'] = $_REQUEST['searchsort'];	
					
		} else {
			$_SESSION['search']['sort'] = 'score';	
		}	
		
		if (isset($_SESSION['searchtxt'])) {
			$search = $_SESSION['searchtxt'];
		}	
		
		return $search;
	}
	
	function getSearchResults($search,$start = 0,$stop = 0,$onlyresult = 0) {		
		global $modx;
		$resourceArray = array();$tempResults =  array();		
		$fields = "si.*,sc.pagetitle as parenttitle";
		//Get the table names

			
	    if (strtolower($this->params['sort']) == 'random') {
			$sort = 'rand()';
			$dir = '';
		} else {
			// modify field names to use  table reference
			$sort = $this->params['sort'];
			$dir = $this->params['dir'];
		}

		switch (@$_SESSION['search']['sort']) {
			
			case 'score': {
				$search_sort = '';
			};break;
			
			case 'si.retail_price': {
				$search_sort = 'si.retail_price,';
			};break;
			
			case '': {
				$search_sort = 'si.rating DESC,';
			};break;
			
			default: {
				$search_sort = '';
			}; break;
			
		}
		

		// build query
	    $search_txt = $modx->db->escape($search);	    
		if (!($start >= 0 && $stop >= 0)) return array();
	    $sql = "SELECT $fields FROM ".$modx->getFullTableName("site_ec_items")." si LEFT JOIN ";
	    $sql.= $modx->getFullTableName("site_content")." sc  ON si.parent = sc.id ";
	    //$sql.= $modx->getFullTableName("site_ec_brands")." sb ON si.brand_id = sb.id  LEFT JOIN ";
	    //$sql.= $modx->getFullTableName("site_ec_packs")." sp ON si.pack_id = sp.id ";
	    $sql.= "WHERE si.id IN ($this->searchResultIDs) ";	    
        $sql.= "ORDER BY $search_sort $sort $dir ";
		$sql.= ($start == 0 && $stop == 0) ? " " : " LIMIT $start, $stop;";		
		
		if (!empty($this->searchResultIDs)) {
			//die($sql);
			$result = $modx->dbQuery($sql);		      
			$numResults = @$modx->recordCount($result);
		} else {
			$numResults = 0;
		}
		##
		if($onlyresult)
			return $result;
					
		$resultIds = array();		
		//loop through the results
		for($i=0;$i<$numResults;$i++)  {
			$_item = array();
			$tempDocInfo = $modx->fetchRow($result);
			
			$tempDocInfo['fretail_price'] = money1($tempDocInfo['retail_price']); 
			
			if ($this->config['is_mdealer_price_active'] == '0') $tempDocInfo['fmdealer_price'] = '0.00';
			else $tempDocInfo['fmdealer_price'] = money1($tempDocInfo['mdealer_price']);
			
			if ($this->config['is_dealer_price_active'] == '0') $tempDocInfo['fdealer_price'] = '0.00';
			else $tempDocInfo['fdealer_price'] = money1($tempDocInfo['dealer_price']);
			
			$tempDocInfo['fsku'] = quantity($tempDocInfo['sku']);
			$resultIds[] = $tempDocInfo['id'];				
			$tempResults[$tempDocInfo['id']] = $tempDocInfo;						
	    }
	    unset($result);
		//Process the tvs
		$resourceArray = $this->appendTVs($tempResults,$resultIds);
		$resourceArray = $this->appendColors($resourceArray,$resultIds);
		//var_dump($resourceArray);
        return $resourceArray;
	}	
	
	// end search
	
	function getItem($id) {		
		global $modx;
		$resourceArray = array();
		$tempResults =  array();		
		$fields = "*";		
	    //Get the table names
	    $tblsc = $modx->getFullTableName("site_ec_items");					       
	    // build query
	    $sql = "SELECT si.*,sc.pagetitle as parenttitle FROM "; 
	    $sql.= $modx->getFullTableName("site_ec_items")." si LEFT JOIN ";
	    $sql.= $modx->getFullTableName("site_content")." sc  ON si.parent = sc.id ";
	    //$sql.= $modx->getFullTableName("site_ec_brands")." sb ON si.brand_id = sb.id  LEFT JOIN ";
	    //$sql.= $modx->getFullTableName("site_ec_packs")." sp ON si.pack_id = sp.id ";
	    $sql.= "WHERE si.published=1 AND si.deleted=0 AND si.id={$id} LIMIT 1";	      
		//echo $sql;
		
		$result = $modx->dbQuery($sql);				
		$numResults = @$modx->recordCount($result);
		if ($numResults != 1) return  false;
		$resultIds = array();		
		//loop through the results
		$tempDocInfo = $modx->fetchRow($result);
		
		//XXX отключение розницы
		if((int)$tempDocInfo['price_opt']){
			$tempDocInfo['retail_price'] = 0;
		}
		
		$tempDocInfo['fretail_price'] = money1($tempDocInfo['retail_price']); 
		
		if ($this->config['is_mdealer_price_active'] == '0') $tempDocInfo['fmdealer_price'] = '0.00';
		else $tempDocInfo['fmdealer_price'] = money1($tempDocInfo['mdealer_price']);
		
		if ($this->config['is_dealer_price_active'] == '0') $tempDocInfo['fdealer_price'] = '0.00';
		else $tempDocInfo['fdealer_price'] = money1($tempDocInfo['dealer_price']);
		
		$tempDocInfo['fsku'] = quantity($tempDocInfo['sku']);
		$resultIds[] = $tempDocInfo['id'];				
		$tempResults[$tempDocInfo['id']] = $tempDocInfo;	    
		
		$resourceArray = $this->appendTVs($tempResults,$resultIds);
		
		$resourceArray = $this->appendColors($resourceArray, $resultIds);
		
		//$resourceArray = $resourceArray[$tempDocInfo['id']];	
		//print_r($resourceArray);die();	
        return $resourceArray;
	}

	function appendColors($tempResults, $docIDs){
		global $modx;
		
		$ids = implode($docIDs,",");
		if ($ids == '') return $tempResults;
		
		$sql = "SELECT * FROM ".$modx->getFullTableName('site_ec_colors')." WHERE (item_id IN (".$ids."))";//die($sql);
		$result = $modx->db->query($sql);
		
		$colorsTemp = array();
		
		while($row = $modx->db->getRow($result)){
			$colorsTemp[$row['item_id']][] = $row;
		}		
		
		$results = array();
		
		foreach($tempResults as $id => $item){
			$item['color'] = new Colors($id, $colorsTemp[$id]);
			$results[$id] = $item;
		}
		unset($tempResults);
		
		return $results;
	}
	
	// ---------------------------------------------------
	// Function: appendTV taken from Ditto (thanks Mark)
	// Apeend a TV to the documents array
	// ---------------------------------------------------	
	//Get all of the documents from the database
	function getSimilarItemsList($ids) {		
		global $modx;
		$itemid = $ids;
		$sql = "SELECT si.*,sc.pagetitle as parenttitle FROM modx_site_ec_items si
				LEFT JOIN modx_site_content sc ON si.parent = sc.id
				WHERE(si.parent=(SELECT parent FROM modx_site_ec_items WHERE id=$itemid LIMIT 0,1) AND si.published=1 AND si.deleted=0) ORDER by RAND() LIMIT 0,4";
		$result = $modx->dbQuery($sql);	
		
		/*if (empty($ids)) return array();
		$resourceArray = array();$tempResults =  array();		
		$fields = "*";
	    $sql = "SELECT si.*,sc.pagetitle as parenttitle FROM "; 
	    $sql.= $modx->getFullTableName("site_ec_items")." si LEFT JOIN ";
	    $sql.= $modx->getFullTableName("site_content")." sc  ON si.parent = sc.id ";
	    $sql.= "WHERE si.id IN ($ids) AND si.published=1 AND si.deleted=0 ";
	    $sql.= " GROUP BY si.id ORDER BY si.pagetitle ";
		$result = $modx->dbQuery($sql);*/		      
		$numResults = @$modx->recordCount($result);		
		if (!$numResults > 0) return $resourceArray;		
		$resultIds = array();		
		//loop through the results
		for($i=0;$i<$numResults;$i++)  {
			$tempDocInfo = $modx->fetchRow($result);							
			$tempResults[$tempDocInfo['id']] = $tempDocInfo;

			$resultIds[] = $tempDocInfo['id'];
	    }
	    $resourceArray = $tempResults;
	    unset($result);		
	    
	    $resourceArray = $this->appendTVs($resourceArray,$resultIds);
		$resourceArray = $this->appendColors($resourceArray,$resultIds);
	    
		return $resourceArray;
	}	

	function getRecommendItemsList($ids) {		
		global $modx;
		
		if (empty($ids)) return array();
		$resourceArray = array();$tempResults =  array();		
		$fields = "*";
	    $sql = "SELECT si.*,sc.pagetitle as parenttitle FROM "; 
	    $sql.= $modx->getFullTableName("site_ec_items")." si LEFT JOIN ";
	    $sql.= $modx->getFullTableName("site_content")." sc  ON si.parent = sc.id ";
	    $sql.= "WHERE si.id IN ($ids) AND si.published=1 AND si.deleted=0 ";
	    $sql.= " GROUP BY si.id ORDER BY si.pagetitle ";
		$result = $modx->dbQuery($sql);		      
		$numResults = @$modx->recordCount($result);		
		if (!$numResults > 0) return $resourceArray;		
		$resultIds = array();		
		//loop through the results
		for($i=0;$i<$numResults;$i++)  {
			$tempDocInfo = $modx->fetchRow($result);							
			$tempResults[$tempDocInfo['id']] = $tempDocInfo;

			$resultIds[] = $tempDocInfo['id'];
	    }
	    $resourceArray = $tempResults;
	    unset($result);		
	    
	    $resourceArray = $this->appendTVs($resourceArray,$resultIds);
		$resourceArray = $this->appendColors($resourceArray,$resultIds);
	    
		return $resourceArray;
	}	
	
	function getchild ($id_t)
	
	{
	global $modx;
$childs = $id_t;

  $deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id_t, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id_t;
if ($showinmenu == 1) {
    $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id IN(".$childs.") AND hidemenu=0 AND deleted=0 AND published=1";   
    $result = $modx->dbQuery($sql);           
    $numResults = @$modx->recordCount($result);    
    $child_ = array();       
    for($i=0;$i<$numResults;$i++)  {
        $row = $modx->fetchRow($result);
        $child_[] = $row['id'];
    }
    $childs = implode(',',$child_);
}

return $childs;

}
	
	
	
	function getListItems($start = 0,$stop = 0, $onlyresult = 0) {	
		global $modx;
		$resourceArray = array();$tempResults =  array();		
		$fields = "*";
		
	    //Get the table names
	    if (strtolower($this->params['sort']) == 'random') {
			$sort = 'rand()';
			$dir = '';
		} else {
			// modify field names to use  table reference
			$sort = $this->params['sort'];
			$dir = $this->params['dir'];
		}				       
	    // build query
		if (!($start >= 0 && $stop >= 0)) return array();
	    $filter_sql =str_replace(':','=',$this->params['filter']);
	    
	    	$filter_price_sql =str_replace(':','=',$this->params['filter_price']);
	    	
	$filter_price2_sql =str_replace(':','=',$this->params['filter_price2']);
	
	$filter_gr_sql =str_replace(':','=',$this->params['filter_gr']);
	    $filter_gr2_sql =str_replace(':','=',$this->params['filter_gr2']);

if(isset($_POST['ucf_price']))	$price_1=$_POST['ucf_price'];
if(isset($_POST['ucf_price2'])) $price_2=$_POST['ucf_price2'];

if(isset($_POST['ucf_gr']) && $_POST['ucf_gr'])	$gr_1=2894; else $gr_1=0;
if(isset($_POST['ucf_gr2']) && $_POST['ucf_gr2']) $gr_2=2902; else $gr_2=0;





		   
	    $sql = "SELECT si.*,sc.pagetitle as parenttitle,si.rating/si.votes as rate FROM "; 
	    //$sql = "SELECT si.*,sc.pagetitle as parenttitle, sb.name as brand_name,sp.name as pack_name FROM "; 
	    $sql.= $modx->getFullTableName("site_ec_items")." si LEFT JOIN ";
	    $sql.= $modx->getFullTableName("site_content")." sc  ON si.parent = sc.id ";
	    //$sql.= $modx->getFullTableName("site_ec_brands")." sb ON si.brand_id = sb.id ";
	    //$sql.= $modx->getFullTableName("site_ec_packs")." sp ON si.pack_id = sp.id ";
		
	
	   //die("GR1:".$gr_1);
	  if ($gr_1>0&&$gr_2=='0') 
	  { $sql.= "WHERE ".(!empty($this->params['parents']) ? " si.parent IN (".$this->getchild($gr_1).") AND " : ""); }
	  else if ($gr_2>0&&$gr_1=='0') 
	  { $sql.= "WHERE ".(!empty($this->params['parents']) ? " si.parent IN (".$this->getchild($gr_2).") AND " : ""); }
	  	else
	   { $sql.= "WHERE ".(!empty($this->params['parents']) ? " si.parent IN (".$this->params['parents'].") AND " : ""); }
	   //die($sql);
	

   
	 
		
		
	    $sql.= " si.published=1 AND si.deleted=0";
	    $sql.= !empty($filter_sql) ? " AND {$filter_sql} " : " ";
	    $sql.= (!empty($price_1)) ? "AND si.price_opt>$price_1  " : " ";
	    $sql.= (!empty($price_2)) ? "AND  si.price_opt<$price_2  " : " ";
	   //$sql.= (!empty($gr_1)) ? "AND si.parent IN (".$this->getchild($gr_1).")  " : " ";
//$sql.= (!empty($gr_2)) ? "AND si.parent IN (".$this->getchild($gr_2).")  " : " ";

$f_keys = array_keys($_POST);
$fkey_exist = false;
foreach($f_keys as $f_key){
	if ( preg_match('/^f\d+$/i', $f_key) ){
		$fkey_exist = true;
	}
}

	    
if ($fkey_exist == true) {


$sql.= "AND (  ";
$i=1;
  for ($i=1; $i<50; $i++)
	  {
	  $a="f".$i; 
	  //echo $a . "\r\n";
	 if (isset($_POST[$a]))
	  {
	   $words=$_POST[$a]; 
	 $sql.=  " si.pagetitle LIKE '%$words%'  OR ";    
}


} 
	//die();  	  
$sql.= "si.pagetitle='111111' ) ";

}

	    $sql.= " ORDER BY {$sort} {$dir} ";
		$sql.= ($start == 0 && $stop == 0) ? " " : " LIMIT {$start}, {$stop};";
		//die($sql);
		$result = $modx->dbQuery($sql);		      
		$numResults = @$modx->recordCount($result);//die("NUM".$numResults);
		$resultIds = array();		
		
		##
		if($onlyresult)
			return $result; 
		//$sql_brandes = "SELECT id,name FROM ".$modx->getFullTableName("site_ec_brands");
		
		//loop through the results
		for($i=0;$i<$numResults;$i++)  {
			$_item = array();
			$tempDocInfo = $modx->fetchRow($result);
			$tempDocInfo['fretail_price'] = money1($tempDocInfo['retail_price']);
						
			if ($this->config['is_mdealer_price_active'] == '0') $tempDocInfo['fmdealer_price'] = '0.00';
			else $tempDocInfo['fmdealer_price'] = money1($tempDocInfo['mdealer_price']);
			
			if ($this->config['is_dealer_price_active'] == '0') $tempDocInfo['fdealer_price'] = '0.00';
			else $tempDocInfo['fdealer_price'] = money1($tempDocInfo['dealer_price']);				
			
			$tempDocInfo['fsku'] = quantity($tempDocInfo['sku']);
			$resultIds[] = $tempDocInfo['id'];				
			$tempResults[$tempDocInfo['id']] = $tempDocInfo;						
	    }
	    unset($result);	    
		//Process the tvs		
		$resourceArray = $this->appendTVs($tempResults,$resultIds);
		$resourceArray = $this->appendColors($resourceArray,$resultIds);
		unset($tempResults);
		//echo '<pre>';
		//var_dump($resourceArray);
  		//echo '</pre>';		
		//exit;
  		//var_dump($resourceArray);
        return $resourceArray;
	}	
	
	function getItemAccessories($ids) {		
		global $modx;
		$resourceArray = array();$tempResults =  array();		
		$fields = "*";
		
	    //Get the table names
	    $tblsc = $modx->getFullTableName("site_ec_items");	       
		if (strtolower($this->params['sort']) == 'random') {
			$sort = 'rand()';
			$dir = '';
		} else {
			// modify field names to use  table reference
			$sort = $this->params['sort'];
			$dir = $this->params['dir'];
		}				       
	    // build query
	    $sql = "SELECT {$fields} FROM {$tblsc} WHERE published=1 AND deleted=0 ";
	    $sql.= " AND id IN (".$ids.")"; 
	    $sql.= " ORDER BY {$this->params['accsort']} {$this->params['accdir']}";
		//echo  $sql;
		//echo $sql;
		$result = $modx->dbQuery($sql);		      
		$numResults = @$modx->recordCount($result);
		$resultIds = array();		
		//loop through the results
		for($i=0;$i<$numResults;$i++)  {
			$_item = array();
			$tempDocInfo = $modx->fetchRow($result);
			$tempDocInfo['fretail_price'] = money1($tempDocInfo['retail_price']); 
			$tempDocInfo['fdealer_price'] = money1($tempDocInfo['dealer_price']);
			$tempDocInfo['fsku'] = quantity($tempDocInfo['sku']);
			$resultIds[] = $tempDocInfo['id'];				
			$tempResults[] = $tempDocInfo;						
	    }
	    unset($result);
		if (!empty($this->tvList) && !empty($resultIds)) {
			$tvValues = array();
			//loop through all tvs and get their values for each document
			
			foreach ($this->tvList as $tvName) {				
				$tvValues = array_merge_recursive($this->appendTV($tvName,$resultIds),$tvValues);				
			}
		    //loop through the document array and add the tvar values to each document		
			foreach ($tempResults as $tempDocInfo) {
				if (array_key_exists("#{$tempDocInfo['id']}",$tvValues)) {
					foreach ($tvValues["#{$tempDocInfo['id']}"] as $tvName => $tvValue) {							
						$tempDocInfo[$tvName] = $tvValue;
					}
				}
				$resourceArray[] = $tempDocInfo;
				unset($resultIds);
				unset($tempResults);
			}
		} else {				
			$resourceArray = $tempResults;
		}	
		//var_dump($resourceArray);
        return $resourceArray;
	}	
		
	function appendTVs($tempResults,$docIDs){
		global $modx;		
		if (implode($docIDs,",") == '') return $tempResults;
		$baspath= $modx->config["base_path"] . "manager/includes";
	    include_once $baspath . "/tmplvars.format.inc.php";
	    include_once $baspath . "/tmplvars.commands.inc.php";
		$tb1 = $modx->getFullTableName("site_tmplvar_ec_itemvalues");		
		$tb2 = $modx->getFullTableName("site_tmplvars");		
		$query = "SELECT stv.name,stc.tmplvarid,stc.itemid,stv.type,stv.display,stv.display_params,stc.value";
		$query .= " FROM ".$tb1." stc LEFT JOIN ".$tb2." stv ON stv.id=stc.tmplvarid  ";
		$query .= " WHERE stc.itemid IN (".implode($docIDs,",").")";
		$rs = $modx->db->query($query);
		$tot = $modx->db->getRecordCount($rs);		
		$tvlist = $this->getTVList();		
		foreach ($tvlist as $tv) {
			foreach ($tempResults as $id => $item) {
				$tempResults[$id][$tv] = '';
			}
		}		
		$resourceArray = $tempResults;
		for($i=0;$i<$tot;$i++)  {			
			$row = @$modx->fetchRow($rs);
			if (!empty($row['value']))
			$resourceArray[$row['itemid']][$row['name']] = getTVDisplayFormat($row['name'], $row['value'], $row['display'], $row['display_params'], $row['type'],$row['itemid']);   
			else 
			$resourceArray[$row['itemid']][$row['name']] = getTVDisplayFormat($row['name'], $row['default_text'], $row['display'], $row['display_params'], $row['type'],$row['itemid']);	
			$tv_names[$row['itemid']][] = $row['name'];				
		}		
		return $resourceArray;
	}	
	// ---------------------------------------------------
	// Get a list of all available TVs
	// ---------------------------------------------------		
	//debugging to check for valid chunks
    function getTemplate($v) {
        global $modx;		
		$template = $this->fetch($v);
        return $template; 				
    }
	//if (!empty($nonZCartFields)) {
	//	$nonZCartFields = array_unique($nonZCartFields);
	function setTVList() {
		$allTvars = array();
		$allTvars = $this->getTVList();
		foreach ($allTvars as $field) {
			$this->placeHolders['tvs'][] = "[+{$field}+]";
			$this->tvList[] = $field;
		}		
    }
	// ---------------------------------------------------
	// Function: getTVList
	// Get a list of all available TVs
	// ---------------------------------------------------
	function getTVList() {
		global $modx;
		$table = $modx->getFullTableName("site_tmplvars");
		$tvs = $modx->db->select("name", $table);
		// TODO: make it so that it only pulls those that apply to the current template
		$dbfields = array();
		while ($dbfield = $modx->db->getRow($tvs))
			$dbfields[] = $dbfield['name'];
		return $dbfields;
	}
	
	function fetch($tpl){
		// based on version by Doze at http://modxcms.com/forums/index.php/topic,5344.msg41096.html#msg41096
		global $modx;
		$template = "";
		if ($modx->getChunk($tpl) != "") {
			$template = $modx->getChunk($tpl);
		} else if(substr($tpl, 0, 6) == "@FILE:") {
			$template = $this->get_file_contents(substr($tpl, 6));
		} else if(substr($tpl, 0, 6) == "@CODE:") {
			$template = substr($tpl, 6);
		} else {
			$template = FALSE;
		}
		return $template;
	}

	function get_file_contents($filename) {
		// Function written at http://www.nutt.net/2006/07/08/file_get_contents-function-for-php-4/#more-210
		// Returns the contents of file name passed
		if (!function_exists('file_get_contents')) {
			$fhandle = fopen($filename, "r");
			$fcontents = fread($fhandle, filesize($filename));
			fclose($fhandle);
		} else	{
			$fcontents = file_get_contents($filename);
		}
		return $fcontents;
	}
	
	function findTemplateVars($tpl) {
		preg_match_all('~\[\+(.*?)\+\]~', $tpl, $matches);
		$cnt = count($matches[1]);
				
		$tvnames = array ();
		for ($i = 0; $i < $cnt; $i++) {
			if (strpos($matches[1][$i], "zc.") === FALSE) {
				$tvnames[] =  $matches[1][$i];
			}
		}

		if (count($tvnames) >= 1) {
			return array_unique($tvnames);
		} else {
			return false;
		}
	}
	
	function buildURL($args,$id=false,$dittoIdentifier=false) {
		global $modx;//, $dittoID;
			$dittoID = '';//($dittoIdentifier !== false) ? $dittoIdentifier : $dittoID;
			$query = array();
			foreach ($_GET as $param=>$value) {
				if ($param != 'id' && $param != 'q') {
					$query[htmlspecialchars($param, ENT_QUOTES)] = htmlspecialchars($value, ENT_QUOTES);					
				}
			}
			if (!is_array($args)) {
				$args = explode("&",$args);
				foreach ($args as $arg) {
					$arg = explode("=",$arg);
					$query[$dittoID.$arg[0]] = urlencode(trim($arg[1]));
				}
			} else {
				foreach ($args as $name=>$value) {
					$query[$dittoID.$name] = urlencode(trim($value));
				}
			}
			$queryString = "";
			foreach ($query as $param=>$value) {
				$queryString .= '&'.$param.'='.(is_array($value) ? implode(",",$value) : $value);
			}
			$cID = ($id !== false) ? $id : $modx->documentObject['id'];
			$url = $modx->makeURL(trim($cID), '', $queryString);
			return str_replace("&","&amp;",$url);
	}
	
	function relToAbs($text, $base) {
		return preg_replace('#(href|src)="([^:"]*)(?:")#','$1="'.$base.'$2"',$text);
	}
	
	function paginate($start, $stop, $total, $pagerlinkcount, $summarize, $paginateAlwaysShowLinks, $tplPaginateNext, $tplPaginatePrevious,$paginateSplitterCharacter) {
		global $modx;
		if ($stop == 0 || $total == 0 || $summarize==0) {
			return false;
		}
		
		$next = $start + $summarize;
		$nextlink = "<a  onclick='document.filter_form.action=\"".$this->buildURL("start=$next")."\";document.filter_form.submit();'>" . $tplPaginateNext . "</a>";
		$nextlink2 = "<a href='".$this->buildURL("start=$next")."'>" . $tplPaginateNext . "</a>";
		$previous = $start - $summarize;
		$previouslink = "<a   onclick='document.filter_form.action=\"".$this->buildURL("start=$previous")."\";document.filter_form.submit();'>" . $tplPaginatePrevious . "</a>";
		$previouslink2 = "<a href='".$this->buildURL("start=$previous")."'>" . $tplPaginatePrevious . "</a>";
		$limten = $summarize + $start;
		if ($paginateAlwaysShowLinks == 1) {
			$previousplaceholder = "<span class='ditto_off'>" . $tplPaginatePrevious . "</span>";
			$nextplaceholder = "<span class='ditto_off'>" . $tplPaginateNext . "</span>";
		} else {
			$previousplaceholder = "";
			$nextplaceholder = "";
		}
		
			
	
	

	
		
		
		$split = "";
		if ($previous > -1 && $next < $total)
			$split = $paginateSplitterCharacter;
		if ($previous > -1)
			$previousplaceholder = $previouslink;
			$previousplaceholder2 = $previouslink2;
		if ($next < $total)
			$nextplaceholder = $nextlink;
			$nextplaceholder2 = $nextlink2;
		if ($start < $total)
			$stop = $limten;
		if ($limten > $total) {
			$limiter = $total;
		} else {
			$limiter = $limten;
		}
				
		$totalpages = ceil($total / $summarize);
		
		if ($pagerlinkcount >= $totalpages) {
			for ($x = 0; $x <= $totalpages -1; $x++) {
				$inc = $x * $summarize;
				$display = $x +1;
				if ($inc != $start) {
					$pages .= "<a class=\"ditto_page\"  onclick='document.filter_form.action=\"".$this->buildURL("start=$inc")."\";document.filter_form.submit();'>$display</a>";
					$pages2 .= "<a class=\"ditto_page\" href='".$this->buildURL("start=$inc")."'>$display</a>";
				} else {
					$modx->setPlaceholder($dittoID."currentPage", $display);
					$pages .= "<span class=\"ditto_currentpage\">$display</span>";
					$pages2 .= "<span class=\"ditto_currentpage\">$display</span>";
				}
			}
		} else {
			
			$side = ($pagerlinkcount-1)/2;
			$curpage = ceil($start / $summarize)+1;	 
			
			
					
			if (($curpage + $side) <= $totalpages && ($curpage - $side) >= 1) {
				$from = $curpage-$side-1;
				$till = $curpage+$side-1;
			} elseif (($curpage + $side) > $totalpages) {
				$from = $curpage-$side-(($curpage + $side)-($totalpages - 1));
				$till = $totalpages - 1;
			} else {
				$from = 0;
				$till = $pagerlinkcount-1;
			}
			
			for ($x = $from; $x <= $till; $x++) {
				$inc = $x * $summarize;
				$display = $x +1;
				if ($inc != $start) {
					$pages .= "<a class=\"ditto_page\" onclick='document.filter_form.action=\"".$this->buildURL("start=$inc")."\";document.filter_form.submit();'>$display</a>";
					$pages2 .= "<a class=\"ditto_page\" href='".$this->buildURL("start=$inc")."'>$display</a>";
				} else {
					$modx->setPlaceholder($dittoID."currentPage", $display);
					$pages .= "<span class=\"ditto_currentpage\">$display</span>";
					$pages2 .= "<span class=\"ditto_currentpage\">$display</span>";
				}
			}			
		}	
		
		$pager["next"] = $nextplaceholder;
		$pager["previous"] = $previousplaceholder;
		$pager["splitter"] = $split;
		$pager["start"] = $start + 1;
		$pager["urlStart"] = $start;
		$pager["stop"] = $limiter;
		$pager["total"] = $total;
		$pager["pages"] = $pages;
		$pager["perPage"] = $summarize;
		$pager["totalPages"] = $totalpages;
		$this->pager = $pager;
		
		
		$pager2["next"] = $nextplaceholder2;
		$pager2["previous"] = $previousplaceholder2;
		$pager2["splitter"] = $split;
		$pager2["start"] = $start + 1;
		$pager2["urlStart"] = $start;
		$pager2["stop"] = $limiter;
		$pager2["total"] = $total;
		$pager2["pages"] = $pages2;
		$pager2["perPage"] = $summarize;
		$pager2["totalPages"] = $totalpages;
		$this->pager2 = $pager2;
	}
	
	
	
		function paginate2($start, $stop, $total, $pagerlinkcount, $summarize, $paginateAlwaysShowLinks, $tplPaginateNext, $tplPaginatePrevious,$paginateSplitterCharacter) {
		global $modx;
		if ($stop == 0 || $total == 0 || $summarize==0) {

			return false;
		}
		
		$next = $start + $summarize;
		$nextlink = "<a href='".$this->buildURL("start=$next")."'>" . $tplPaginateNext . "</a>";
		$previous = $start - $summarize;
		$previouslink = "<a href='".$this->buildURL("start=$previous")."'>" . $tplPaginatePrevious . "</a>";
		$limten = $summarize + $start;
		if ($paginateAlwaysShowLinks == 1) {
			$previousplaceholder = "<span class='ditto_off'>" . $tplPaginatePrevious . "</span>";
			$nextplaceholder = "<span class='ditto_off'>" . $tplPaginateNext . "</span>";
		} else {
			$previousplaceholder = "";
			$nextplaceholder = "";
		}
		$split = "";
		if ($previous > -1 && $next < $total)
			$split = $paginateSplitterCharacter;
		if ($previous > -1)
			$previousplaceholder = $previouslink;
		if ($next < $total)
			$nextplaceholder = $nextlink;
		if ($start < $total)
			$stop = $limten;
		if ($limten > $total) {
			$limiter = $total;
		} else {
			$limiter = $limten;
		}
				
		$totalpages = ceil($total / $summarize);
		
		if ($pagerlinkcount >= $totalpages) {
			for ($x = 0; $x <= $totalpages -1; $x++) {
				$inc = $x * $summarize;
				$display = $x +1;
				if ($inc != $start) {
					$pages .= "<a class=\"ditto_page\" href='".$this->buildURL("start=$inc")."'>$display</a>";
				} else {
					$modx->setPlaceholder($dittoID."currentPage", $display);
					$pages .= "<span class=\"ditto_currentpage\">$display</span>";
				}
			}
		} else {
			
			$side = ($pagerlinkcount-1)/2;
			$curpage = ceil($start / $summarize)+1;	 
			
			
					
			if (($curpage + $side) <= $totalpages && ($curpage - $side) >= 1) {
				$from = $curpage-$side-1;
				$till = $curpage+$side-1;
			} elseif (($curpage + $side) > $totalpages) {
				$from = $curpage-$side-(($curpage + $side)-($totalpages - 1));
				$till = $totalpages - 1;
			} else {
				$from = 0;
				$till = $pagerlinkcount-1;
			}
			
			for ($x = $from; $x <= $till; $x++) {
				$inc = $x * $summarize;
				$display = $x +1;
				if ($inc != $start) {
					$pages .= "<a class=\"ditto_page\" href='".$this->buildURL("start=$inc")."'>$display</a>";
				} else {
					$modx->setPlaceholder($dittoID."currentPage", $display);
					$pages .= "<span class=\"ditto_currentpage\">$display</span>";
				}
			}			
		}	
		
		$pager2["next"] = $nextplaceholder;
		$pager2["previous"] = $previousplaceholder;
		$pager2["splitter"] = $split;
		$pager2["start"] = $start + 1;
		$pager2["urlStart"] = $start;
		$pager2["stop"] = $limiter;
		$pager2["total"] = $total;
		$pager2["pages"] = $pages;
		$pager2["perPage"] = $summarize;
		$pager2["totalPages"] = $totalpages;
		$this->pager2 = $pager2;
	}
	
	}

?>