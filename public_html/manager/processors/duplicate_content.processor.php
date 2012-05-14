<?php 
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('save_document')) {
	$e->setError(3);
	$e->dumpError();	
}
?>
<?php

// check the document doesn't have any children
$id=$_GET['id'];
$children = array();

// check permissions on the document
include_once "./processors/user_documents_permissions.class.php";
$udperms = new udperms();
$udperms->user = $modx->getLoginUserID();
$udperms->document = $id;
$udperms->role = $_SESSION['mgrRole'];
$udperms->duplicateDoc = true;

if(!$udperms->checkPermissions()) {
	include "header.inc.php";
	?><br /><br /><div class="sectionHeader"><img src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/misc/dot.gif' alt="." />&nbsp;<?php echo $_lang['access_permissions']; ?></div><div class="sectionBody">
	<p><?php echo $_lang['access_permission_denied']; ?></p>
	<?php
	include("footer.inc.php");
	exit;	
}

// check for MySQL 4.0.14
$mysqlVerOk = (version_compare(mysql_get_server_info(),"4.0.14")>=0);

// get document's parent id
$sql = "SELECT parent FROM $dbase.`".$table_prefix."site_content` WHERE $dbase.`".$table_prefix."site_content`.id=$id;";
$rs = mysql_query($sql);
if(!rs){
	echo "A database error occured while trying to load document: <br /><br />".mysql_error();
	exit;
}
else {
	$row=mysql_fetch_assoc($rs);
	$parent = $row['parent'];
}

// get document's children
$children = getChildren($id);

$newdocid = 0;
duplicateDocument($parent,$id,$children);

