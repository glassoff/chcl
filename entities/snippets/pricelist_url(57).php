<?php 

$sql = "SELECT * FROM ".$modx->getFullTableName("ec_settings")." WHERE setting_name='pricelist_file'";
$rs =  $modx->dbQuery($sql);
if (mysql_num_rows($rs)>0){
    $row = mysql_fetch_array($rs);
    $current_pl = $row['setting_value'];
    $url = MODX_SITE_URL . $current_pl;
    return '<a id="price-button" href="'.$url.'"><img src="images/price5-2.jpg" border="0"/></a>';   
}

?>
