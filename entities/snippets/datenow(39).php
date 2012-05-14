<?php 

$m=date('m');
$d=date('d');
$Y=date('Y');


$date_now = mktime(0, 0, 0, $m, $d, $Y);
return $date_now;

?>
