<?php 

print '<form action="" method="post" style="margin: 0px; padding: 0px;"><table cellspacing="10" cellpadding="0" border="0">  <tbody>
<tr><td valign="middle"><select name="type">           
<option value="" '; if ($_POST['type']=='') echo 'selected';  print '>Все</option>             
<option value="1" '; if ($_POST['type']==1) echo 'selected';  print '>Больницы гинекологические</option>            
 <option value="2" '; if ($_POST['type']==2) echo 'selected';  print '>Больницы детские инфекционные</option>             
 <option value="3" '; if ($_POST['type']==3) echo 'selected';  print '>Больницы детские городские</option>             
 <option value="4" '; if ($_POST['type']==4) echo 'selected';  print '>Родильные дома</option>                
 <option value="5" '; if ($_POST['type']==5) echo 'selected';  print '>Центры восстановительного лечения для детей</option>                 
</select></td><td><input type="submit" value="  Применить  " name="" /></td> </tr></tbody></table></form><br />';

global $modx; $filter = "";
if (!empty($_POST['type'])) $filter .= (empty($filter)?"":"|")."tvtype,".mysql_escape_string($_POST['type']).",1"; 
if (!empty($_POST['address'])) $filter .= (empty($filter)?"":"|")."tvaddress,".mysql_escape_string($_POST['address'])." ,1"; 
if (!empty($_POST['phone'])) $filter .= (empty($filter)?"":"|")."tvphone,".mysql_escape_string($_POST['phone']) .",1"; 

return $modx->runSnippet('Ditto', array('parents'=>'2584', 'display'=>'30', 'language'=>'russian-UTF8', 'sortBy'=>'pub_date', 'tpl'=>'meduch',  
'dateSource'=>'pub_date', 'dateFormat'=>'%d.%m.%Y', 'paginate'=>'1', 
'filter'=>$filter, 'noResults'=>''));

?>
