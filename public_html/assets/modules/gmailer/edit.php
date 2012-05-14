<?php
	//only show the fatal errors
	error_reporting(E_ERROR | E_PARSE);

	//get database connection info and set up grid
	include_once('../../../manager/includes/config.inc.php');
	include_once('DrasticTools/drasticSrcMySQL.class.php');
$options = array (
	"add_allowed" => false,
	"delete_allowed" => false,
	"editablecols" => array ()
);
	
	$path = '';
	$dbase = str_replace('`', '', $dbase);
	$src = new drasticSrcMySQL($database_server, $database_user, $database_password, $dbase, $table_prefix . "temailinglist");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<link rel="stylesheet" type="text/css" href="DrasticTools/css/grid_default.css"/>
		<title>TE Mailer Mailing List</title>
	</head>
	<body>
		<script type="text/javascript" src="DrasticTools/js/mootools-1.2-core.js"></script>
		<script type="text/javascript" src="DrasticTools/js/mootools-1.2-more.js"></script>
		<script type="text/javascript" src="DrasticTools/js/drasticGrid.js"></script>
		<div id="mailingList"></div>
		<script type="text/javascript">
			var thegrid = new drasticGrid('mailingList', {
				pathimg: "DrasticTools/img/",
				pagelength: 30,    
				columns: [
					{name: 'name', displayname: 'Name', width: 150, editable: true},        
					{name: 'email', type: DDTYPEMAILTO, displayname: 'Email Address', width: 180, editable: true},
					{name: 'unsubscribe', displayname: 'Unsubscribed?', width: 95, editable: true},
					{name: 'sent', displayname: 'Sent?', width: 40, editable: true},
					{name: 'id', displayname: 'ID', width: 30, editable: false}
				]
			});
		</script>
	</body>
</html>