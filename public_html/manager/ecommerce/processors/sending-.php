<?php
if(IN_MANAGER_MODE!="true") die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if(!$modx->hasPermission('ec_settings')) {
	$e->setError(3);
	$e->dumpError();	
}

	$sending= $_POST['sending'];
	

	
	$sql = "Select id, email,  fname, lname, sname, internalKey   From $dbase.`".$table_prefix."web_user_attributes` where sending=1 and id=1939 ";
	$rs = mysql_query($sql);
    $row = mysql_fetch_assoc($rs);
	
	if ($row['id']!=0) {
	
	do {  
	
	 $id = $row['id'];
	$Key=$row['internalKey'];
	 $email = $row['email'];
	 $username =$row['fname'].' '.$row['sname'].' '.$row['lname'];	
	 

	
      	$sql1 = "select modx_site_ec_items.parent 
      	from     $dbase.`".$table_prefix."site_ec_orders`, $dbase.`".$table_prefix."site_ec_order_items`,
      	$dbase.`".$table_prefix."site_ec_items`
	
Where customer_id =$Key and  modx_site_ec_order_items.order_id = modx_site_ec_orders.id and modx_site_ec_items.id = modx_site_ec_order_items.item_id   GROUP BY customer_id  LIMIT 1
";
	
	$rs1 = mysql_query($sql1);
    $row1 = mysql_fetch_assoc($rs1);
    
   
   $parent = $row1['parent'];
  
   
    
    while ($parent != 0) {

$sql9 = "select id, parent from $dbase.`".$table_prefix."site_content` where id = $parent";
	$rs9 = mysql_query($sql9);
    $row9 = mysql_fetch_assoc($rs9);

$parent=$row9['parent'];
$id = $row9['id'];
 
}



/*
if ($id==5) { $a=2513;}
elseif ($id==2150) {$a=2514;} 
elseif ($id==2225 or $id==2442 or $id==2441 or $id==2440 or $id==2471)
{$a=2515;}
 elseif ($id==0) $a=2513; */
 
 
 
 if ( (($id==5 or id==0) and $sending==1) or   (($id==2150 or id==0) and $sending==2) or  (($id==2225 or $id==2442 or $id==2441 or $id==2440 or $id==2471 or id==0) and $sending==3)      ) 
 
 {

 
 if ($sending==1) $sending=2513;
 elseif ($sending==2) $sending=2514;
 elseif ($sending==3) $sending=2515;
	$a=$sending;

	
	
		$sql2 = "select *  from $dbase.`".$table_prefix."site_tmplvar_contentvalues` 
	
Where contentid = $a ";
	
	$rs2 = mysql_query($sql2);
    $row2 = mysql_fetch_assoc($rs2);
	

	
	$zag =''; $zag1 =''; $zag2 =''; $zag3 ='';
	$disc1=''; $disc2=''; $disc3=''; $main_text=''; $text2=''; $footer='';
	
	do {
	
	if ($row2['tmplvarid']==80) {
	$zag=$row2['value'];
	}
	elseif ($row2['tmplvarid']==72) {
	$zag1=$row2['value'];
	}
	elseif ($row2['tmplvarid']==73) {
	$zag2=$row2['value'];
	}
	elseif ($row2['tmplvarid']==74) {
	$zag3=$row2['value'];
	}
	elseif ($row2['tmplvarid']==75) {
	$disc1=$row2['value'];
	}
	elseif ($row2['tmplvarid']==76) {
	$disc2=$row2['value'];
	}
	elseif ($row2['tmplvarid']==77) {
	$disc3=$row2['value'];
	}
	elseif ($row2['tmplvarid']==81) {
	$main_text=$row2['value'];
	}
	elseif ($row2['tmplvarid']==78) {
	$text2=$row2['value'];
	}
	elseif ($row2['tmplvarid']==79) {
	$footer=$row2['value'];
	}
	
	  } while ($row2 = mysql_fetch_assoc($rs2));
	  
	  
	  
	$message='<TABLE width=700 cellspacing=0 cellpadding=0 border=0 class=pad_null  ><TR ><TD valign=top align=left >
<TABLE width=100% cellspacing=0 cellpadding=0 border=0 class=pad_null ><TR >
	<TD width=131 align=left >
	<a href=http://www.cddiski.ru><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/welcome.gif  hspace=15 vspace=0 ></a>
</TD>
	<TD bgcolor=#800000 width=16 align="left" valign="top"  >

<img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/sending_ug.jpg" width="19" height="16">
	</TD>
	<TD bgcolor=#800000 align="center"  >
<font face=Verdana, sans-serif size=4 color=#FFFFFF>
	'.$zag.'</font></TD>
	<TD bgcolor=#800000 width=16 align="left" valign="top"  >
&nbsp;</TD></TR></TABLE>
	
	<TABLE width=100% cellspacing=0 cellpadding=0 border=0 class=pad_null><TR >
		<TD valign=top> &nbsp;<p>
		<FONT size=2 face=Verdana, sans-serif >Здравствуйте, '.$username.'</FONT></TD></TABLE>
     &nbsp;


   <TABLE width=700 cellspacing=0 cellpadding=0 border=0 class=pad_null ><TR ><TD valign=top align=left >

		<br />'.$main_text.'
							
							<br><br>
';  


$message.='	<TABLE width=100% cellspacing=0 cellpadding=0 border=0 class=pad_null >
	
	
	<TR ><TD valign=top align=left >

	<span align="left"><font size="4" color="#620907"><b>	'.$zag1.' </b>	</font></span>
</TD></TR>

	
	<TR ><TD valign=top align=left >
<span align="left">
	<img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/sending_zg1.jpg" ></span></TD></TR>


</TABLE>
						<br><br>	';
	  
	  
	if($disc1>0)  {
	
	$disc1=explode(",", $disc1);
	
	$count = count($disc1);
	$i=0;
	for ($i=0; $i<$count; $i++) {
	
	
	$disc = $disc1[$i];
	
	
	

	
	$sql_ = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs_ = mysql_query($sql_);
    $row_ = mysql_fetch_assoc($rs_);
    
   
    
 
    $price = $row_['retail_price'];
 
      $pic =  $row_['value'];

      $title =   $row_['pagetitle']; 
  

	 }
	 
	}


	
	
	
	$message.='
	<TABLE width=100% cellspacing=0 cellpadding=0 border=0  class=pad_null>
		<tr>
			<TD valign=top align=center  width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc01.'"><img border="0"  src=http://www.cddiski.ru/'.$pic01.' width="120"></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc02.'" ><img border="0"  src=http://www.cddiski.ru/'.$pic02.' width="120" ></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center width="25%" ><a href="http://www.cddiski.ru/catalog/item?id='.$disc03.'"><img border="0"  src=http://www.cddiski.ru/'.$pic03.' width="120" ></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center  width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc04.'"><img border="0"  src=http://www.cddiski.ru/'.$pic04.' width="120" ></a></TD>
		</tr>
		
		<TR >
<TD valign=top align=center> <a href="http://www.cddiski.ru/catalog/item?id='.$disc01.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title01.'</b></a> <br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc02.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title02.' </b></a><br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc03.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title03.' </b></a><br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc04.'"><FONT size=2 face=Verdana color=#000000><b>'.$title04.' </b></a><br></TD>
</TR>
		<tr>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price01.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b>'.$price02.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price03.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price04.' </b></font> руб.</FONT></TD>
		</tr>
		<TR >
			<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" > 
		<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc01.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit>

</form>
</TD>
<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc02.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket"  value="В корзину"  type=submit>
</form></TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc03.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket"  value="В корзину"  type=submit>
</form></TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		
<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc04.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket"  value="В корзину"  type=submit>
</form></TD></TR></TABLE>';





$massage.='<br><br><p>&nbsp;</p>
						'.$text2.'
						
							
</TD></TR></TABLE>

<TABLE width=100% cellspacing=0 cellpadding=0 border=0  class=pad_null><TR height=5 ><TD ></TD></TR></TABLE><TABLE width=100% cellspacing=5 cellpadding=5 border=0 class=pad_null ><TR bgcolor=#dfdfdf >

<TH width=600 valign=bottom nowrap= height=8 bgcolor=#dfdfdf align=left >

	<FONT size=1 face=Verdana color=gray >'.$footer.' <br>

Если вы хотите отписаться от рассылки, нажимете  <a href=http://www.cddiski.ru/cabinet/2516?id='.$id.'&key='.$Key.'>сюда</a><br />
	</FONT>

</TH></TR></TABLE></TD></TR></TABLE>
<br />


';

	
	
	

	
	
	
    		
				$headers  = "Content-type: text/html; charset=windows-1251 \r\n";
				$headers .= "From: pochta@cddiski.ru\r\n";

				mail("salikova@inbox.ru, diski-ru@yandex.ru", "$zag", "
				
			

$message

<br />


",  $headers);
	
	
}
	

	  
	  
	  
	  
	  
	  
		  } while ($row = mysql_fetch_assoc($rs));  
	  
	  }
	  
	 $header="Location: index.php?a=5200";
header($header); 
	  



?>