<?php 
 



if ($_SESSION['rozn']!='1')

{
echo '<tr><td colspan=2 style="padding-top:6px;"><center>
<form  action="'.MODX_SITE_URL.'" method="POST">    
<input type="hidden" name="rozn" value="1">
  <input type="submit"   value="&nbsp;" class="rozn_but">
</form>
</center>
</td></tr>';

} else 
{
echo '<tr><td colspan=2 style="padding-top:6px;"><center>
<form  action="'.MODX_SITE_URL.'" method="POST">    
<input type="hidden" name="rozn" value="0">
  <input type="submit"   value="&nbsp;" class="opt_but">
</form>
</center>
</td></tr>';

}



?>

?>
