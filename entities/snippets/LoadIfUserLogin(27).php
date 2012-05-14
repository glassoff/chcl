<?php 

if ($modx->getLoginUserID('web')) {
return $modx->getChunk($output);
}

?>
