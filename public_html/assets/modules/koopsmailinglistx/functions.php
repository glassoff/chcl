<? /*    Copyright Jasper Koops 2006
    This file is part of Koops Mailinglist.

    Koops Mailinglist is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    any later version.

    Koops Mailinglist is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
    Last Modified 29-07-2006  
	
    support@koops-projects.com

********************************************************************************    
    
    Ported to MODx 12-08-2006 by sottwell@sottwell.com
    Updated 17-08-2006

********************************************************************************
*/ ?><?

function subscribe($email, $modx, $prefix, $l_subscribe, $l_unsubscribe, $l_submit, $l_notvalid, $l_added, $l_alreadysubscribed, $l_notactivated, $yourname, $youremail, $msg_activate2, $msg_activate1, $l_registration, $l_activate, $l_mailerror){	

		$key = randomkeys(32);	
	if(!ereg("^.+@.+\\..+$", $email)){
       return  '  <table>  <tr>  <td><b>'.$l_mailinglist.'</b></td>  </tr>
  <tr><td><span class="error">'.$l_notvalid.'</span></td></tr>
    <tr>  <td><form action="" method="post">  <input type="text" name="email" size="'.$length.'" maxlength="'.$maxlength.'" value="'.$email.'">  <input type="hidden" name="op" value="set"><br/>  <input type="radio" name="option" value="subscribe" CHECKED>'.$l_subscribe.'  <input type="radio" name="option" value="unsubscribe">'.$l_unsubscribe.'&nbsp;&nbsp;&nbsp;  <input type="submit" value="'.$l_submit.'" >  </form>  </td>  </tr>  </table>';
	}	
	$sql = "SELECT * 
			FROM ".$prefix."subscribers
			WHERE email = '".$email."'";
	$result = $modx->db->query($sql);
	$num = mysql_num_rows($result);	
	$sql = "SELECT * 
			FROM ".$prefix."activation
			WHERE email = '".$email."'";
	$result = $modx->db->query($sql);
	$num2 = mysql_num_rows($result);	
	if($num == 1){
		$display_block = $l_alreadysubscribed.".";					
	}elseif($num2 == 1){
		$display_block = $l_notactivated;		
	}else{
	$url = substr($modx->config['site_url'],0,-1).$modx->makeUrl($modx->documentIdentifier,'','&k='.$key);
		$sql = "INSERT INTO `".$prefix."activation`
					values('', '".$email."', '".time()."', '".$key."')";	
		$msg_activate = $msg_activate1."<a href=\"".$url."\"><b>".$l_activate."</b></a><br/><br/>".$msg_activate2;	
		$phpversion = phpversion();
		if(($phpversion > 4)&&($phpversion < 5)){
            require('Swift/Swift.php');			
            require('Swift/Swift/Swift_Sendmail_Connection.php');		
		}elseif($phpversion >=5){			
            require('Swift/Swift.php');			
            require('Swift/Swift/Swift_Sendmail_Connection.php');		
        }		
        $connection = new Swift_Sendmail_Connection;		
        $mail = new Swift($connection);
		if ($mail->isConnected()){
				$mail->addPart($msg_activate, 'text/html');
				$mail->send($email, '"'.$yourname.'" <'.$youremail.'>', $l_registration);
				$mail->close();			
				$result = $modx->db->query($sql);				
				$display_block = $l_added;
		}else{
			$display_block = $l_mailerror;
		}	
	}
	return $display_block;
}

function activate($key, $modx, $prefix, $l_confirmed, $l_falseconfirmation){	
	$sql = "SELECT *
			FROM `".$prefix."activation`
			WHERE `key` = '".$key."'";	
	$result = $modx->db->query($sql) ;	
	$email = mysql_fetch_row($result);
	$email = $email[1];	
	$num = mysql_num_rows($result);
	if($num == "1"){	
	   		$sql = "INSERT INTO ".$prefix."subscribers
				VALUES('', '".$email."', NOW())";	
		$result = $modx->db->query($sql);		
		$sql = "DELETE 
				FROM ".$prefix."activation
				WHERE `key` = '".$key."'";				
		$result = $modx->db->query($sql,$conn);				
		$display_block = $l_confirmed;		
	}elseif($num == "0"){		
		$display_block = '<span class="error">'.$l_falseconfirmation.'</span>';		
	}
	return $display_block;
}

function unsubscribe($email, $modx, $prefix, $l_subscribe, $l_unsubscribe, $l_submit, $l_notvalid, $l_notsubscribed,$l_removed, $l_deleted){
	if(!ereg("^.+@.+\\..+$", $email)){
       return  '  <table>  <tr>  <td><b>'.$l_mailinglist.'</b></td>  </tr>
  <tr><td><span class="error">'.$l_notvalid.'</span></td></tr>
    <tr>  <td><form action="" method="post">  <input type="text" name="email" size="'.$length.'" maxlength="'.$maxlength.'" value="'.$email.'">  <input type="hidden" name="op" value="set"><br/>  <input type="radio" name="option" value="subscribe" CHECKED>'.$l_subscribe.'  <input type="radio" name="option" value="unsubscribe">'.$l_unsubscribe.'&nbsp;&nbsp;&nbsp;  <input type="submit" value="'.$l_submit.'" >  </form>  </td>  </tr>  </table>';
	}	
	$sql ="SELECT *
			FROM `".$prefix."subscribers`
			WHERE `email` = '".$email."'"; 
	$result = $modx->db->query($sql);	
	$num = mysql_num_rows($result);	
	$sql = "DELETE 
			FROM `".$prefix."subscribers`
			WHERE `email` = '".$email."'"; 	
	$result = $modx->db->query($sql);	
	if($num == 0){		
		echo $l_notsubscribed;	
	}else{		
		echo $l_removed;
	}	
	return $display_block;
}

