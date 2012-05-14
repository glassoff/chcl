<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('view_eventlog')) {
	$e->setError(3);
	$e->dumpError();
}
$theme = $manager_theme ? "$manager_theme/":"";
// initialize page view state - the $_PAGE object
$modx->manager->initPageViewState();  

function getParentPath($pid) {
	global $modx;
	if ($pid == 0) return; 
	while (($parent=$modx->getPageInfo($pid,0,"id,parent,pagetitle"))) {
        $titleToShow = $parent['pagetitle'] ;
        $pid = $parent['parent'];       
        $ptarr[] = $titleToShow;
    } 
    
    if ($parent != 0){
        $ptarr[] = '<span class="B_hideCrumb">...</span>';
    }

    $ptarr = array_reverse($ptarr);
    $ptarr[0] = '<span class="B_firstCrumb">'.$ptarr[0].'</span>';
    $ptarr[count($ptarr)-1] = '<span class="B_lastCrumb">'.$ptarr[count($ptarr)-1].'</span>';
    return '<span class="B_crumbBox">'. join($ptarr, " / ").'</span>';	
}

if (isset($_REQUEST['all']) && intval($_REQUEST['all']) == 1) {
	unset($_SESSION['ec_list_pid']);
	unset($_SESSION['ec_list_filter']);
	unset($_SESSION['ec_search']);
}

if (isset($_REQUEST['search']) && intval($_REQUEST['search']) == 1 && 
	isset($_POST['ec_item_id']) && isset($_POST['ec_item_title'])) {
	$_SESSION['ec_search']['ec_item_id'] = $_POST['ec_item_id'];
	$_SESSION['ec_search']['ec_item_title'] = $_POST['ec_item_title'];
	$_SESSION['ec_search']['ec_item_acc_id'] = $_POST['ec_item_acc_id'];			
} 



if (isset($_REQUEST['pid'])) {
	$pid = intval($_REQUEST['pid']);
	$_SESSION['ec_list_pid'] = $pid;
	$parent_path = getParentPath($pid);
	unset($_SESSION['ec_search']);
} elseif(isset($_SESSION['ec_list_pid'])) {
	$pid = $_SESSION['ec_list_pid'];
	$parent_path = getParentPath($pid);
} else {	
	$pid = false;
	$parent_path = '';
}


if (isset($_REQUEST['sort'])) {			
	$_SESSION['ec_list_sort'] = mysql_escape_string($_REQUEST['sort']);
	if (isset($_SESSION['sortdir']) && $_SESSION['sortdir'] == 'DESC') {
		$_SESSION['sortdir'] = 'ASC';
	} else {
		$_SESSION['sortdir'] = 'DESC';	
	} 		
} 
$sortdir = $_SESSION['sortdir'];

if (isset($_SESSION['ec_list_sort'])) {
	$sort_field = $_SESSION['ec_list_sort'];
	$sort_sql = "$sort_field  $sortdir,";					
} else {
	$sort_sql = "";	
}

if (isset($_REQUEST['perpage'])) {			
	$_SESSION['ec_perpage'] = mysql_escape_string($_REQUEST['perpage']);
} 

if (isset($_SESSION['ec_perpage'])) {
	$perpage = $_SESSION['ec_perpage'];						
} else {
	$perpage = 30;	
}



if (isset($_REQUEST['killfilter']) && $_REQUEST['killfilter'] == 1) {
	$where_filter_sql = "";
	if (isset($_SESSION['ec_list_filter'])) unset($_SESSION['ec_list_filter']);		
} else {
	
	if (isset($_POST['filter'])) {			
		$_SESSION['ec_list_filter'] = $_POST['filter'];		 
	} 
	
	if (isset($_POST['filter_status'])) {			
		$_SESSION['ec_list_filter_status'] = $_POST['filter_status'];	
		$_SESSION['ec_list_filter_status_cmd'] = $_POST['filter_status_cmd'];	 
	} 
		
	if (isset($_SESSION['ec_list_filter'])) {
		$filter_data = $_SESSION['ec_list_filter'];
		$filter_status = $_SESSION['ec_list_filter_status'];
		$filter_status_cmd = $_SESSION['ec_list_filter_status_cmd'];
		$where_filter_sql = '';
			
		if (!empty($filter_data['fromdate'])) {
			list ($d, $m, $Y, $H, $M, $S) = sscanf($filter_data['fromdate'], "%2d-%2d-%4d %2d:%2d:%2d");
			$fromdate = mktime($H, $M, $S, $m, $d, $Y);	
			$from_date = $filter_data['fromdate'];	
		} else {
			$fromdate = 0;
			$from_date = '';
		}
		
		if (!empty($filter_data['todate'])) {
			list ($d, $m, $Y, $H, $M, $S) = sscanf($filter_data['todate'], "%2d-%2d-%4d %2d:%2d:%2d");
			$todate = mktime($H, $M, $S, $m, $d, $Y);
			$to_date = $filter_data['todate'];
		} else {
			$todate = 0;
			$to_date = '';
		} 	
		
		if ($fromdate != 0 && $todate != 0) {
			if ($fromdate == $todate) $where_filter_sql .= "ec.createdon = $fromdate ";
			else $where_filter_sql .= "ec.createdon >= $fromdate AND ec.createdon <= $todate ";
		} elseif ($fromdate != 0) {
			$where_filter_sql .= "ec.createdon >= $fromdate ";
		} elseif ($todate != 0) { 
			$where_filter_sql .= "ec.createdon <= $todate ";
		}
		
		$prepare_filter_status = array();
		
		foreach ($filter_status as $k => $v) {
			if ($v != 'no') $prepare_filter_status[$k] = $v;			
		}
		
		$i = 0;
		$imax = count($prepare_filter_status);
		
		$where_filter_stat_sql = '';
		$zn = '=';
		foreach ($prepare_filter_status as $k => $v) {				
			$i++;
			$zn = '=';
			if($k=='sku' && $v)
				$zn = '>=';
			
			if ($imax > 1 && $i != $imax) {				
				$where_filter_stat_sql .= " ec.$k $zn '$v' $filter_status_cmd[$k] "; 
			} else {
				$where_filter_stat_sql .= " ec.$k $zn '$v'";
			}					
		}		
		
		if (!empty($where_filter_sql) && !empty($where_filter_stat_sql) ) $where_filter_sql .= " AND ". $where_filter_stat_sql; 
		elseif (empty($where_filter_sql)) $where_filter_sql .= $where_filter_stat_sql;
						
	} else $where_filter_sql ="";		
} 

