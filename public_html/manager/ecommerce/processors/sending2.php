
 
	
	$sql = "Select id, email,  fname, lname, sname, internalKey   From $dbase.`".$table_prefix."web_user_attributes` where send=0  ";
	
	if ($sending==1) $sql .="and sending_comp=1"; elseif  ($sending==2) $sql .="and sending_video=1"; else ($sending==3) $sql .="and sending_pr=1";
	
	if (isset($_POST['test']))  $sql .="and id=1939";
	
	$rs = mysql_query($sql);
    $row = mysql_fetch_assoc($rs);
	
	if ($row['id']!=0) {
	
	do {  
	
	 $id = $row['id'];
	$Key=$row['internalKey'];
	 $email = $row['email'];
	 $username =$row['fname'].' '.$row['sname'].' '.$row['lname'];	
	 

	

    

 
 
 
 

 
 if ($sending==1) $sending=2513;
 elseif ($sending==2) $sending=2514;
 elseif ($sending==3) $sending=2515;
	$a=$sending;

	
	

		
		$sql2 = "select *  from $dbase.`".$table_prefix."site_tmplvar_contentvalues` 
	
Where contentid = $a ";
	
	$rs2 = mysql_query($sql2);
    $row2 = mysql_fetch_assoc($rs2);
	

	
	$zag =''; $zag01 ='';  $zag1 =''; $zag2 =''; $zag3 ='';
	$disc1=''; $disc2=''; $disc3=''; $main_text=''; $text2=''; $footer='';
	
	do {
	
	if ($row2['tmplvarid']==80) {
	$zag=$row2['value'];
	}
	if ($row2['tmplvarid']==82) {
	$zag01=$row2['value'];
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
	  
	  
	  
	  	$message='


<TABLE width=800 cellspacing=0 cellpadding=0 border=0 class=pad_null  ><TR ><TD valign=top align=left >
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
	<TD bgcolor=#800000 width=130 align="left" valign="bottom"  >
	
	
	<table border="0" width="100%" height="100%" class=pad_null>
	<tr>
		<td><br><br></td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><a href="http://www.cddiski.ru/"><font color="#FFFFFF" size="2" face="Verdana">www.cddiski.ru</font></a><br></td>
		<td><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/sending_str.jpg"></td>
	</tr>
	</table>
	

</TD>



</TR></TABLE>
	
	<TABLE width=100% cellspacing=0 cellpadding=0 border=0 class=pad_null><TR >
		<TD valign=top> &nbsp;<p>
		<FONT size=2 face=Verdana, sans-serif >Здравствуйте, '.$username.'</FONT></TD></TABLE>
     &nbsp;


   <TABLE width=800 cellspacing=0 cellpadding=0 border=0 class=pad_null ><TR ><TD valign=top align=left ><br><br>
<table class=pad_null height=46 background=http://www.cddiski.ru/assets/templates/cd/i/fon_top.gif width=100%><tr><td valign=center><font color="#620907" size=4>&nbsp;<b>'.$zag01.'</b></font></td></tr></table>

		<br />'.$main_text.'
							
	<br><br>';		
				
	  
	  
	  
	if($disc1>0)  {
	
	$disc1=explode(",", $disc1);
	
	$disc01 = $disc1[0];
	$disc02 = $disc1[1];
	$disc03 = $disc1[2];
	$disc04 = $disc1[3];

	
	$sql10 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc01 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs10 = mysql_query($sql10);
    $row10 = mysql_fetch_assoc($rs10);
    
    
    
    $sql11 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc02 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs11 = mysql_query($sql11);
    $row11 = mysql_fetch_assoc($rs11);
    
    
    
    $sql12 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc03 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs12 = mysql_query($sql12);
    $row12 = mysql_fetch_assoc($rs12);
    
    
    
    $sql13 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc04 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs13 = mysql_query($sql13);
    $row13 = mysql_fetch_assoc($rs13);
    
    $price01 = $row10['retail_price'];
     $price02 = $row11['retail_price'];
      $price03 = $row12['retail_price'];
       $price04 = $row13['retail_price'];
       
       
      $pic01 =  $row10['value'];
       $pic02 =  $row11['value'];
        $pic03 =  $row12['value'];
         $pic04 =  $row13['value'];
         
      $title01 =   $row10['pagetitle']; 
      $title02 =   $row11['pagetitle']; 
      $title03 =   $row12['pagetitle']; 
      $title04 =   $row13['pagetitle']; 
      
  
	
	
	}
	if($disc2>0) 
	{
	
	$disc2=explode(",", $disc2);
	
	$disc21 = $disc2[0];
	$disc22 = $disc2[1];
	$disc23 = $disc2[2];
	$disc24 = $disc2[3];
	

	
	$sql20 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc21 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs20 = mysql_query($sql20);
    $row20 = mysql_fetch_assoc($rs20);
    
    
    
    $sql21 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc22 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs21 = mysql_query($sql21);
    $row21 = mysql_fetch_assoc($rs21);
    
    
    
    $sql22 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc23 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs22 = mysql_query($sql22);
    $row22 = mysql_fetch_assoc($rs22);
    
    
    
    $sql23 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc24 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs23 = mysql_query($sql23);
    $row23 = mysql_fetch_assoc($rs23);
    
    $price21 = $row20['retail_price'];
     $price22 = $row21['retail_price'];
      $price23 = $row22['retail_price'];
       $price24 = $row23['retail_price'];
       
       
      $pic21 =  $row20['value'];
       $pic22 =  $row21['value'];
        $pic23 =  $row22['value'];
         $pic24 =  $row23['value'];
         
      $title21 =   $row20['pagetitle']; 
      $title22 =   $row21['pagetitle']; 
      $title23 =   $row22['pagetitle']; 
      $title24 =   $row23['pagetitle']; 
	
	
	}
	
	
	if($disc3>0) { $disc3=explode(",", $disc3);
	
	$disc31 = $disc3[0];
	$disc32 = $disc3[1];
	$disc33 = $disc3[2];
	$disc34 = $disc3[3];
	

	
	$sql30 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc31 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs30 = mysql_query($sql30);
    $row30 = mysql_fetch_assoc($rs30);
    
    
    
    $sql31 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc32 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs31 = mysql_query($sql31);
    $row31 = mysql_fetch_assoc($rs31);
    
    
    
    $sql32 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc33 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs32 = mysql_query($sql32);
    $row32 = mysql_fetch_assoc($rs32);
    
    
    
    $sql33 = "select pagetitle, retail_price, modx_site_tmplvar_ec_itemvalues.value  from $dbase.`".$table_prefix."site_ec_items`, $dbase.`".$table_prefix."site_tmplvar_ec_itemvalues` 
	
Where    modx_site_ec_items.id = $disc34 and modx_site_tmplvar_ec_itemvalues.itemid = modx_site_ec_items.id and modx_site_tmplvar_ec_itemvalues.tmplvarid=40  ";
	
	$rs33 = mysql_query($sql33);
    $row33 = mysql_fetch_assoc($rs33);
    
    $price31 = $row30['retail_price'];
     $price32 = $row31['retail_price'];
      $price33 = $row32['retail_price'];
       $price34 = $row33['retail_price'];
       
       
      $pic31 =  $row30['value'];
       $pic32 =  $row31['value'];
        $pic33 =  $row32['value'];
         $pic34 =  $row33['value'];
         
      $title31 =   $row30['pagetitle']; 
      $title32 =   $row31['pagetitle']; 
      $title33 =   $row32['pagetitle']; 
      $title34 =   $row33['pagetitle']; 
	
	}
	

	
	
	



$message.='	<TABLE width=100% cellspacing=0 cellpadding=0 border=0 class=pad_null >
	
	
	<TR ><TD valign=top align=left >

	<span align="left"><font size="4" color="#620907"><b>	'.$zag1.' </b>	</font></span>
</TD></TR>

	
	<TR ><TD valign=top align=left >
<span align="left">
	<img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/sending_zg1.jpg" ></span></TD></TR>


</TABLE>
<br><br>		
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
		<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc01.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	


</TD>
<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		

<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc02.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	

</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		

<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc03.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	

</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >	
	
<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc04.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	


</TD></TR></TABLE>';

$message.='


<br><br>
<TABLE width=100% cellspacing=0 cellpadding=0 border=0 class=pad_null >
	
	
	<TR ><TD valign=top align=left >

	<span align="left"><font size="4" color="#620907"><b>	'.$zag2.' </b>	</font></span>
</TD></TR>

	
	<TR ><TD valign=top align=left >
<span align="left">
	<img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/sending_zg1.jpg" ></span></TD></TR>


</TABLE>
<br><br>		
<TABLE width=100% cellspacing=0 cellpadding=0 border=0  class=pad_null>
		<tr>
			<TD valign=top align=center  width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc21.'"><img border="0"  src=http://www.cddiski.ru/'.$pic31.' width="120"></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc22.'" ><img border="0"  src=http://www.cddiski.ru/'.$pic22.' width="120" ></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center width="25%" ><a href="http://www.cddiski.ru/catalog/item?id='.$disc23.'"><img border="0"  src=http://www.cddiski.ru/'.$pic23.' width="120" ></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center  width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc24.'"><img border="0"  src=http://www.cddiski.ru/'.$pic24.' width="120" ></a></TD>
		</tr>
		
		<TR >
<TD valign=top align=center> <a href="http://www.cddiski.ru/catalog/item?id='.$disc21.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title21.'</b></a> <br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc22.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title22.' </b></a><br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc23.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title23.' </b></a><br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc24.'"><FONT size=2 face=Verdana color=#000000><b>'.$title24.' </b></a><br></TD>
</TR>
		<tr>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price21.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b>'.$price22.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price23.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price24.' </b></font> руб.</FONT></TD>
		</tr>
		<TR >
			<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" > 
			
			
<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc21.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	


</TD>
<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		

<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc22.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	

</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		

<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc23.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	


</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		

<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc24.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	



</TD></TR></TABLE>



';
$message.='<br><br>
<TABLE width=100% cellspacing=0 cellpadding=0 border=0 class=pad_null >
	
	
	<TR ><TD valign=top align=left >

	<span align="left"><font size="4" color="#620907"><b>	'.$zag3.' </b>	</font></span>
</TD></TR>

	
	<TR ><TD valign=top align=left >
<span align="left">
	<img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/sending_zg1.jpg" ></span></TD></TR>


</TABLE>
<br><br>		
<TABLE width=100% cellspacing=0 cellpadding=0 border=0  class=pad_null>
		<tr>
			<TD valign=top align=center  width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc31.'"><img border="0"  src=http://www.cddiski.ru/'.$pic31.' width="120"></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc32.'" ><img border="0"  src=http://www.cddiski.ru/'.$pic32.' width="120" ></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center width="25%" ><a href="http://www.cddiski.ru/catalog/item?id='.$disc33.'"><img border="0"  src=http://www.cddiski.ru/'.$pic33.' width="120" ></a></TD>
			
			<TD valign=top align=center width="20"><img border="0" src="http://www.cddiski.ru/assets/templates/cd/i/line.jpg" ></TD>
			
			<TD valign=top align=center  width="25%"><a href="http://www.cddiski.ru/catalog/item?id='.$disc34.'"><img border="0"  src=http://www.cddiski.ru/'.$pic34.' width="120" ></a></TD>
		</tr>
		
		<TR >
<TD valign=top align=center> <a href="http://www.cddiski.ru/catalog/item?id='.$disc031.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title31.'</b></a> <br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc32.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title32.' </b></a><br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc33.'"><FONT size=2 face=Verdana color=#000000 ><b>'.$title33.' </b></a><br></TD>
<TD valign=top align=center >&nbsp;</TD>
<TD valign=top align=center ><a href="http://www.cddiski.ru/catalog/item?id='.$disc34.'"><FONT size=2 face=Verdana color=#000000><b>'.$title34.' </b></a><br></TD>
</TR>
		<tr>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price31.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b>'.$price32.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price33.' </b></font> руб.</FONT></TD>
			<TD valign=top align=center >&nbsp;</TD>
			<TD valign=top align=center ><FONT size=2 face=Verdana color=#575757 >Цена: <font color =#620907 size=3><b> '.$price34.' </b></font> руб.</FONT></TD>
		</tr>
		<TR >
			<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" > 
		
<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc31.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	



</TD>
<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		

<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc32.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	


</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >	

<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc33.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	


</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		&nbsp;</TD>

<TD valign=center align=center background="http://www.cddiski.ru/assets/templates/cd/i/sending_fon.jpg" height="46" >		


<table class=pad_null><tr><td><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/in_cart.gif  hspace=3 vspace=0 ></td>
		<td>	<form action="http://www.cddiski.ru/cabinet/cart" method="POST" style="margin:0px;padding:0px;">
                    <input name="addtocart" value="1" type="hidden">
                    <input name="item[id]" value="'.$disc34.'" type="hidden">  
                    <input name="item[quantity]"   value="1" type="hidden">
<input name=button3 class="tobasket" 
 value="В корзину"  type=submit></form>
</td>
		</tr></table>	


</TD></TR></TABLE>
';


$message.='
<br>
<p>&nbsp;</p>
'.$text2.'
		
							
							
				
						
							
							
							</TD></TR></TABLE>

<TABLE width=100% cellspacing=0 cellpadding=0 border=0  class=pad_null><TR height=5 ><TD ></TD></TR></TABLE>
<TABLE width=100% cellspacing=3 cellpadding=5 border=0 class=pad_null >
<TR bgcolor=#dfdfdf >
<TD width=135 align=center valign=center >
	<a href=http://www.cddiski.ru><img border=0 src=http://www.cddiski.ru/assets/templates/cd/i/logo_footer.jpg  hspace=5 vspace=0 ></a>
</TD>
<TD width=600 valign=bottom nowrap= height=8 bgcolor=#dfdfdf align=left >

	<FONT size=1 face=Verdana color=gray >'.$footer.' <br>
	Если содержание сообщений Вас не устраивает, или сообщения отображаются некорректно, пожалуйста, <a href=http://www.cddiski.ru/help/contactus>сообщите нам об этом.</a>
	<br>

Если вы хотите поменять категорию или отписаться от рассылки, нажимете  <a href=http://www.cddiski.ru/cabinet/2516?id='.$id.'&key='.$Key.'>сюда</a><br />
	</FONT>

</TD></TR></TABLE></TD></TR></TABLE>
<br />


';

	
	
	

	
	if (isset($_POST['test'])) $mail1='diski-ru@yandex.ru'; else $mail1='salikova@inbox.ru';
	
    		
				$headers  = "Content-type: text/html; charset=windows-1251 \r\n";
				$headers .= "From: pochta@cddiski.ru\r\n";

				mail("$mail1", "$zag", "
				
			

$message

<br />


",  $headers);
	
	


	  
	  
	  
		  } while ($row = mysql_fetch_assoc($rs));  
	  
	  }
	  
	
 $header="Location: index.php?a=5200";
header($header); 
	  


?>