function randomkeys($length){
	$pattern = "1234567890abcdefghijklmnopqrstuvwxyz";	
	$key = "";	
	for($i=0;$i<$length;$i++){	
		 $key .= $pattern{rand(0,35)};
	}			
	return $key;
}

function listsubscribers($modId, $path, $modx, $prefix, $l_id, $l_email, $l_date, $l_delete, $l_page, $l_amountsubscribed){
	$page = $_GET['l'];	
	$start = $page * 20 - 20;	
	if(!isset($page)){
		$start = 0;
		$page = 1;		
	}		
	$sql = "SELECT `id`, `email`, UNIX_TIMESTAMP(`date`) AS date 
			FROM ".$prefix."subscribers
			ORDER BY `id` ASC
			LIMIT ".$start.",20";		
	$result = $modx->db->query($sql);		
	$num = mysql_num_rows($result);	
	// deal with no users
	if($num < 1) {
	  $display_block = '<br />'.$l_amountsubscribed.":<strong> ".$num."</strong>";
	  return $display_block;
	}	
	$sql2 ="SELECT `id`, `email`, UNIX_TIMESTAMP(`date`) as date 
			FROM ".$prefix."subscribers";
	$result2 = $modx->db->query($sql2);	 
	$num2 = mysql_num_rows($result2);		
	//calculate amount of pages, always round up
	$pages = ceil($num2 / 20);	
	$display_block = '<table style="margin-top:10px;">
					  	<tr>
							<td width="25" bgcolor="#BCBCBA"><b>'.$l_id.'</b></td>
							<td width="200" bgcolor="#BCBCBA"><b>'.$l_email.'</b></td>
							<td width="100" bgcolor="#BCBCBA"><b>'.$l_date.'</b></td>
							<td bgcolor="#BCBCBA"><b>'.$l_delete.'</b></td>
						</tr>';							
	$i =0;	
	while($i < $num){		
		$row = $modx->db->getRow($result);	
		$display_block .= '<tr>
							<td>'.$row['id'].'</td>
							<td>'.$row['email'].'</td>
							<td>'.date("M.d.Y", $row['date']).'</td>
							<td align="center"><a href="index.php?a=112&id='.$modId.'&p=6&u='.$row['id'].'"><img src="'.$path.'images/delete.png" alt="delete" border="0"></a></td>
						</tr>';
		$i++;							
	}	
	$display_block .= '</table><br/>'; 	
	$p=1;
	$display_block .= $l_page.":";
	//$display_block .= $pages;
	do{			
		if($page == $l){
			$display_block .= $l;
			$l++;
			//$pages;			
			}if(($l > $pages)||($pages == 1)){
				break;			
		}	
		$display_block .= " <a href=\"index.php?a=112&id=".$modId."&p=4&l=".$l." \">".$l."</a> ";
		$l++;		
	}while($pages >= $l);	
	return $display_block;
}

function addsubscriber($modId, $path, $modx, $prefix, $l_email, $l_tolist, $l_notvalid, $l_alreadysubscribed) {
    $display_block = '<div id="addSubscriberBlock">';
    if(isset($_POST['subscriberEmail'])) {
        if(ereg("^.+@.+\\..+$", $_POST['subscriberEmail'])){
             // check for duplicate email
            $sql = "SELECT * 
                FROM ".$prefix."subscribers
                WHERE email = '".$_POST['subscriberEmail']."'";
            $result = $modx->db->query($sql);
            $num = mysql_num_rows($result);	
            if($num < 1) {
                 $intotable = $prefix . 'subscribers';
                 $sql = "INSERT INTO ".$prefix."subscribers VALUES('', '".$_POST['subscriberEmail']."', NOW())";
                $result = $modx->db->query($sql);
                if($result) {
                    $subscriberid = $modx->db->getInsertId();
                    $display_block = "<br />".$_POST['subscriberEmail'].$l_tolist." #".$subscriberid."<br />";
                }
            } else {
                $display_block = '<br /><span style="color:red;font-weight:bold;">'.$l_alreadysubscribed.' - '.$_POST['subscriberEmail'].'</span>';
            }
        } else {
            // invalid email address
            $display_block = '<br /><span style="color:red;font-weight:bold;">'.$l_notvalid.' - '.$_POST['subscriberEmail'].'.</span>';
        }
    }
    $display_block .= '
    <form id="addSubscriberForm" action="index.php?a=112&id='.$modId.'&p=3" method="post">
    <fieldset>
    <br /><br />
    <input type="text" name="subscriberEmail" id="subscriberEmail" class="text" />
    <input type="submit" name="addSubscriber" id="addSubscriber" value="Add Subscriber" />
    </fieldset>
    </form>
    </div>';
    return $display_block;
}
?>