<?php 

$id = isset($docID) ? intval($docID) : $modx->documentIdentifier;
$deep = isset($deep) ? intval($deep) : 5;
$showinmenu = isset($showinmenu) ? intval($showinmenu) : 0;
$childs = $modx->getChildIds($id, $deep);
if (is_array($childs) && count($childs)>0) $childs = implode(',',$childs);
else $childs = $id;
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

?>
