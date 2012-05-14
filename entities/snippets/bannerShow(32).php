<?php 

$ids = isset($ids) ? $ids : '0'; 
$bannerTpl = isset($bannerTpl) ? $bannerTpl : '0'; 
$output = '';
$id_array = explode(',',$ids);
if (is_array($id_array) && sizeof($id_array)>0) {
    $rindex = rand(0,sizeof($id_array)-1);
    $rid = $id_array[$rindex];    
    $banner = $modx->getTemplateVars(array('banner_link','banner_img','banner_title','banner_target'), "name", $rid,1);       
    if (count($banner)>0) {     
        $output = $modx->getChunk($bannerTpl);        
        for($i=0;$i<count($banner);$i++)  {               
           $v = $banner[$i];               
           $output = str_replace("[+".$v['name']."+]", $v['value'], $output); 
        }       
    }   
}
return $output;

?>
