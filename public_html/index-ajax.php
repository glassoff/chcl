<?php
// harden it
require_once('./manager/includes/protect.inc.php');
// initialize the variables prior to grabbing the config file
// get the required includes
// initiate a new document parser
if (isset($_GET['rid'])) {
	if (!$rt = @include_once "manager/includes/config.inc.php") {
    exit('Could not load MODx configuration file!');
}
	if(!$modxDBConn = mysql_connect($database_server, $database_user, $database_password)) {
    	die("<h2>Failed to create the database connection!</h2>. Please run the MODx <a href='../install'>install utility</a>");
	} 
	mysql_select_db($dbase);
	@mysql_query("SET CHARACTER SET {$database_connection_charset}");
	@mysql_query("SET CHARACTER SET 'cp1251_general_ci'",$modxDBConn);    
	mysql_query("SET NAMES 'cp1251';", $modxDBConn);
	mysql_query("SET character_set_results = cp1251';", $modxDBConn);
	mysql_query("SET collation_connection = 'cp1251_general_ci';", $modxDBConn);
	header('Content-Type: text/html; charset=windows-1251');
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	$rid = intval($_GET['rid']);
	$sql = "SELECT * FROM $dbase.`modx_site_ec_cities` WHERE rid= $rid order by listindex, name";
	$rs = mysql_query($sql) or die ('MYSQL: ' . mysql_error());
	$lines = array();		
	if ($rs && mysql_num_rows($rs)>0) {		
		$lines[] = '<select class="reg_field" name="state">';
		while ($row = mysql_fetch_assoc($rs)) {			
			$lines[] = '<option value="'.$row['id'].'"  >'.$row['name'].'</option>';
		}		
		$lines[] = '</select>';	
	} else {
		$lines[] = '<b>'.$_lang['ec_select_region'].'</b>';
	}	
	echo implode("\n", $lines);	
	exit;
}

if($axhandler = (strtoupper($_SERVER['REQUEST_METHOD'])=='GET') ? $_GET['q'] : $_POST['q']) {
  $axhandler = preg_replace('/[^A-Za-z0-9_\-\.\/]/', '', $axhandler);
  $axhandler = realpath($axhandler) or die(); 
  $directory = realpath(MODX_BASE_PATH.DIRECTORY_SEPARATOR.'/assets/snippets'); 
  $axhandler = realpath($directory.str_replace($directory, '', $axhandler));
  
  if($axhandler && (strtolower(substr($axhandler,-4))=='.php')) {
    include_once($axhandler);
    exit;
  }
}
?>