<?php 

/*
 * &tplid
 * &contentTpl
 * */

$output = "";
if($tplid){
    $sql = "SELECT content FROM modx_site_templates WHERE (id='$tplid')";
    $result = $modx->db->query($sql);
    $row = $modx->db->getRow($result);
    
    $output = $row['content'];
    
    if($contentTpl){
        $output = str_replace('[*#content*]', '{{'.$contentTpl.'}}', $output);
    }
}
echo $output;

?>
