<?php 


$id = isset($docID) ? intval($docID) : $modx->documentIdentifier;

if($id==5  ) {


$output = ''; 
$output = $modx->getChunk('filter');  
echo $output;

 }

?>
