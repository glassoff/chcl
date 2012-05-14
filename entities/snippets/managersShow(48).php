<?php 

$ids = isset($ids) ? $ids : '0'; 
$managersTpl = isset($managersTpl) ? $managersTpl : '0';  $output = '';
$id_array = explode(',',$ids);
if (is_array($id_array) && sizeof($id_array)>0) {
    $rindex = rand(0,sizeof($id_array)-1);
    $rid = $id_array[$rindex];    
    
    $manager= $modx->getTemplateVars(array('m_email','m_photo','m_phone','skype_login','m_fio'), "name", $rid,1);          
    if (count($manager)>0) {     
        $output = $modx->getChunk($managersTpl);        
        for($i=0;$i<count($manager);$i++)  {               
           $v = $manager[$i];               
           $output = str_replace("[+".$v['name']."+]", $v['value'], $output); 
        }       
    }  
}

return $output;

?>