##
$pricelists_dir = "assets/files/pricelists/";
if ($_POST['upload_price']) {
	include_once(MODX_BASE_PATH.'manager/ecommerce/1cpricelist/fileUpload.class.php'); 
	$max_size = 1024*250*10000; // the max. size for uploading	
	$_lang[11] = "Расширение загружаемого файла должно быть только .xls, .xlsx, .doc, .docx, .zip";	
	$fu = new fileUpload($_lang);
	$fu->upload_dir = MODX_BASE_PATH . $pricelists_dir;
	$fu->extensions = array(".xls", ".xlsx", ".doc", ".docx", ".zip"); 	
	$fu->max_length_filename = 100; 
	$fu->rename_file = true;
	$fu->the_temp_file = $_FILES['price_list']['tmp_name'];
	$fu->the_file = $_FILES['price_list']['name'];	
	$fu->http_error = $_FILES['price_list']['error'];
	$fu->replace = 'y'; 
	$fu->do_filename_check = "y"; 
	$new_name = 'PriceCHCL';
	
	$pl_output = "";
	if ($fu->upload($new_name)) { 
		$full_path = $fu->upload_dir.$fu->file_copy;
		//echo $full_path;
		$sql = "REPLACE INTO ".$modx->getFullTableName("ec_settings")." (setting_name, setting_value) 
		VALUES('pricelist_file', '".$pricelists_dir.$fu->file_copy."')";
		$rs =  $modx->dbQuery($sql);
		$pl_output = "Файл успешно загружен.";
	}else {
		$uploadError = true;
		$upload_errors = $fu->message;
	} 
	if ($uploadError || !$rs){
		$pl_output .= "Ошибка загрузки файла.<br>";
		foreach ((array)$upload_errors as $upload_error) {
			$pl_output.= ''.$upload_error.'</br>';
		}		
	}
}
if ($_POST['delete_price']) {
	$sql = "DELETE FROM ".$modx->getFullTableName("ec_settings")." WHERE setting_name='pricelist_file'";
	$rs =  $modx->dbQuery($sql);	
}

if($_GET['action']=='1c_tovars'){
	ob_clean();
	$sql = "SELECT * FROM " . $modx->getFullTableName("site_ec_items") . "WHERE published='1' AND deleted='0'";
	$result = $modx->db->query($sql);
	
	header ("Content-Type: text/plain");
	header ("Accept-Ranges: bytes");
	//header ("Content-Length: ".filesize($file));
	header ("Content-Disposition: attachment; filename=tovars.txt"); 	
	while($row = $modx->db->getRow($result)){
		echo $row['acc_id'] . "#" . $row['1c_code'] . "#" . $row['id'] . "\r\n";
	}
	exit();	
}

// get & save listmode
$listmode = isset($_REQUEST['listmode']) ? $_REQUEST['listmode']:$_PAGE['vs']['lm'];
$_PAGE['vs']['lm'] = $listmode;

$sql  =  "SELECT ec.*,IF(ec.new=1,'$_lang[ec_item_1new]','$_lang[ec_item_0new]') as new_title,"; 
$sql .=  "IF(ec.sell=1,'$_lang[ec_item_1sell]','$_lang[ec_item_0sell]') as sell_title,";
$sql .=  "IF(ec.published=1,'$_lang[ec_item_1published]','$_lang[ec_item_0published]') as published_title,";
$sql .=  "IF(ec.deleted=1,'$_lang[ec_item_1deleted]','$_lang[ec_item_0deleted]') as deleted_title,";
$sql .=  "IF(ec.soon=1,'$_lang[ec_item_1soon]','$_lang[ec_item_0soon]') as soon_title, sc.pagetitle as ptitle, ";
$sql .=  "br.name as brand_name,p.name as pack_name ";
$sql .=  "FROM ".$modx->getFullTableName("site_ec_items")." ec ";
$sql .=  "LEFT JOIN ".$modx->getFullTableName("site_content")." sc  ON ec.parent = sc.id ";
$sql .=  "LEFT JOIN ".$modx->getFullTableName("site_ec_brands")." br  ON br.id = ec.brand_id ";
$sql .=  "LEFT JOIN ".$modx->getFullTableName("site_ec_packs")." p  ON p.id = ec.pack_id ";
	
if (isset($_REQUEST['ec_brand'])) {			
	$_SESSION['ec_brand'] = mysql_escape_string($_REQUEST['ec_brand']);	
} 

if (isset($_SESSION['ec_brand']) && $_SESSION['ec_brand'] != 'all') {
	$curr_brand = $_SESSION['ec_brand'];
	$brand_sql = " ec.brand_id = $curr_brand ";
	if (empty($where_filter_sql)) $where_filter_sql.= $brand_sql;
	else $where_filter_sql.= " AND ".$brand_sql;					
} 


if (isset($_SESSION['ec_search'])) {
	$searchid = $modx->db->escape(trim($_SESSION['ec_search']['ec_item_id']));
	$searchaccid = $modx->db->escape(trim($_SESSION['ec_search']['ec_item_acc_id']));
	$searchtitle = $modx->db->escape(trim($_SESSION['ec_search']['ec_item_title']));	
	$sqladd  = $searchid!="" ? " AND ec.id=$searchid " : "" ;
	$sqladd .= $searchaccid!="" ? " AND ec.acc_id='$searchaccid' " : "" ;
	$sqladd .= $searchtitle!="" ? " AND MATCH (ec.pagetitle) AGAINST ('$searchtitle') " : "" ;
	$sql .= " WHERE  1=1".$sqladd." ";
} else { 
	if (!empty($where_filter_sql))  { 
		$sql .= " WHERE ".(($pid) ? "ec.parent=".$pid." AND " : " ").$where_filter_sql." ";
	} else {
		($pid) ? $sql .= " WHERE ec.parent=".$pid." " : "";		
	}		
}	

