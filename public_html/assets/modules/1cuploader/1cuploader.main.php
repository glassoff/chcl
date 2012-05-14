<?
/**
 * Document Manager Module
 * 
 * Purpose: Allows for the bulk management of key document settings.
 * Author: Garry Nutting (Mark Kaplan - Menu Index functionalty, Luke Stokes - Document Permissions concept)
 * For: MODx CMS (www.modxcms.com)
 * Date:29/09/2006 Version: 1.6
 *
 */

global $theme;
global $table;
global $_lang;
global $siteURL;
global $_1c_data_dir;

$basePath = $modx->config['base_path'];
$siteURL = $modx->config['site_url'];
$_1c_data_dir = $basePath.'assets/modules/1cuploader/data/';

/** CONFIGURATION SETTINGS **/

//-- set to false to hide the 'Select Tree' option

/** END CONFIGURATION SETTINGS **/

//-- include language file
$manager_language = $modx->config['manager_language'];
$sql = "SELECT setting_name, setting_value FROM ".$modx->getFullTableName('user_settings')." WHERE setting_name='manager_language' AND user=" . $modx->getLoginUserID();
$rs = $modx->db->query($sql);
if ($modx->db->getRecordCount($rs) > 0) {
    $row = $modx->db->getRow($rs);
    $manager_language = $row['setting_value'];
}
include_once $basePath.'assets/modules/1cuploader/lang/russian.inc.php';
if($manager_language!="english") {
if (file_exists($basePath.'assets/modules/1cuploader/lang/'.$manager_language.'.inc.php')) {
     include_once $basePath.'assets/modules/1cuploader/lang/'.$manager_language.'.inc.php';
}
}

//-- get theme
$tb_prefix = $modx->db->config['table_prefix'];
$theme = $modx->db->select('setting_value', '`' . $tb_prefix . 'system_settings`', 'setting_name=\'manager_theme\'', '');
$theme = $modx->db->getRow($theme);
$theme = ($theme['setting_value'] <> '') ? '/' . $theme['setting_value'] : '';

//-- setup initial vars
$table = $modx->getFullTableName('site_content');
$output = '';
$error = '';

//-- include php files
include_once $basePath.'manager/includes/controls/datagrid.class.php';
include_once $basePath.'assets/modules/1cuploader/includes/interaction.inc.php';
include_once $basePath.'assets/modules/1cuploader/includes/process.inc.php';
//showInfo
//-- get POST vars
$tabAction = (isset ($_POST['tabAction'])) ? $_POST['tabAction'] : ''; // get action for active tab

//-- process POST actions if required
if ($tabAction == '_1c_upload_data') {
    $output .= upload1CData($_POST['_1c_data_file']);
}  elseif ($tabAction == '_1c_change_settings') {
	$output .= change1CSettings($_POST['_1_settings']);
}

//-- render tabbed output
//--- HEAD
$output .= ' <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html '.($modx->config['manager_direction'] == 'rtl' ? 'dir="rtl"' : '').' lang="'.$modx->config['manager_lang_attribute'].'"> 
        <head>
        <title>'.$_lang['1C_module_title'].'</title> 
        <script type="text/javascript">var MODX_MEDIA_PATH = "media";</script>
        <link rel="stylesheet" type="text/css" href="media/style' . $theme . '/style.css" /> 
        <link rel="stylesheet" type="text/css" href="media/style' . $theme . '/coolButtons2.css" /> 
        <link rel="stylesheet" type="text/css" href="media/style' . $theme . '/tabs.css"/> 
        <script type="text/javascript" src="media/script/scriptaculous/prototype.js"></script> 
        <script type="text/javascript" src="media/script/scriptaculous/scriptaculous.js"></script> 
        <script type="text/javascript" src="media/script/modx.js"></script> 
        <script type="text/javascript" src="media/script/cb2.js"></script> 
        <script type="text/javascript" src="media/script/tabpane.js"></script>  
        <script type="text/javascript" src="../assets/modules/1cuploader/js/functions.js"></script>
        <script type="text/javascript" src="media/script/datefunctions.js"></script>
        <script type="text/javascript">
        function save()
        {
            document.newdocumentparent.submit();
        }   
';

$output.='</script>';
$output.= buttonCSS();
$output.='
        </head>
        <body>
        <div class="subTitle" id="bttn"> 
                <span class="right"><img src="media/style' . $theme . '/images/_tx_.gif" width="1" height="5" alt="" /><br />' . $_lang['1C_module_title'] . '</span> 
                <div class="bttnheight"><a id="Button5" onclick="document.location.href=\'index.php?a=106\';">
                    <img src="media/style' . $theme . '/images/icons/close.gif" alt="" /> '.$_lang['1C_close'].'</a>
                </div> 
                <div class="stay"></div> 
        </div> 
    ';
        
//--- TABS
$output.= '<div class="sectionHeader">&nbsp;' . $_lang['1C_action_title'] . '</div>
           <div class="sectionBody"> 
           <div class="tab-pane" id="1cuploaderPane"> 
           <script type="text/javascript"> 
                tpResources = new WebFXTabPane( document.getElementById( "1cuploaderPane" ) ); 
           </script>';

//--- Info         
$output.= '<div class="tab-page" id="tabInfo">  
        <h2 class="tab">' . $_lang['1C_tab_info'] . '</h2>  
        <script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabInfo" ) );</script> 
        ';
$output.=show1CInfo();
$output.='</div>';

//--- Upload       
$output.= '<div class="tab-page" id="tabUploadData">  
        <h2 class="tab">' . $_lang['1C_tab_upload_data']. '</h2>  
        <script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabUploadData" ) );</script> 
        ';
$output.=show1CUploadForm();   
$output.='</div>';

//--- Settings         
$output.= '<div class="tab-page" id="tabChangeSettings">  
        <h2 class="tab">' . $_lang['1C_tab_change_settings']. '</h2>  
        <script type="text/javascript">tpResources.addTabPage( document.getElementById( "tabChangeSettings" ) );</script> 
        ';
$output.=showSettingChangeForm();   
$output.='</div>';

$output.='</div></div></div>';

//-- send output
$output.='</body></html>';
return $output;
?>