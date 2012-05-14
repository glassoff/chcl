<?php 

if ($modx->getLoginUserID('web') || $modx->getLoginUserID('mgr')) {
  return 1;
} else {
  return 0;
}

?>
