<?php 
 

$parent = isset($parent) ? $parent : 0;
$ids = isset($ids) ? $ids : '0'; 
$bannerTpl = isset($bannerTpl) ? $bannerTpl : '0'; 
$output = '';

$id_array = explode(',',$ids);

if($parent){
    $children = $modx->getChildIds($parent, 1);
    $id_array = array_values($children);
    $keys = array_keys($id_array);
    
    if(isset($_COOKIE['homebanners']) && $_COOKIE['homebanners']){
        $order = $_COOKIE['homebanners'];
        $orderArr = explode(',', $order);
        if(count($orderArr) >= count($id_array)){
            $keys = $orderArr;
        }
        else{
            setcookie('homebanners', '');
        }
    }
    else{
        shuffle($keys);
        setcookie('homebanners', implode(',', $keys), time()+24*3600);
    }
    
}

$keys = array_merge((array)$keys, array());

$ii = 1;
foreach($keys as $key){
    $rid = $id_array[$key]; 
    if(!$rid)
        continue;
        
    $banner = $modx->getTemplateVars(array('banner_link','banner_img','banner_title','banner_target'), "name", $rid,1);       
    if (count($banner)>0) {     
        $output1 = $modx->getChunk($bannerTpl);        
        for($i=0;$i<count($banner);$i++)  {               
           $v = $banner[$i];               
           $output1 = str_replace("[+".$v['name']."+]", $v['value'], $output1); 
        }       
    } 
    if($ii%3 > 0){
        $output1 .= '&nbsp;&nbsp;'; 
    }
    else{
        $output1 .= '<br><br>';
    }
    $output .= $output1;
    $ii++;
}  
return $output;


?>
