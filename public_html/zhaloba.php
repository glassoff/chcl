
<?php require_once('conn.php');  

	error_reporting(0);	
				
mysql_select_db($database_fp, $fp);
mysql_query("set CHARACTER SET cp1251");



if (isset($_POST["name"])) {
$error = false;
			$code = $_SESSION['veriword'] ? $_SESSION['veriword'] : $_SESSION['eForm.VeriCode'];
			if($_POST['vericode']!=$code) {
				$error = true;
				echo '<div class="message">�������� ���</div>';
			}	
if(!$error):
$name = $_POST['name'];

$email = $_POST['email'];
$phone = $_POST['phone'];
$link = $_POST['link'];
$company =$_POST['company'];
$comment = $_POST['comment'];
$tovar_id=$_POST['tovar_id'];
$tovar = $_POST['tovar'];


$time = time();
$date = date(' Y-m-d , H:i:s'  );





$headers  = "Content-type: text/html; charset=windows-1251 \r\n";
$headers .= "From: info@chcl.ru\r\n";

mail("stivin@yandex.ru,shutov@chcl.ru,info@chcl.ru", "������ �� ����", "

<br>
������ �� ����:<br>
$tovar  - http://chcl.ru/catalog/item?id=$tovar_id; <br><br><br>

<br><br>
���: $name; <br><br>

�-����: <a href=mailto:$email>$email </a><br><br>
����������  �������: $phone <br><br>
������ �� ����� � ������� �����: $link <br><br>
��������: $company <br><BR>
���. ����: $comment <br><br>



",  $headers);


?>
 <p><b>������ �������. </b><br><br>
 <a href="http://chcl.ru/catalog/item?id=<?php echo $tovar_id;?>"><-- ��������� � ������</a></p>

<?php
endif;
} 
if (isset($_GET['t_id']) || $error) {


$t_id=$_GET['t_id'];

$sql = "Select pagetitle  From modx_site_ec_items Where id=$t_id limit 1";
$tovar= mysql_query($sql, $fp);
$row_tovar = mysql_fetch_assoc($tovar);

?>

<p  id="alert"></p>

<p>������������ �� ���� ������ "<?php echo $row_tovar['pagetitle']; ?>"
</p>


<form action="" method="POST" name="zhaloba">


<div class="space1"></div>
<table cellpadding="0" cellspacing="1" width="100%" class="content_table">

  


  <tr>

    <td>���������� ����*:</td>
    <td align="left"><input type="text" name="name" value="<?php echo $_POST['name']?>"></td>
    </tr>
 
 <tr>
    <td>Email*:</td>
    <td align="left"><input type="text" name="email" value="<?php echo $_POST['email']?>"></td>
    </tr>
      
      
      <tr>
    <td>������ �� �����
� ������� �����*:</td>
    <td align="left"><input type="text" name="link" value="<?php echo $_POST['link']?>"></td>
    </tr>
    
    <tr>
      <td>��������:</td>
    <td align="left"><input type="text" name="company" value="<?php echo $_POST['company']?>"></td>
    </tr>
    
     <tr>
     <td>�������:</td>
    <td align="left"><input type="text" name="phone" value="<?php echo $_POST['phone']?>"></td>
    </tr>
    
  
    
    <tr>

    <td>�������������� ����������:</td>
    <td align="left"><textarea name="comment" rows="5" cols="45" ><?php echo $_POST['comment']?></textarea>
    <input type="hidden" name="tovar" value="<?php echo $row_tovar['pagetitle']; ?>">
    <input type="hidden" name="tovar_id" value="<?php echo $t_id; ?>">
    </td>
    </tr>
    
    <tr>
    	<td>
    		������� ���, ��������� �� �������� <br>
    		<?php $_SESSION['eForm.VeriCode'] = substr(uniqid(''),-5);?>
    	</td>
    	<td>
    		<img src="<?php echo $modx->config['base_url'].'manager/includes/veriword.php?rand='.rand() ?>" />
    		<input type="text" name="vericode" value="" />
    	</td>
    </tr>

    
  
   
</table>    
<div class="space1"></div>


<input type="button" name="placeorder"  value="���������"  onclick="return checkForm();">
</form>
<div class="space1"></div>
<br><br>
<a  href="javascript: history.back()">&larr;&nbsp; �����</a>


<script type="text/javascript">
   function text (str) { return /[0-9_;:'!~?=+<|>]/g.test(str); }

   function numeric (str) { return /^[0-9-\+\(\)\s]+z/.test(str + "z"); }

   function mail (str) { return /^[a-z0-9_\-.]+@[a-z0-9_\.]+.[a-z]{2,3}$/.test(str); }

   function checkForm ()
      {
      var title;
      var elem;
      var dutyField = "�� ��������� ���� ";
      var wrongField = "�������� �������� ���� ";
      var check = true;

      function checkError (field, str)
         {
         document.getElementById("alert").innerHTML = str;
         $("#alert").addClass('message');
         document.forms.zhaloba.field.focus();
         check = false;
         }

      document.getElementById("alert").innerHTML = "";



   
      if (check)
         {
         title = '"���������� ����"';
         elem = document.zhaloba.name.value;
         if (elem.length == 0) checkError('name', dutyField + title);
         else if (text(elem)) checkError('name', wrongField + title);
         }
         
       
         
      if (check)
         {
         title = '"Email"';
         elem = document.zhaloba.email.value;
         if (elem.length == 0) checkError('email', dutyField + title);
         else if (!mail(elem)) checkError('email', wrongField + title);
         }

     
            if (check)
         {
         title = '"������ �� ������ ������� �����"';
         elem = document.zhaloba.link.value;
         if (elem.length == 0) checkError('link', dutyField + title);
         
         }
         
      
          
         
      if (check)  { $("#alert").removeClass('message'); document.zhaloba.submit(); }

      return check;
      }
</script>




<?php


}

?>
