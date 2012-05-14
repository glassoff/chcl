<?php 

$childs = $modx->getActiveChildren($id, 'menuindex', 'ASC', 'id');

$return = '';

$ids = array($id);

foreach($childs as $child){
	$ids[] = $child['id'];
}

$return = implode(',', $ids);

return $return;
?>