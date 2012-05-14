<?php 

$tblsc = $modx->getFullTableName("site_ec_items");        
$sql = "SELECT count(id) as cnt FROM {$tblsc} WHERE published=1 AND deleted=0 ";       
$result = $modx->dbQuery($sql);         
$numResults = @$modx->recordCount($result);
if ($numResults == 1) {
   $row = $modx->fetchRow($result);            
   return $row['cnt'];
} else return 0;

?>
