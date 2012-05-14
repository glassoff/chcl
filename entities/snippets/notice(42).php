<?php 

global $modx;   
          

$u_id = $modx->getLoginUserID('web');
if (isset ($_POST['disc_id'])){

$disc_id = $_POST['disc_id']; 

$sql1 ="Select id From modx_notice Where user_id =$u_id   and disc_id=$disc_id and active=1 limit 1";
$rt= mysql_query($sql1);
$rr = mysql_fetch_assoc($rt);

if ($rr['id']) {echo "Вы уже подписались на эту позицию";}
else {


$sql = "INSERT INTO modx_notice  VALUES ('', '$u_id', '$disc_id', '1', '' ) ";

$res = mysql_query($sql);


if(!$res) {echo "ошибка"; }
else {



$sql2 = "Select email From modx_web_user_attributes Where internalKey=$u_id  ";
$res2 = mysql_query($sql2);
$row2 = mysql_fetch_assoc($res2);

echo "Ваша заявка принята. Как только диск появится в продаже, вам будет выслано уведомление на Ваш электронный адрес: <b> ".$row2['email']."</b>";
}


}


}

?>
