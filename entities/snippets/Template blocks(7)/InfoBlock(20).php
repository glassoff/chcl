<?php 

$docid = isset($docid)? $docid : 0;
$tvOutput = $modx->getTemplateVarOutput('content', $docid,1);
$output = $tvOutput['content'];
return $output;

?>