function duplicateDocument($parent,$docid,$children,$_toplevel=0){	

	global $mysqlVerOk;
	global $dbase, $table_prefix;
	global $modx;
	
	$myChildren = array();
	$userID = $modx->getLoginUserID();
	
	// duplicate document
	if($mysqlVerOk) {
		$sql = "INSERT INTO $dbase.`".$table_prefix."site_content` (type, contentType, pagetitle, longtitle, description, alias, published, pub_date, unpub_date, parent, isfolder, introtext, content, richtext, template, menuindex, searchable, cacheable, createdby, createdon, editedby, editedon, deleted, deletedon, deletedby, menutitle, donthit, privateweb, privatemgr, content_dispo, hidemenu) 
				SELECT type, contentType, ".($_toplevel==0 ? "CONCAT('Duplicate of ',pagetitle) AS 'pagetitle'":"pagetitle").", longtitle, description, NULL AS alias, published, pub_date, unpub_date, '$parent' as 'parent', isfolder, introtext, content, richtext, template, menuindex, searchable, cacheable, $userID as createdby, UNIX_TIMESTAMP(), 0, 0, 0, 0, 0, menutitle, donthit, privateweb, privatemgr, content_dispo, hidemenu  
				FROM $dbase.`".$table_prefix."site_content` WHERE id=$docid;";
		$rs = mysql_query($sql);
	}
	else{
		$sql = "SELECT type, contentType, ".($_toplevel==0 ? "CONCAT('Duplicate of ',pagetitle) AS 'pagetitle'":"pagetitle").", longtitle, description, alias, published, pub_date, unpub_date, '$parent' as 'parent', isfolder, introtext, content, richtext, template, menuindex, searchable, cacheable, createdby, createdon, editedby, editedon, deleted, deletedon, deletedby, menutitle, donthit, privateweb, privatemgr, content_dispo, hidemenu 
				FROM $dbase.`".$table_prefix."site_content` WHERE id=$docid;";
		$rs = mysql_query($sql);		
		if($rs) {
			$row = mysql_fetch_assoc($rs);	
			$sql = "INSERT INTO $dbase.`".$table_prefix."site_content` 
					(type, contentType, pagetitle, longtitle, description, alias, published, pub_date, unpub_date, parent, isfolder, introtext, content, richtext, template, menuindex, searchable, cacheable, createdby, createdon, editedby, editedon, deleted, deletedon, deletedby, menutitle, donthit, privateweb, privatemgr, content_dispo, hidemenu) VALUES
					('".$row['type']."', '".$row['contentType']."', '".mysql_escape_string($row['pagetitle'])."', '".mysql_escape_string($row['longtitle'])."', '".mysql_escape_string($row['description'])."', NULL, '".$row['published']."', '".$row['pub_date']."', '".$row['unpub_date']."', '".$row['parent']."', '".$row['isfolder']."', '".mysql_escape_string($row['introtext'])."', '".mysql_escape_string($row['content'])."', '".$row['richtext']."', '".$row['template']."', '".$row['menuindex']."', '".$row['searchable']."', '".$row['cacheable']."', $userID, UNIX_TIMESTAMP(), 0, 0, 0, 0, 0, '".mysql_escape_string($row['menutitle'])."', '".$row['donthit']."', '".$row['privateweb']."', '".$row['privatemgr']."', ".$row['content_dispo'].", ".$row['hidemenu'].");";
			$rs = mysql_query($sql);		
		}
	}
	if($rs) {
		$parent = mysql_insert_id(); // get new parent id
		if($_toplevel==0) {
			global $newdocid;
			$newdocid = $parent;
		}
	}
	else {
		echo "A database error occured while trying to duplicate document: <br /><br />".mysql_error();
		exit;
	}

	// duplicate document's TVs & Keywords
	duplicateTVs($docid,$parent);
	duplicateKeywords($docid,$parent);
	duplicateAccess($docid,$parent);

	// duplicate document's children.
	if(is_array($children)) {
		foreach($children as $id => $child){
			if (is_array($child)) duplicateDocument($parent,$id,$child,++$_toplevel); // duplicate my child with grandchildren
			else $myChildren[] = $id;  // used to duplicate my child without grandchildren
		}
		if(count($myChildren)>0) {
			$docs_to_duplicated = implode(" ,", $myChildren);
			if($mysqlVerOk) {
				$sql = "INSERT INTO $dbase.`".$table_prefix."site_content` (type, contentType, pagetitle, longtitle, description, alias, published, pub_date, unpub_date, parent, isfolder, introtext, content, richtext, template, menuindex, searchable, cacheable, createdby, createdon, editedby, editedon, deleted, deletedon, deletedby, menutitle, donthit, privateweb, privatemgr, content_dispo, hidemenu) 
						SELECT type, contentType, pagetitle, longtitle, description, alias, published, pub_date, unpub_date, '$parent' as 'parent', isfolder, introtext, content, richtext, template, menuindex, searchable, cacheable, $userID, UNIX_TIMESTAMP(), 0, 0, 0, 0, 0, menutitle, donthit, privateweb, privatemgr, content_dispo, hidemenu 
						FROM $dbase.`".$table_prefix."site_content` WHERE id IN ($docs_to_duplicated);";
				$rs = @mysql_query($sql);
				$affected = mysql_affected_rows();
				$newid = mysql_insert_id();
				for ($i=0;$i<$affected;$i++) {
					// duplicate the TVs and keywords for the document's children
					duplicateTVs($myChildren[$i],$newid);
					duplicateKeywords($myChildren[$i],$newid);
					duplicateAccess($myChildren[$i],$newid);
					$newid++;
				}
			}
			else {
				//-- get children
				$sql = "SELECT id, type, contentType, pagetitle, longtitle, description, alias, published, pub_date, unpub_date, '$parent' as 'parent', isfolder, introtext, content, richtext, template, menuindex, searchable, cacheable, createdby, createdon, editedby, editedon, deleted, deletedon, deletedby, menutitle, donthit, privateweb, privatemgr, content_dispo, hidemenu 
						FROM $dbase.`".$table_prefix."site_content` WHERE id IN ($docs_to_duplicated);";
				$ds = @mysql_query($sql);
				while($row = mysql_fetch_assoc($ds)) {
					$sql = "INSERT INTO $dbase.`".$table_prefix."site_content` 
							(type, contentType, pagetitle, longtitle, description, alias, published, pub_date, unpub_date, parent, isfolder, introtext, content, richtext, template, menuindex, searchable, cacheable, createdby, createdon, editedby, editedon, deleted, deletedon, deletedby, menutitle, donthit, privateweb, privatemgr, content_dispo, hidemenu) VALUES
							('".$row['type']."', '".$row['contentType']."', '".mysql_escape_string($row['pagetitle'])."', '".mysql_escape_string($row['longtitle'])."', '".mysql_escape_string($row['description'])."', NULL, '".$row['published']."', '".$row['pub_date']."', '".$row['unpub_date']."', '".$row['parent']."', '".$row['isfolder']."', '".mysql_escape_string($row['introtext'])."', '".mysql_escape_string($row['content'])."', '".$row['richtext']."', '".$row['template']."', '".$row['menuindex']."', '".$row['searchable']."', '".$row['cacheable']."', $userID, UNIX_TIMESTAMP(), 0, 0, 0, 0, 0, '".mysql_escape_string($row['menutitle'])."', '".$row['donthit']."', '".$row['privateweb']."', '".$row['privatemgr']."', '".$row['content_dispo']."', '".$row['hidemenu']."');";
					$rs = mysql_query($sql);
					$newid = mysql_insert_id(); // get first inserted id
					// duplicate the TVs and keywords for the document's children
					duplicateTVs($row['id'],$newid);
					duplicateKeywords($row['id'],$newid);
					duplicateAccess($row['id'],$newid);
				}
			}
			
			if(!$rs) {
				echo "A database error occured while trying to duplicate document's children:<br /><br />".mysql_error();
				exit;
			}
		}
	}
}

