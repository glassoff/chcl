<?php 

$id = isset($docID) ? intval($docID) : $modx->documentIdentifier;

  $sql = "SELECT id FROM ".$modx->getFullTableName("site_content")." WHERE id =$id";   
    $result = $modx->dbQuery($sql);

return $result;

?>
