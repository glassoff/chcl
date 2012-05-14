<?php 

global $modx;  

if ( isset($_GET['key'])  ) 
{



$key = $_GET['key'];
$id = $_GET['id'];


$sql = "Select send, sending_comp, sending_video, sending_pr From modx_web_user_attributes where id=$id and internalKey=$key ";
    $rs = mysql_query($sql);
    $row = mysql_fetch_assoc($rs);
    
    if ($row['sending_comp']==1) $c=1; else $c=0;
if ($row['sending_video']==1) $v=1; else $v=0;
if ($row['sending_pr']==1) $g=1; else $g=0;

    

if ($row['send']==1) {

    $sql1 = "select modx_site_ec_items.parent 
        from    `modx_site_ec_orders`, `modx_site_ec_order_items`,
        `modx_site_ec_items`
    
Where id =$id and send=1 and  modx_site_ec_order_items.order_id = modx_site_ec_orders.id and modx_site_ec_items.id = modx_site_ec_order_items.item_id  
";
    
    $rs1 = mysql_query($sql1);
    if ($rs1) {
    $row1 = mysql_fetch_assoc($rs1);
    
   
   $parent = $row1['parent'];
  
   
    
    while ($parent != 0) {

$sql9 = "select id, parent from $dbase.`".$table_prefix."site_content` where id = $parent";
    $rs9 = mysql_query($sql9);
    $row9 = mysql_fetch_assoc($rs9);

$parent=$row9['parent'];
$i = $row9['id'];
}

} else $i=0;

}else {

$i=1;


}
?>
<form action="/cabinet/2516" method="post"  >
           
<input type="checkbox" name="comp" value="1" <?php if ($i==5 or $i==0 or $c==1) echo 'checked'; ?> />Компьютерные игры <br />
<input type="checkbox" name="dvd" value="1" <?php if ($i==2150 or i==0 or $v==1) echo 'checked'; ?> />DVD-фильмы <br />
<input type="checkbox" name="games" value="1" <?php if ($i==2225 or $i==2442 or $i==2441 or $i==2440 or $i==2471 or i==0 or $g==1) echo 'checked'; ?> />Приставочные игры<br />
<input type="hidden" name="id" value="<?php echo $id; ?>" >
<input type="hidden" name="send" value="0" >
 <br>
 <br> 
<input type="submit" value="Сохранить">        
</form>

<?php
}

if ( isset($_POST['send'])  ) 
{

$comp=0; $dvd=0; $games=0;


if ( isset($_POST['comp'])  )  $comp = $_POST['comp'];
if ( isset($_POST['dvd'])  ) $dvd = $_POST['dvd'];
if ( isset($_POST['games'])  ) $games =$_POST['games'];
$id = $_POST['id'];

$sql5 = "UPDATE  modx_web_user_attributes  SET sending_comp=$comp, sending_video=$dvd, sending_pr=$games, send=0  where id=$id  ";
$rs5 = mysql_query($sql5);

echo " <p>Настройки рассылки успешно сохранены.</p> <p><br /> <br /> <a href=\"javascript: history.back()\">&larr;&nbsp; Назад </a></p>";

}

?>
