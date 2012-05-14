<?php
/*
*************************************************************************
	MODx Content Management System and PHP Application Framework 
	Managed and maintained by Raymond Irving, Ryan Thrash and the
	MODx community
*************************************************************************
	MODx is an opensource PHP/MySQL content management system and content
	management framework that is flexible, adaptable, supports XHTML/CSS
	layouts, and works with most web browsers, including Safari.

	MODx is distributed under the GNU General Public License	
*************************************************************************

	MODx CMS and Application Framework ("MODx")
	Copyright 2005 and forever thereafter by Raymond Irving & Ryan Thrash.
	All rights reserved.

	This file and all related or dependant files distributed with this filie
	are considered as a whole to make up MODx.

	MODx is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	MODx is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with MODx (located in "/assets/docs/"); if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA

	For more information on MODx please visit http://modxcms.com/
	
**************************************************************************
    Originally based on Etomite by Alex Butter
**************************************************************************
*/	

/**
 * Initialize Document Parsing
 * -----------------------------
 */

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
 
// is this file included?
//if(count(get_included_files())>1) $noparser = true;
if(count(get_included_files())>1) {
if (array_search(__FILE__,get_included_files())>0)
	$noparser = true;
}

// get start time
$mtime = microtime(); $mtime = explode(" ",$mtime); $mtime = $mtime[1] + $mtime[0]; $tstart = $mtime;

// harden it
require_once(dirname(__FILE__).'/manager/includes/protect.inc.php');

// set some settings, and address some IE issues
@ini_set('url_rewriter.tags', '');
@ini_set('session.use_trans_sid', 0);
@ini_set('session.use_only_cookies',1);
session_cache_limiter('');
header('P3P: CP="NOI NID ADMa OUR IND UNI COM NAV"'); // header for weird cookie stuff. Blame IE.
header('Cache-Control: private, must-revalidate');
ob_start();
error_reporting(E_ALL & ~E_NOTICE);

/**
 *	Filename: index.php
 *	Function: This file loads and executes the parser. *
 */

define("IN_ETOMITE_PARSER", "true"); // provides compatibility with etomite 0.6 and maybe later versions
define("IN_PARSER_MODE", "true");
define("IN_MANAGER_MODE", "false");

// initialize the variables prior to grabbing the config file
$database_type = '';
$database_server = '';
$database_user = '';
$database_password = '';
$dbase = '';
$table_prefix = '';
$base_url = '';
$base_path = '';

// get the required includes
if($database_user=="") {
	$rt = @include_once(dirname(__FILE__).'/manager/includes/config.inc.php');
	// Be sure config.inc.php is there and that it contains some important values
	if(!$rt || !$database_type || !$database_server || !$database_user || !$dbase) {
	echo "
<style type=\"text/css\">
*{margin:0;padding:0}
body{margin:50px;background:#eee;}
.install{padding:10px;border:5px solid #f22;background:#f99;margin:0 auto;font:120%/1em serif;text-align:center;}
p{ margin:20px 0; }
a{font-size:200%;color:#f22;text-decoration:underline;margin-top: 30px;padding: 5px;}
</style>
<div class=\"install\">
<p>MODx is not currently installed or the configuration file cannot be found.</p>
<p>Do you want to <a href=\"install/index.php\">install now</a>?</p>
</div>";
		exit;
	}
}

// start session 
startCMSSession();

// initiate a new document parser
include_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');
$modx = new DocumentParser;
$etomite = &$modx; // for backward compatibility

$modx->aliveplaceholders = array('metatags','itemtitle','jot.html.navigation','jot.html.form','jot.html.comments','jot.html.subscribe','jot.html.moderate');


// set some parser options
include_once(MODX_BASE_PATH . '/manager/includes/extenders/phx/phx.parser.class.inc.php');
$modx->phx = new PHxParser;   

$modx->minParserPasses = 1; // min number of parser recursive loops or passes
$modx->maxParserPasses = 10; // max number of parser recursive loops or passes
$modx->dumpSQL = false;
$modx->dumpSnippets = false; // feed the parser the execution start time
$modx->tstart = $tstart;

// Debugging mode:
$modx->stopOnNotice = false;

// Don't show PHP errors to the public
if(!isset($_SESSION['mgrValidated']) || !$_SESSION['mgrValidated']) @ini_set("display_errors","0");

// execute the parser if index.php was not included
if(!$noparser) $modx->executeParser();
exit;
?>