$sql .= " ORDER BY $sort_sql createdon DESC";
//echo $sql;
$manager_theme = $manager_theme ? $manager_theme : '';
$number_of_results = $perpage;	
//echo $sql; 

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
$grd->fields="num,check,pagetitle,ptitle,id,acc_id,retail_price,dealer_price,sku,createdon";
$grd->columns = "<div class=\"".($sort_field == 'id' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=id\">".$_lang["ec_item_id"] ."</a></div>,";	
$grd->columns.= "<input type=\"checkbox\" onclick=\"checkall(this)\">,";	
$grd->columns.= "<div  class=\"".($sort_field == 'pagetitle' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=pagetitle\">".$_lang["ec_item_title"] ."</a></div>,";	
$grd->columns.= "<div class=\"".($sort_field == 'acc_id' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=acc_id\">".$_lang["ec_item_acc_id"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'ptitle' ? 'actsortfield' : 'sortfield')."\"><a title=\"".$_lang['document_parent_help']."\"  href=\"index.php?a=5000&sort=ptitle\">".$_lang["ec_item_ptitle"] ."</a></div>,";	
$grd->columns.= "<div class=\"".($sort_field == 'retail_price' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=retail_price\">".$_lang["ec_item_retail_price"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'dealer_price' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=dealer_price\">".$_lang["ec_item_dealer_price"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'mdealer_price' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=mdealer_price\">".$_lang["ec_item_mdealer_price"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'sku' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=sku\">".$_lang["ec_item_sku"] ."</a></div>,";
$grd->columns.= "<div class=\"".($sort_field == 'menuindex' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=menuindex\">".$_lang["ec_item_menuindex"] ."</a></div>,";
$grd->columns.= $_lang["ec_item_status"].",";	
$grd->columns.= "<div class=\"".($sort_field == 'createdon' ? 'actsortfield' : 'sortfield')."\"><a href=\"index.php?a=5000&sort=createdon\">".$_lang["createdon"]."</a></div>,";
$grd->columns.= $_lang["ec_item_actions"];		
$grd->colWidths="20,30,60%,80,40%,20,20,20,70,60,60,60,40";
$grd->colAligns="left,left,left,left,left,left,left,left,left,left,left,left,left";
$grd->colTypes ="template:[+id+]";	
$grd->colTypes.="||template:<input type=\"checkbox\" name=\"sel_items[[+id+]]\" id=\"check_[+num+]\" value=\"[+id+]\">";
$grd->colTypes.="||template:<a href='index.php?a=5004&id=[+id+]' class=\"deletedItem_[+deleted+]\" title='".$_lang["click_to_view_details"]."'>[+pagetitle+], [+brand_name+], [+pack_name+], [+cds+][+medium+]</a>";
$grd->colTypes.="||template:[+acc_id+]";

$grd->colTypes.="||template:<input type=\"hidden\" name=\"parents[[+id+]]\" id=\"parentID[+id+]\" value=\"[+parent+]\">";
$grd->colTypes.="<img name=\"plock[+id+]\" src=\"media/style/".$manager_theme."/images/tree/folder.gif\" onclick=\"enableParentSelection([+id+]);\" align=\"left\" style=\"cursor: pointer;\" height=\"18\" width=\"18\"><b><span id=\"parentName[+id+]\">[+ptitle+]</span></b>";

$grd->colTypes.="||php:echo money(\$row['retail_price']);";
$grd->colTypes.="||php:echo money(\$row['dealer_price']);";
$grd->colTypes.="||php:echo money(\$row['mdealer_price']);";
$grd->colTypes.="||php:echo quantity(\$row['sku']);";
$grd->colTypes.="||template:
<input name=\"menuindexes[[+id+]]\" maxlength=\"4\" id=\"index_[+id+]\" value=\"[+menuindex+]\" class=\"inputBox\" style=\"width: 30px;\" type=\"text\"><br>
<input class=\"button\" value=\"&lt;\" onclick=\"var elm = document.getElementById('index_[+id+]');var v=parseInt(elm.value+'')-1;elm.value=v>0? v:0;elm.focus();\" type=\"button\">
<input class=\"button\" value=\"&gt;\" onclick=\"var elm = document.getElementById('index_[+id+]');var v=parseInt(elm.value+'')+1;elm.value=v>0? v:0;elm.focus();\" type=\"button\">
";  
	
$grd->colTypes.="||template:[+published_title+]&nbsp;<img src=\"media/style/".$manager_theme."/images/icons/information.png\"  title=\"[+published_title+], [+sell_title+], [+new_title+], [+soon_title+], [+deleted_title+]\">";	
$grd->colTypes.="||php:echo datetime1(\$row['createdon']);";
$grd->colTypes.="||php:";
$grd->colTypes.=($modx->hasPermission('ec_edit_item') ? 'echo \'<a href="#" title="'.$_lang['ec_edit_item'].'" onclick="ec_edit_item(\'.$row["id"].\')"><img src="media/style/'.$manager_theme.'/images/icons/save.gif"></a>\';' : '');
$grd->colTypes.='echo \'&nbsp;\';';
	
$delete_code = 'echo \'<a href="#" title="'.$_lang['ec_delete_item'].'" onclick="ec_delete_item(\'.$row["id"].\')"><img src="media/style/'.$manager_theme.'/images/icons/delete.gif"></a>\';';	
$undelete_code = 'echo \'<a href="#" title="'.$_lang['ec_undelete_item'].'" onclick="ec_undelete_item(\'.$row["id"].\')"><img src="media/style/'.$manager_theme.'/images/icons/b092.gif"></a>\';';	

$grd->colTypes.=($modx->hasPermission('ec_delete_item') ? 'if ($row["deleted"] == 1) '.$undelete_code.' else '.$delete_code : '');
$grd->colTypes.='echo \'&nbsp;\';';
	
$publish_code = 'echo \'<a href="#" title="'.$_lang['ec_pub_item'].'" onclick="ec_publish_item(\'.$row["id"].\')"><img src="media/style/'.$manager_theme.'/images/icons/cal.gif"></a>\';';	
$unpublish_code = 'echo \'<a href="#" title="'.$_lang['ec_unpub_item'].'" onclick="ec_unpublish_item(\'.$row["id"].\')"><img src="media/style/'.$manager_theme.'/images/icons/cal_nodate.gif"></a>\';';	
	
$grd->colTypes.=($modx->hasPermission('ec_publish_item') ? 'if ($row["published"] == 1) '.$unpublish_code.' else '.$publish_code : '');
	
if($listmode=='1') $grd->pageSize=0;
if($_REQUEST['op']=='reset') $grd->pageNumber = 1;

?>
<script type="text/javascript">
	var activeItemID = 0;
	var openedFolders = {};	
	
	function enableParentSelection(id){
	  	parent.tree.ca = "parent";
	    var closed = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folder.gif";
	    var opened = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folderopen.gif";
	    closeFolders(id);
	    var src = document.images["plock"+id].src;
	    if (src.substr(src.length-10,10) == 'folder.gif') {
	        document.images["plock"+id].src = opened;
	        activeItemID = id;
	        openedFolders[id] = true;
	    } else {	    	
	        document.images["plock"+id].src = closed;
	        activeItemID = 0;
	        openedFolders[id] = false;
	        parent.tree.ca = "open";
	    }
	}
	
	function closeFolders(skipID) {
		var closed = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folder.gif";
	    var opened = "media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/tree/folderopen.gif";
	    for(p in openedFolders){
        	if(openedFolders[p] && p != skipID) { 
        		if (openedFolders[p]) document.images["plock"+p].src = closed;
        	}	
	    }	
	}
	
	function setParent(pId, pName) {
	    if (activeItemID != 0) {
	        if(pId!=0){
	            document.getElementById('parentID'+activeItemID).value=pId;
	            var elm = document.getElementById('parentName'+activeItemID);
	            if(elm) {
	                elm.innerHTML = pName;
	            }
	        }
	    }
	}
	
  	function searchResource(){
		document.resource.op.value="srch";
		document.resource.submit();
	};

	function resetSearch(){
		document.resource.search.value = ''
		document.resource.op.value="reset";
		document.resource.submit();
	};

	function changeListMode(){
		var m = parseInt(document.resource.listmode.value) ? 1:0;
		if (m) document.resource.listmode.value=0;
		else document.resource.listmode.value=1;
		document.resource.submit();
	};	
	function showAll() {
		window.location.href='index.php?a=5000&all=1';		
	}
	
	function cancelFilter() {
		window.location.href='index.php?a=5000&killfilter=1<?php if ($pid) echo "&pid=".$pid;?>';		
	}
	
	function changePerPage(to) {
		window.location.href='index.php?a=5000&perpage='+to+'<?php if ($pid) echo "&pid=".$pid;?>';		
	}	
	function postAdd() {
		window.location.href='index.php?a=5001<?php if ($pid) echo "&pid=".$pid;?>';		
	}
	function ec_edit_item(id) {
		window.location.href='index.php?a=5002&id='+id;		
	}
	function ec_undelete_item(id) {
		window.location.href='index.php?a=5008&id='+id;		
	}
	function ec_delete_item(id) {
	    if(confirm("<?php echo $_lang['confirm_ec_delete_item']; ?>")==true) {
			window.location.href='index.php?a=5007&id='+id;		
	    }
	}	
	function ec_publish_item(id) {
		window.location.href='index.php?a=5010&id='+id;		
	}
	function ec_unpublish_item(id) {
	    window.location.href='index.php?a=5011&id='+id;	
	}
	

	function checkall(obj) {
		id_ = 'check_';
		num = 1;
		while (document.getElementById(id_+num)) {					
			document.getElementById(id_+num).checked = obj.checked;
			num++;	
		}

		document.getElementById('allpages').disabled = !obj.checked;
	}	
	function postAction() {
		var value = document.getElementById("action_cmd").options[document.getElementById("action_cmd").selectedIndex].value;
		if (value != '0') {
			if(confirm("<?php echo $_lang['confirm_ec_item_group_action']; ?>")==true) {				
				document.group_actions.cmd.value = value;
				document.group_actions.allpages.value = document.getElementById("allpages").checked ? 1 : 0;
				document.group_actions.submit();
			}	
		}
	}
		
	function selectBrand(id){
		window.location.href='index.php?a=5000&ec_brand='+id;	
	}
	
	function postfilter() {
		document.filterform.submit();		
	}
</script>
<script type="text/javascript" src="media/script/tabpane.js"></script>
<br/>
 <?php
 function getItemsCount($pid,$field,$cmd) {
 	global $modx;
 	if ($pid != false) $pid_state = 'parent = '.$pid;  else $pid_state = '';
 	$wheresql = !empty($field) ? ($field. " = ". $cmd) : "";
 	if (!empty($pid_state) && !empty($wheresql)) $state = " WHERE ". $pid_state." AND ".$wheresql;
 	elseif (!empty($pid_state) || !empty($wheresql)) $state = " WHERE ". $pid_state." ".$wheresql;
 	else $state = "";
 	$sql = "SELECT count(id) as cnt FROM " . $modx->getFullTableName("site_ec_items"). $state; 	
	$rs = mysql_query($sql);
	if (mysql_num_rows($rs) == 1) {
		$row = mysql_fetch_assoc($rs);
		return $row['cnt'];
	} else return 0; 	
 }
 ?>
<div class="sectionHeader"><?php if (!empty($parent_path)) echo $parent_path; else echo $_lang["ec_catalog"]; ?></div>
<div class="sectionBody">
	<!-- load modules -->
		<div class="tab-pane" id="FilterPane" style="border:0">
			<script type="text/javascript">
		    	tpSettings = new WebFXTabPane( document.getElementById( "FilterPane" ) );
		    </script>
		    <div class="tab-page" id="tabMain">
	        	<h2 class="tab"><?php echo $_lang["ec_item_main"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabMain" ) );</script>	   
	        </div>	
		    <div class="tab-page" id="tabInfo">
	        	<h2 class="tab"><?php echo $_lang["ec_item_info"] ?></h2>
	        	<script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabInfo" ) );</script>     	
	        	<table class="grid" align="center" border="0" cellpadding="0" cellspacing="0">
					<tbody>
					<tr class="gridHeader" align="center">
						<td colspan="8" valign="middle">
							<b><?php echo $_lang["ec_items_stat_qnt_by_cats"]?> - 
							   <?php if (!empty($parent_path)) echo $parent_path; else echo $_lang["ec_catalog"]; ?>
							</b>
						</td></tr>											
					</tr>
					<tr>
						<td align="center" class="gridItem" ><?php echo $_lang["ec_item_all"];?></td>
						<td align="center" class="gridItem" ><?php echo $_lang["ec_items_qnt_published"];?></td>
						<td align="center" class="gridItem" ><?php echo $_lang["ec_items_qnt_sell"];?></td>
						<td align="center" class="gridItem" ><?php echo $_lang["ec_items_qnt_new"];?></td>
						<td align="center" class="gridItem" ><?php echo $_lang["ec_items_qnt_popular"];?></td>
						<td align="center" class="gridItem"><?php echo $_lang["ec_items_qnt_recommended"];?></td>
						<td align="center" class="gridItem"><?php echo $_lang["ec_items_qnt_byorder"];?></td>
						<td align="center" class="gridItem"><?php echo $_lang["ec_items_qnt_deleted"];?></td>
					</tr>					
					<tr>
					<td align="center" class="gridAltItem"><?php echo getItemsCount($pid,"","");?> <?php echo $_lang["qnt"];?></td>
						<td align="center" class="gridAltItem"><?php echo getItemsCount($pid,"published","1");?> <?php echo $_lang["qnt"];?></td>
						<td align="center" class="gridAltItem"><?php echo getItemsCount($pid,"sell","1");?> <?php echo $_lang["qnt"];?></td>
						<td align="center" class="gridAltItem"><?php echo getItemsCount($pid,"new","1");?> <?php echo $_lang["qnt"];?></td>
						<td align="center" class="gridAltItem"><?php echo getItemsCount($pid,"popular","1");?> <?php echo $_lang["qnt"];?></td>
						<td align="center" class="gridAltItem"><?php echo getItemsCount($pid,"recommended","1");?> <?php echo $_lang["qnt"];?></td>
						<td align="center" class="gridAltItem"><?php echo getItemsCount($pid,"byorder","1");?> <?php echo $_lang["qnt"];?></td>
						<td align="center" class="gridAltItem"><?php echo getItemsCount($pid,"deleted","1");?> <?php echo $_lang["qnt"];?></td>
					</tr>
					</tbody>
				</table>	        	  	
			</div>
		 	<div class="tab-page" id="tabFilter">
	        <h2 class="tab"><?php echo $_lang["ec_item_filter"] ?></h2>
	        <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabFilter" ) );</script>
	        <p><?php echo $_lang["ec_item_filter_overview"]; ?></p>
	        <form action="index.php?a=5000" name="filterform" method="POST">	       
	        <input type="hidden" name="pid" value="<?php echo $pid;?>"/>	                
	        <table border="0">
	        	 <tr>
				 	<td >
				    <strong><?php echo $_lang["ec_sort_by_created_date"];?>:</strong> 
				 	<table cellpadding="1" cellspacing="1" class="actionButtons" align="left" >
        				<tr>
						  <td nowrap>
							    <strong><?php echo $_lang["ec_order_date_from"];?>&nbsp;</strong>
						 	    <input id="from_date" name="filter[fromdate]" type="text" size="20" value="<?php echo $from_date; ?>" readonly>
						  </td>
						  <td>
							 	<a onclick="cal1.popup();" onmouseover="window.status='Select a date'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal.gif" width="16" height="16" border="0" /></a>
						  </td>
						  <td>
								<a onclick="document.forms['filterform'].elements['filter[fromdate]'].value='';" href='#'><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="No date" /></a>
						  </td>
						  <td nowrap>
							    <strong><?php echo $_lang["ec_order_date_to"];?>&nbsp;</strong>
						 	    <input id="to_date" name="filter[todate]" type="text" size="20" value="<?php echo $to_date;?>" readonly>
					 	 </td>
					 	 <td>
						 	 <a onclick="cal2.popup();" onmouseover="window.status='Select a date'; return true;" onmouseout="window.status=''; return true;" style="cursor:pointer; cursor:hand"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal.gif" width="16" height="16" border="0" /></a>
					 	 </td>
						 <td>
						     <a onclick="document.forms['filterform'].elements['filter[todate]'].value='';" href='#'><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cal_nodate.gif" width="16" height="16" border="0" alt="No date" /></a>
						 </td>
				 		</tr>
				     </table>
				 	
				      
				 	</td>				 	  
				 </tr>
				 <tr>					 	
					  <td><div class="stay"></div></td>	  		  
				 </tr>
				 <tr>
				    <td>
				    <strong><?php echo $_lang["ec_sort_by_status"];?>:</strong>
				 	<table cellpadding="1" cellspacing="1" class="actionButtons" align="left" >
        				<tr>				 	
						 <td style="padding-right:5px;" align="left" nowrap>						 
							 <?php echo $_lang["ec_item_sell"].":";?><br>
							 <select name="filter_status[sell]">
							 	<option value="no" <?php if ($filter_status['sell'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>
							 	<option value="0"  <?php if ($filter_status['sell'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
							 	<option value="1"  <?php if ($filter_status['sell'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
							 </select>						 
							 <select name="filter_status_cmd[sell]">						 					 	
							 	<option value="AND" <?php if ($filter_status_cmd['sell'] == 'AND') echo "selected";?>><?php echo $_lang["ec_item_filter_and"];?></option>
							 	<option value="OR"  <?php if ($filter_status_cmd['sell'] == 'OR') echo "selected";?>><?php echo $_lang["ec_item_filter_or"];?></option>
							 </select>						 
						 </td>
					     <td style="padding-right:5px;" align="left" nowrap>
							 <?php echo $_lang["ec_item_soon"].":";?><br>
							 <select name="filter_status[soon]">
							 	<option value="no" <?php if ($filter_status['soon'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>
								<option value="0"  <?php if ($filter_status['soon'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
								<option value="1"  <?php if ($filter_status['soon'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
							  </select>						 
							  <select name="filter_status_cmd[soon]">											 	
								<option value="AND" <?php if ($filter_status_cmd['soon'] == 'AND') echo "selected";?>><?php echo $_lang["ec_item_filter_and"];?></option>
								<option value="OR" <?php if ($filter_status_cmd['soon'] == 'OR') echo "selected";?>><?php echo $_lang["ec_item_filter_or"];?></option>
							  </select>
						 </td>
					     <td  style="padding-right:5px;" align="left" nowrap>
							 <?php echo $_lang["ec_item_new"].":";?><br>
						      <select name="filter_status[new]">						 						 	
								<option value="no" <?php if ($filter_status['new'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>
							   	<option value="0"  <?php if ($filter_status['new'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
								<option value="1"  <?php if ($filter_status['new'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
							  </select>						 
							  <select name="filter_status_cmd[new]">												 	
								<option value="AND" <?php if ($filter_status_cmd['new'] == 'AND') echo "selected";?>><?php echo $_lang["ec_item_filter_and"];?></option>
								<option value="OR"  <?php if ($filter_status_cmd['new'] == 'OR') echo "selected";?>><?php echo $_lang["ec_item_filter_or"];?></option>
							  </select></td>
					      <td  style="padding-right:5px;" align="left" nowrap>
							 <?php echo $_lang["ec_item_publish"].":";?><br>
						      <select name="filter_status[published]">
							 	<option value="no" <?php if ($filter_status['published'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>						 	
								<option value="0"  <?php if ($filter_status['published'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
								<option value="1"  <?php if ($filter_status['published'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
							  </select>						 
							  <select name="filter_status_cmd[published]">													 	
								<option value="AND" <?php if ($filter_status_cmd['published'] == 'AND') echo "selected";?>><?php echo $_lang["ec_item_filter_and"];?></option>
								<option value="OR" <?php if ($filter_status_cmd['published'] == 'OR') echo "selected";?>><?php echo $_lang["ec_item_filter_or"];?></option>
							  </select>
						  </td>
					      <td  style="padding-right:5px;" align="left" nowrap>
							 <?php echo $_lang["ec_item_delete"].":";?><br>
						      <select name="filter_status[deleted]">
							 	<option value="no" <?php if ($filter_status['deleted'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>						 	
								<option value="0"  <?php if ($filter_status['deleted'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
								<option value="1"  <?php if ($filter_status['deleted'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
							  </select>	
							   <select name="filter_status_cmd[deleted]">													 	
								<option value="AND" <?php if ($filter_status_cmd['deleted'] == 'AND') echo "selected";?>><?php echo $_lang["ec_item_filter_and"];?></option>
								<option value="OR" <?php if ($filter_status_cmd['deleted'] == 'OR') echo "selected";?>><?php echo $_lang["ec_item_filter_or"];?></option>
							  </select>		
						  </td>	  		  
					 </tr>
					 
					 <tr>					 	
								 <td style="padding-right:5px;" align="left" nowrap>
									 <?php echo $_lang["ec_item_popular"].":";?><br>
									 <select name="filter_status[popular]">
									 	<option value="no" <?php if ($filter_status['popular'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>
									 	<option value="0"  <?php if ($filter_status['popular'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
									 	<option value="1"  <?php if ($filter_status['popular'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
									 </select>						 
									 <select name="filter_status_cmd[popular]">						 					 	
									 	<option value="AND" <?php if ($filter_status_cmd['popular'] == 'AND') echo "selected";?>><?php echo $_lang["ec_item_filter_and"];?></option>
									 	<option value="OR"  <?php if ($filter_status_cmd['popular'] == 'OR') echo "selected";?>><?php echo $_lang["ec_item_filter_or"];?></option>
									 </select>						 
								 </td>
							     <td style="padding-right:5px;" align="left" nowrap>
									 <?php echo $_lang["ec_item_recommended"].":";?><br>
									 <select name="filter_status[recommended]">
									 	<option value="no" <?php if ($filter_status['recommended'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>
										<option value="0"  <?php if ($filter_status['recommended'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
										<option value="1"  <?php if ($filter_status['recommended'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
									  </select>						 
									  <select name="filter_status_cmd[recommended]">											 	
										<option value="AND" <?php if ($filter_status_cmd['recommended'] == 'AND') echo "selected";?>><?php echo $_lang["ec_item_filter_and"];?></option>
										<option value="OR" <?php if ($filter_status_cmd['recommended'] == 'OR') echo "selected";?>><?php echo $_lang["ec_item_filter_or"];?></option>
									  </select>
								 </td>
							     <td  style="padding-right:5px;" align="left" _colspan="3" nowrap>
									 <?php echo $_lang["ec_item_byorder"].":";?><br>
								      <select name="filter_status[byorder]">			 			 						 	
										<option value="no" <?php if ($filter_status['byorder'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>
									   	<option value="0"  <?php if ($filter_status['byorder'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
										<option value="1"  <?php if ($filter_status['byorder'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
									  </select>	
									  <select name="filter_status_cmd[byorder]">											 	
										<option value="AND" <?php if ($filter_status_cmd['byorder'] == 'AND') echo "selected";?>><?php echo $_lang["ec_item_filter_and"];?></option>
										<option value="OR" <?php if ($filter_status_cmd['byorder'] == 'OR') echo "selected";?>><?php echo $_lang["ec_item_filter_or"];?></option>
									  </select>									  					 
						 		 </td>	
							     <td  style="padding-right:5px;" align="left" colspan="2" nowrap>
									 Наличие на складе: <br>
								      <select name="filter_status[sku]">			 			 						 	
										<option value="no" <?php if ($filter_status['sku'] == 'no') echo "selected";?>><?php echo $_lang["ec_item_filter_empty"];?></option>
									   	<option value="0"  <?php if ($filter_status['sku'] == '0') echo "selected";?>><?php echo $_lang["ec_item_filter_no"];?></option>
										<option value="1"  <?php if ($filter_status['sku'] == '1') echo "selected";?>><?php echo $_lang["ec_item_filter_yes"];?></option>
									  </select>						 
						 		 </td>						 		 				     
				      		</tr>
				    	 </table>
				 	</td>	 
				 </tr>
				
				 <tr>					 	
					  <td><div class="stay"></div></td>	  		  
				 </tr>	 
				 
				 <tr>
				 
				 
				     
				     <td >
				     
				      <table cellpadding="2" cellspacing="2" class="actionButtons" align="left" >
        				<tr>
           					<td id="Button1" ><a href="#" onclick="postfilter();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/save.gif" /> <?php echo $_lang["ec_item_filter_button"];?></a></td>
            				<td width="100%"></td>
           					<td id="Button2"><a href="#" onclick="cancelFilter();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/refresh.gif" /> <?php echo $_lang["ec_item_cancel_filter_button"];?></a></td>
            				<td id="Button2"><a href="#" onclick="showAll();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/table.gif" /> <?php echo $_lang["ec_item_all_filter_button"];?></a></td>
            			
           				</tr>
				     </table>
				    
				     </td>
				
				</tr>
				
			</table>
			</form>
	        </div>
    		
		<div class="tab-page" id="tabGroupActions">
	        <h2 class="tab"><?php echo $_lang["ec_item_group_actions"] ?></h2>
	        <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabGroupActions" ) );</script>
	        <p><?php echo $_lang["ec_item_group_actions_overview"]; ?></p> 
	        
	        <table cellpadding="2" cellspacing="2" class="actionButtons">
	        	<tr>
	        		<td>
	        			<label>Выбрать товары на ВСЕХ страницах <input type="checkbox" id="allpages" name="allpages" disabled <?php echo $_REQUEST['allpages'] ? 'checked' : '' ?>/></label> 
	        		</td>
	        		<td></td>
	        	</tr>
        		<tr>
        			<td>
        			<?php echo $_lang["ec_select_action"];?>
        			</td>
        			<td>
        				<select name="a" id="action_cmd">
        					<option value="0"><?php echo $_lang["ec_actions"]?></option>        					
							<?php if ($modx->hasPermission('ec_publish_item')) {?>
           					<option value="5017"><?php echo $_lang["ec_publish_items"];?></option>
           					<option value="5018"><?php echo $_lang["ec_unpublish_items"];?></option>
							<?php }?>
							<?php if ($modx->hasPermission('ec_delete_item')) {?>
           					<option value="5015"><?php echo $_lang["ec_delete_items"];?></option>
           					<option value="5016"><?php echo $_lang["ec_undelete_items"];?></option>
							<?php }?>
							<?php if ($modx->hasPermission('ec_remove_item')) {?>
           					<option value="5019"><?php echo $_lang["ec_remove_items"];?></option>
							<?php }?>    
							<?php if ($modx->hasPermission('ec_edit_item')) {?>
							<option value="5020"><?php echo $_lang["ec_sort_items"];?></option>
							<option value="5029"><?php echo $_lang["ec_change_parents"];?></option>
           					<option value="5030"><?php echo $_lang["ec_item_1popular"];?></option>
           					<option value="5031"><?php echo $_lang["ec_item_0popular"];?></option>
           					<option value="5032"><?php echo $_lang["ec_item_1recommended"];?></option>
           					<option value="5033"><?php echo $_lang["ec_item_0recommended"];?></option>
           					<option value="5034"><?php echo $_lang["ec_item_1byorder"];?></option>
           					<option value="5035"><?php echo $_lang["ec_item_0byorder"];?></option>
           					<option value="5036"><?php echo $_lang["ec_item_1new"];?></option>
           					<option value="5037"><?php echo $_lang["ec_item_0new"];?></option>
           					<option value="5038"><?php echo $_lang["ec_item_1sell"];?></option>
           					<option value="5039"><?php echo $_lang["ec_item_0sell"];?></option>
           					<option value="5040"><?php echo $_lang["ec_item_1soon"];?></option>
           					<option value="5041"><?php echo $_lang["ec_item_0soon"];?></option>
           					<option value="5042">Акция</option>
           					<option value="5043">Не акция</option>
							<?php }?>     					
        				</select>
        				        				
        			</td>
        			<td id="Button5"><a href="#" onclick="postAction();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/save.gif" /> <?php echo $_lang["ec_do_action"];?></a></td>
				</tr>
			</table>				
			
						
		</div>
		<div class="tab-page" id="tabSearch">
	        <h2 class="tab"><?php echo $_lang["ec_search"] ?></h2>
	        <script type="text/javascript">tpSettings.addTabPage( document.getElementById( "tabSearch" ) );</script>	
		    <?php echo $_lang['ec_search_criteria']; ?>
			<p><form action="index.php?a=5000&search=1" method="post" name="searchform"></p>
			<table  border="0">
			  <tr>
			    <td width="120"><?php echo $_lang['search_criteria_id']; ?></td>
			    <td width="20">&nbsp;</td>
			    <td width="120"><input name="ec_item_id" type="text" value="<?php echo !@empty($_SESSION['ec_search']['ec_item_id']) ? $_SESSION['ec_search']['ec_item_id'] : "";?>"></td>
				
			  </tr>
			   <tr>
			    <td width="120"><?php echo $_lang['search_criteria_acc_id']; ?></td>
			    <td width="20">&nbsp;</td>
			    <td width="120"><input name="ec_item_acc_id" type="text" value="<?php echo !@empty($_SESSION['ec_search']['ec_item_acc_id']) ? $_SESSION['ec_search']['ec_item_acc_id'] : "";?>"></td>
				
			  </tr>
			  <tr>
			    <td><?php echo $_lang['search_criteria_title']; ?></td>
			    <td>&nbsp;</td>
			    <td><input name="ec_item_title" type="text" value="<?php echo htmlspecialchars(@$_SESSION['ec_search']['ec_item_title']);?>"></td>
				
			  </tr>			  
			  <tr>
			  	<td colspan="4">
					<table cellpadding="0" cellspacing="0" border="0" class="actionButtons">
					    <td id="Button1" align="right"><a href="#" onclick="document.searchform.submit();"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" align="absmiddle"> <?php echo $_lang['search']; ?></a></td>
					    <td id="Button2" align="right"><a href="index.php?a=5000"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/cancel.gif" align="absmiddle"> <?php echo $_lang['cancel']; ?></a></td>
					</table>
				</td>
			  </tr>
			</table>
			
			</form>
			<script>
				document.searchform.onkeypress = function(e){
					if(e.keyCode==13) this.submit();
				}
			</script>
			<div class="stay"></div>	
			<p><strong><?php if (isset($_SESSION['ec_search'])) echo $_lang['ec_search_found']." ".$result_size." ".$_lang['ec_search_found_records'];?></strong></p>
		</div>
		
			<div class="tab-page" id="tabFilter">
	        <h2 class="tab">Слова для фильтрации в каталоге сайта</h2>
	        
		<?php 
		
		    if (isset($_POST['id_f2']) && $_POST['zag'] && $_POST['words']) {
 
  
	$zag=$_POST['zag'];
	$words=$_POST['words'];
	$sql5 = "INSERT INTO word_filter (id, name, zag, words) VALUES ('','','$zag','$words')";//die($sql5);
    $rs =  $modx->dbQuery($sql5);
    if(!$rs) {
				echo "error";
			}
    
    }
		?>
			        
	     <?php  
	     
	     if (isset($_POST['id_f']) && $_POST['zag'] && $_POST['words']) {
 
  
	$zag=$_POST['zag'];
	$id_f=$_POST['id_f'];
	$words=$_POST['words'];
	$sql5 = "UPDATE word_filter SET zag='$zag', words='$words' WHERE id='$id_f'";//die($sql5);
    $rs =  $modx->dbQuery($sql5);
    if(!$rs) {
				echo "error";
			}
    
    }
    
    if($_POST['delete_word']=='Delete' && $_POST['id_f']){
    	$id_f=$_POST['id_f'];
    	$sql5 = "DELETE FROM word_filter WHERE (id='$id_f')";
    	$rs =  $modx->dbQuery($sql5);
    }
	
	     
	     
	     $sql = "SELECT * FROM word_filter ";
		$result = $modx->dbQuery($sql);	
		$row_f = mysql_fetch_assoc($result);
		do {
		?>
			<p><form action="" method="post" name="filter1form"></p>
			<table  border="0">
			  <tr>
			    <td width="100"><input name="zag" value="<?php echo $row_f['zag']; ?>" size="10"></td>
			    <td width="500">
				<input name="words" value="<?php echo $row_f['words']; ?>" size="80">
			    <input type="hidden" name="id_f" value="<?php echo $row_f['id']; ?>">
			    
			<!--    <a href="#" onclick="document.filterform.submitok.click();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/save.gif" /> 
		ОК</a>	-->
		<input type="submit" value="OK"  style=""> <input onclick="if(!confirm('Delete this words?'))return false;" type="submit" value="Delete" name="delete_word" style="">
			    </td> </tr>
		</table>
		</form>
		
		
		<?php }
	while ($row_f = mysql_fetch_assoc($result)) ;  
		?>
		
		<br>
		

		
		
		
		<form action="" method="post" name="filter2form"></p>
			<table  border="0">
			  <tr>
			    <td width="100"><input name="zag" value="" size="10"></td>
			    <td width="500"><input name="words" value="" size="70">
	<input type="hidden" name="id_f2" value="">
		<input type="submit" value="Добавить"  style="">
			    </td> </tr>
		</table>
		</form>
		
		
		
			<div class="stay"></div>	
		
	</div>
	<!-- ## -->
	<div class="tab-page" id="tabFilter">
    <h2 class="tab">Прайс-лист</h2>
    	<h2>Выгрузка товаров</h2>
    	<p><a href="index.php?a=5000&action=1c_tovars">Выгрузить список опубликованых товаров</a></p>
    	
    	<h2>Прайс-лист для загрузки с сайта</h2>
    	<div><?php echo $pl_output; ?></div>
    	<form action="index.php?a=5000" name="upload_data" method="POST" enctype="multipart/form-data">
    		<!-- input type="hidden" name="tabAction1" value="_1c_upload_data" /--> 
    		<input type="file" name="price_list" />
    		<input type="submit" name="upload_price" value="Загрузить" />
    	</form>
    	<?php 
    		$sql = "SELECT * FROM ".$modx->getFullTableName("ec_settings")." WHERE setting_name='pricelist_file'";
    		$rs =  $modx->dbQuery($sql);
    		if (mysql_num_rows($rs)>0){
    			$row = mysql_fetch_array($rs);
    			$current_pl = $row['setting_value'];
    	?>
    	<div style="margin-top: 8px;">
    		Текущий прайс-лист: <a href="<?php echo MODX_SITE_URL . $current_pl;?>"><?php echo basename($current_pl);?></a><form action="index.php?a=5000" name="delete_data" method="POST" enctype="multipart/form-data"><input type="submit" name="delete_price" value="Удалить" /></form>    		
    	</div>
    	<?php }?>
    </div>	
	<div class="split"></div>
	
	 <table border="0" >
		 <tr>				
		 	 <td  align="left" nowrap>
				    <strong><?php echo $_lang["ec_item_brand"];?>&nbsp;</strong>		 	 	
			 </td>	
			 <td  align="left">
			 <?php
			    $order_status_arr = array();
				$order_curr_status = isset($order_curr_status) ? $order_curr_status : $order_def_status;
				$sql = 'SELECT * FROM ' . $modx->getFullTableName("site_ec_brands") . ' order by name,listindex';
				$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
				$lines[] = '<select id="ec_brand" onchange=selectBrand(this.options[this.selectedIndex].value)>';	
				$lines[] = '<option value="all">'.$_lang['ec_order_status_all'].'</option>';				
				if ($rs && mysql_num_rows($rs)>0) {
					while ($row = mysql_fetch_assoc($rs)) {	
						$order_status_arr[] = $row;
						if ($curr_brand == $row['id']) $lines[] = '<option value="'.$row['id'].'"  selected>'.$row['name'].'</option>';					else $lines[] = '<option value="'.$row['id'].'">'.$row['name'].'</option>';					
						}	
					}				
					$lines[] = '</select>';		
					echo implode("\n", $lines);
			?>
			</td> 		
		 
		 	 <td  align="left">
		 	 	<table cellpadding="0" cellspacing="0" class="actionButtons">
        			<tr>
           				<td id="Button1"><a href="#" onclick="postAdd();return false;"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>/images/icons/newdoc.gif" /> <?php echo $_lang["ec_new_item"];?></a></td>
            		</tr>
				</table>
		 	 </td> 	
			 <td  align="right" width="100%">
				 <?php echo $_lang["ec_item_per_page"];?>
					 <select name="per_page" onchange="changePerPage(this.options[this.selectedIndex].value)">
					 	<option value="30" <?php if ($perpage == 30) echo "selected";?>>30</option>
					 	<option value="50" <?php if ($perpage == 50) echo "selected";?>>50</option>
					 	<option value="100" <?php if ($perpage == 100) echo "selected";?>>100</option>
					 	<option value="150" <?php if ($perpage == 150) echo "selected";?>>150</option>
					  	<option value="200" <?php if ($perpage == 200) echo "selected";?>>200</option>

					 	<!--<option value="0" <?php if ($perpage == 0) echo "selected";?>><?php echo $_lang['ec_item_per_page_all']?></option>-->
					 </select>						 
		     </td>
		 </tr>
	 </table>	     
	 <form action="index.php" method="POST" name="group_actions">
	 <input type="hidden" name="a" value="5015">
	 <input type="hidden" name="cmd" value="">
	 <input type="hidden" name="allpages" value="">
	<?php				  
	echo $grd->render();
	?>
	</form>
	</div>
</div>
</form>
<script type="text/javascript" src="media/script/datefunctions.js"></script>
<script type="text/javascript">
    var cal1 = new calendar1(document.forms['filterform'].elements['filter[fromdate]'], document.getElementById("from_date"));
    cal1.path="<?php echo str_replace("index.php", "media/", $_SERVER["PHP_SELF"]); ?>";
    cal1.year_scroll = true;
    cal1.time_comp = true;


    var cal2 = new calendar1(document.forms['filterform'].elements['filter[todate]'], document.getElementById("to_date"));
    cal2.path="<?php echo str_replace("index.php", "media/", $_SERVER["PHP_SELF"]); ?>";
    cal2.year_scroll = true;
    cal2.time_comp = true;
</script>