// finish cloning - redirect
if($newdocid) $id = $newdocid;
$header="Location: index.php?r=1&a=3&id=$id";
header($header);

// Get Children
function getChildren($parent) {

	global $dbase;
	global $table_prefix;
	global $site_start;
	
	//$db->debug = true;
	
	$sql = "SELECT id FROM $dbase.`".$table_prefix."site_content` WHERE $dbase.`".$table_prefix."site_content`.parent=".$parent." AND deleted=0 ORDER BY id ASC;";
	$rs = mysql_query($sql);
	$limit = mysql_num_rows($rs);
	if($limit>0) {
		$children = array();
		// the document has children documents, we'll need to duplicate those too
		for($i=0;$i<$limit;$i++) {
			$row=mysql_fetch_assoc($rs);
			$c = getChildren($row['id']);
			$children[$row['id']] = ($c) ?  $c:$row['id'];
		}
	}
	return $children;
}

// Duplicate Keywords
function duplicateKeywords($oldid,$newid){

	global $mysqlVerOk;
	global $dbase, $table_prefix;

	if($mysqlVerOk) {
		$sql = "INSERT INTO $dbase.`".$table_prefix."keyword_xref` (content_id, keyword_id) 
				SELECT $newid, keyword_id  
				FROM $dbase.`".$table_prefix."keyword_xref` WHERE content_id=$oldid;";
		$rs = mysql_query($sql);
	}
	else {
		$sql = "SELECT $newid as 'newid', keyword_id  
				FROM $dbase.`".$table_prefix."keyword_xref` WHERE content_id=$oldid;";
		$ds = mysql_query($sql);
		while($row = mysql_fetch_assoc($ds)) {
			$sql = "INSERT INTO $dbase.`".$table_prefix."keyword_xref` 
					(content_id, keyword_id) VALUES
					(".$row['newid'].", '".$row['keyword_id']."');";
			$rs = mysql_query($sql);
		}
	}
}

// Duplicate Document TVs
function duplicateTVs($oldid,$newid){

	global $mysqlVerOk;
	global $dbase, $table_prefix;

	if($mysqlVerOk) {
		$sql = "INSERT INTO $dbase.`".$table_prefix."site_tmplvar_contentvalues` (contentid, tmplvarid, value) 
				SELECT $newid, tmplvarid,value  
				FROM $dbase.`".$table_prefix."site_tmplvar_contentvalues` WHERE contentid=$oldid;";
		$rs = mysql_query($sql);
	}
	else {
		$sql = "SELECT $newid as 'newid', tmplvarid, value  
				FROM $dbase.`".$table_prefix."site_tmplvar_contentvalues` WHERE contentid=$oldid;";
		$ds = mysql_query($sql);
		while($row = mysql_fetch_assoc($ds)) {
			$sql = "INSERT INTO $dbase.`".$table_prefix."site_tmplvar_contentvalues`  
					(contentid, tmplvarid,value) VALUES
					(".$row['newid'].", '".$row['tmplvarid']."','".mysql_escape_string($row['value'])."');";
			$rs = mysql_query($sql);
		}
	}
}

// Duplicate Document Access Permissions
function duplicateAccess($oldid,$newid){

	global $mysqlVerOk;
	global $dbase, $table_prefix;

	if($mysqlVerOk) {
		$sql = "INSERT INTO $dbase.`".$table_prefix."document_groups` (document, document_group) 
				SELECT $newid, document_group 
				FROM $dbase.`".$table_prefix."document_groups` WHERE document=$oldid;";
		$rs = mysql_query($sql);
	}
	else {
		$sql = "SELECT $newid as 'newid', document_group 
				FROM $dbase.`".$table_prefix."document_groups` WHERE document=$oldid;";
		$ds = mysql_query($sql);
		while($row = mysql_fetch_assoc($ds)) {
			$sql = "INSERT INTO $dbase.`".$table_prefix."document_groups` 
					(document, document_group) VALUES
					('".$row['newid']."', '".$row['document_group']."');";
			$rs = mysql_query($sql);
		}
	}
}

?>