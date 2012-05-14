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
$basePath = $modx->config['base_path'];
$siteURL = $modx->config['site_url'];

$output = '';
$error = '';

//-- include php files
ob_start();
include_once $basePath.'manager/includes/header.inc.php';
include_once $basePath.'assets/modules/catalogx/includes/mutate_catalogx_dynamic.php';
include_once $basePath.'manager/includes/footer.inc.php';
$output = ob_get_contents();
ob_end_clean();
return $output;
?>