<?php 

$sql = 'SELECT * FROM modx_ec_settings WHERE setting_name="new_year"';

$res = mysql_query($sql);
$row = mysql_fetch_assoc($res);
if ($row['setting_value']==1){

$style= '<link href="assets/templates/cd/css/css2.css" rel="stylesheet" type="text/css"/><script type="text/javascript" src="/assets/js/snow.js"></script>';
}

else 
{

$style= '<link href="assets/templates/cd/css/css.css" rel="stylesheet" type="text/css"/>';
}
return $style;

?>
