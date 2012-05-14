<?php 

$wayfinder_base = $modx->config['base_path']."assets/snippets/wayfinder/";
//Include a custom config file if specified
$config = (isset($config)) ? "{$wayfinder_base}configs/{$config}.config.php" : "{$wayfinder_base}configs/default.config.php";
if (file_exists($config)) {
    include_once("$config");
}

include_once("{$wayfinder_base}wayfinder.inc.php");

if (class_exists('Wayfinder')) {
   $wf = new Wayfinder();
} else {
    return 'error: Wayfinder class not found';
}


ini_set('display_errors', 1);
error_reporting(E_ALL); // better set to
error_reporting(E_ALL|E_STRICT);

$wf->_config = array(
    'id' => isset($startId) ? $startId : $modx->documentIdentifier,    
    'hereid' => isset($hereid) ? intval($hereid) : 0,
    'level' => isset($level) ? $level : 0,
    'columns' => isset($columns) ? $columns : 0,
    'includeDocs' => isset($includeDocs) ? $includeDocs : 0,
    'excludeDocs' => isset($excludeDocs) ? $excludeDocs : 0,
    'ph' => isset($ph) ? $ph : FALSE,
    'debug' => isset($debug) ? TRUE : FALSE,
    'ignoreHidden' => isset($ignoreHidden) ? $ignoreHidden : FALSE,
    'hideSubMenus' => isset($hideSubMenus) ? $hideSubMenus : FALSE,
    'useWeblinkUrl' => isset($useWeblinkUrl) ? $useWeblinkUrl : TRUE,
    'fullLink' => isset($fullLink) ? $fullLink : FALSE,
    'nl' => isset($removeNewLines) ? '' : "\n",
    'sortOrder' => isset($sortOrder) ? strtoupper($sortOrder) : 'ASC',
    'sortBy' => isset($sortBy) ? $sortBy : 'menuindex',
    'limit' => isset($limit) ? $limit : 0,
    'cssTpl' => isset($cssTpl) ? $cssTpl : FALSE,
    'jsTpl' => isset($jsTpl) ? $jsTpl : FALSE,
    'rowIdPrefix' => isset($rowIdPrefix) ? $rowIdPrefix : FALSE,
    'textOfLinks' => isset($textOfLinks) ? $textOfLinks : 'menutitle',
    'titleOfLinks' => isset($titleOfLinks) ? $titleOfLinks : 'pagetitle',
    'displayStart' => isset($displayStart) ? $displayStart : FALSE
);

//get user class definitions
$wf->_css = array(
    'first' => isset($firstClass) ? $firstClass : '',
    'last' => isset($lastClass) ? $lastClass : 'last',
    'here' => isset($hereClass) ? $hereClass : 'active',
    'parent' => isset($parentClass) ? $parentClass : '',
    'row' => isset($rowClass) ? $rowClass : '',
    'outer' => isset($outerClass) ? $outerClass : '',
    'inner' => isset($innerClass) ? $innerClass : '',
    'level' => isset($levelClass) ? $levelClass: '',
    'self' => isset($selfClass) ? $selfClass : '',
    'weblink' => isset($webLinkClass) ? $webLinkClass : ''
);

//get user templates
$wf->_templates = array(
    'outerTpl' => isset($outerTpl) ? $outerTpl : '',
    'cellOuterTpl' => isset($cellOuterTpl) ? $cellOuterTpl : '',
    'rowTpl' => isset($rowTpl) ? $rowTpl : '',
    'parentRowTpl' => isset($parentRowTpl) ? $parentRowTpl : '',
    'parentRowHereTpl' => isset($parentRowHereTpl) ? $parentRowHereTpl : '',
    'hereTpl' => isset($hereTpl) ? $hereTpl : '',
    'innerTpl' => isset($innerTpl) ? $innerTpl : '',
    'innerRowTpl' => isset($innerRowTpl) ? $innerRowTpl : '',
    'innerHereTpl' => isset($innerHereTpl) ? $innerHereTpl : '',
    'activeParentRowTpl' => isset($activeParentRowTpl) ? $activeParentRowTpl : '',
    'categoryFoldersTpl' => isset($categoryFoldersTpl) ? $categoryFoldersTpl : '',
    'startItemTpl' => isset($startItemTpl) ? $startItemTpl : ''
);

//Process Wayfinder
$output = $wf->run();

if ($wf->_config['debug']) {
    $output .= $wf->renderDebugOutput();
}

//Ouput Results
if ($wf->_config['ph']) {
    $modx->setPlaceholder($wf->_config['ph'],$output);
} else {
    return $output;
}

?>
