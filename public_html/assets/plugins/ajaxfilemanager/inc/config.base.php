<?php
	/**
	 * sysem base config setting
	 * @author Logan Cai (cailongqun [at] yahoo [dot] com [dot] cn)
	 * @link www.phpletter.com
	 * @since 1/August/2007
	 *
	 */
	

error_reporting(E_ALL);	
//error_reporting(E_ALL ^ E_NOTICE);	
	
// ** START FOR MODx

require_once('../../../manager/includes/protect.inc.php');

// load configuration file
// initialize the variables prior to grabbing the config file
$database_type = '';
$database_server = '';
$database_user = '';
$database_password = '';
$dbase = '';
$table_prefix = '';
$base_url = '';
$base_path = '';
include("../../../manager/includes/config.inc.php");

/** 
 * Security check user MUST be logged into manager 
 * before being able to run this script
 */
startCMSSession(); 
if(!isset($_SESSION['mgrValidated'])) {
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
}
// connect to the database
if(@!$modxDBConn = mysql_connect($database_server, $database_user, $database_password)) {
	die("Failed to create the database connection!");
} else {
	mysql_select_db($dbase);
    @mysql_query("SET CHARACTER SET {$database_connection_charset}");
}

// Override system settings with user settings
define('IN_MANAGER_MODE', 'true'); // set this so that user_settings will trust us.
include("../../../manager/includes/settings.inc.php");
include("../../../manager/includes/user_settings.inc.php");

	//Access Control Setting
	/**
	 * turn off => 0
	 * by session => 1
	 */
	define('CONFIG_ACCESS_CONTROL_MODE', 0); 	
	define("CONFIG_LOGIN_USERNAME", 'ajax');
	define('CONFIG_LOGIN_PASSWORD', '123456');
	define('CONFIG_LOGIN_PAGE', 'ajax_login.php'); //the url to the login page
	
	//SYSTEM MODE CONFIG
		/**
		 * turn it on when you have this system for demo purpose
		*  that means changes made to each image is not physically applied to it.
		*/
	define('CONFIG_SYS_DEMO_ENABLE', 0); 
	
	//FILESYSTEM CONFIG
		/*
		* CONFIG_SYS_DEFAULT_PATH is the default folder where the files would be uploaded to
			and it must be a folder under the CONFIG_SYS_ROOT_PATH or the same folder
			these two paths accept relative path only, don't use absolute path
		*/
		
	define('CONFIG_SYS_DEFAULT_PATH', '../../'); //accept relative path only
	define('CONFIG_SYS_ROOT_PATH', '../../');	//accept relative path only
	define('CONFIG_SYS_FOLDER_SHOWN_ON_TOP', true); //show your folders on the top of list if true or order by name 
	define("CONFIG_SYS_DIR_SESSION_PATH", 'session/');
	define("CONFIG_SYS_PATTERN_FORMAT", 'reg'); //two options: reg and list, this option define the parttern format for the following patterns
		//reg: use regular expression; list=> use a list of files/folders deliminated by comma
	define('CONFIG_SYS_INC_DIR_PATTERN', ''); //leave empty if you want to include all foldler
	define('CONFIG_SYS_EXC_DIR_PATTERN', '/cache|docs|export|import|js|modules|plugins|site|snippets|templates/'); //leave empty if you want to include all folder
	define('CONFIG_SYS_INC_FILE_PATTERN', '');
	define('CONFIG_SYS_EXC_FILE_PATTERN', '/index\.htm|\.thumb/'); 
	define('CONFIG_SYS_DELETE_RECURSIVE', 1); //delete all contents within a specific folder if set to be 1
	
	//UPLOAD OPTIONS CONFIG
	define('CONFIG_UPLOAD_MAXSIZE', 8 * 1024 * 1024); //by bytes
	//define('CONFIG_UPLOAD_MAXSIZE', 2048); //by bytes
	//define('CONFIG_UPLOAD_VALID_EXTS', 'txt');//
	define('CONFIG_TEXT_EDITOR_VALID_EXTS', $upload_files); //make you include all these extension in CONFIG_UPLOAD_VALID_EXTS if you want all valid
	define('CONFIG_UPLOAD_VALID_EXTS', $upload_images.",".$upload_media.",".$upload_flash);//
	//define('CONFIG_UPLOAD_VALID_EXTS', 'gif,jpg,png,txt'); // 
	define('CONFIG_UPLOAD_INVALID_EXTS', '');

	//Preview
	define('CONFIG_IMG_PREVIEW_MAX_X', 260);
	define('CONFIG_IMG_PREVIEW_MAX_Y', 200);
	define('CONFIG_IMG_THUMBNAIL_MAX_X', 50);
	define('CONFIG_IMG_THUMBNAIL_MAX_Y', 50);
	
	
		/**
		 * CONFIG_URL_PREVIEW_ROOT was replaced by CONFIG_WEBSITE_DOCUMENT_ROOT since v0.8
		 * Normally, you don't need to bother with CONFIG_WEBSITE_DOCUMENT_ROOT
		 * Howerver, some Web Hosts do not have standard php.ini setting 
		 * which you will find the file manager can not locate your files correctly
		 * if you do have such issue, please change it to fit your system.
		 * so what should you to do get it
		 *   1. create a php script file (let's call it document_root.php)
		 *   2. add the following codes in in 
		 * 			<?php
		 * 				echo dirname(__FILE__);
		 * 			?>
		 *   3. upload document_root.php to you website root folder which will only be reached when you visit http://www.domain-name.com or http://localhost/ at localhost computer
		 *   4. run it via http://www.domain-name.com/document_root.php or http://localhost/docuent_root.php if localhost computer, the url has to be exactly like that 
		 *   5. the value shown on the screen is CONFIG_WEBSITE_DOCUMENT_ROOT should be
		 *   6. enjoy it

		        
		 * 		
		 */
		

	define('CONFIG_WEBSITE_DOCUMENT_ROOT', '');	
	//theme related setting
			/*
			*	options avaialbe for CONFIG_EDITOR_NAME are:
					stand_alone
					tinymce
					fckeditor
			*/

	switch($which_editor){
		case "TinyMCE":
			$fmeditor = 'tinymce';
		break;
		
		case "FCKEditor":
			$fmeditor = 'fckeditor';
		break;
		
		default:
			$fmeditor = 'stand_alone';
	}

	//CONFIG_EDITOR_NAME replaced CONFIG_THEME_MODE since @version 0.8			
	define('CONFIG_EDITOR_NAME', (CONFIG_QUERY_STRING_ENABLE && !empty($_GET['editor'])?secureFileName($_GET['editor']):$fmeditor)); 
	define('CONFIG_THEME_NAME', (CONFIG_QUERY_STRING_ENABLE && !empty($_GET['theme'])?secureFileName($_GET['theme']):'default'));  //change the theme to your custom theme rather than default
	
	
	//General Option Declarations
	//LANGAUGAE DECLARATIONNS
	define('CONFIG_LANG_INDEX', 'language'); //the index in the session
	define('CONFIG_LANG_DEFAULT', (CONFIG_QUERY_STRING_ENABLE && !empty($_GET['language'])?secureFileName($_GET['language']):'en')); //change it to be your language file base name, such en
?>