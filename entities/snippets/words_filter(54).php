<?php 

mysql_query("set CHARACTER SET cp1251");

$query= "SELECT * FROM word_filter";
$rs = $modx->dbQuery($query);
$row= mysql_fetch_assoc($rs);


do{

 $a='f'.$row['id'];
?>
<input type="checkbox" value="<?php echo $row['words']; ?>" name="<?php echo $a;?>" <?php  if (isset($_POST[$a]))  echo 'checked' ; ?> ><?php echo $row['zag']; ?>&nbsp; &nbsp;&nbsp;&nbsp;

<?php
} while ($row=mysql_fetch_assoc($rs));

?>
