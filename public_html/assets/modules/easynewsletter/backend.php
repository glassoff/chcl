<?php
/*
Easy Newsletter 0.3
Copyright by: Flux - www.simpleshop.dk
Date: 10. september 2007
Notes: This newsletter system is heavily inspired by KoopsmailinglistX so a bow in respect and appreciation to the original author Jasper Koops and sottwell@sottwell.com who ported it to MODx.

This is version 0.1 so there might be some errors I have missed and functionality that you might think is missing. I have not tested the system with say 1000 subscribers. Error logging/handling is very simple - It will just stop if an error has occurred with no resume function.
---------------------------------------------------------------------
This file is part of Easy Newsletter 0.3

Easy Newsletter 0.3 is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

Easy Newsletter 0.3 is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>. 
---------------------------------------------------------------------*/
$sql = "SELECT * FROM `easynewsletter_config` WHERE `id` = 1";
$result = $modx->db->query($sql);
include($path.'languages/'.mysql_result($result,$i,"lang_backend").'.php');
include($modx->config['base_path'].'manager/includes/config.inc.php');
error_reporting(E_ALL ^ E_NOTICE);
if(!isset($_GET['p'])) { $_GET['p'] = ''; }
if(!isset($_GET['action'])) { $_GET['action'] = 1; }

switch($_GET['p']) {

	// List newsletters
	case "1":
		if ($_GET['action'] == 1) {
			if (!isset($_GET['sortorder'])) {
				$sortorder = 'date';
			} else {
				$sortorder = $_GET['sortorder'];
			}
			$sql = "SELECT * FROM `easynewsletter_newsletter` ORDER BY `".$sortorder."` ASC";
			$result = $modx->db->query($sql);
			$num = mysql_num_rows($result);
			if ($num > 0) {
				$list = '<script type="text/javascript">
				<!--
				function delete_newsletter(a,b)
				{
				answer = confirm("'.$lang_newsletter_delete_alert.'\n"+b)
				if (answer !=0)
				{
				location = "index.php?a=112&id='.$modId.'&p=1&action=6&nid="+a
				}
				}
				function send_newsletter(a,b)
				{
				answer = confirm("'.$lang_newsletter_send_alert1.'\n"+b+"\n\n'.$lang_newsletter_send_alert2.'")
				if (answer !=0)
				{
				location = "index.php?a=112&id='.$modId.'&p=1&action=2&nid="+a
				}
				}
				//-->
				</script>';
				$list .= '<table style="font-size: 12px;" width="600">';
				$list .= '<tr><td colspan="6" height="30"><a href="index.php?a=112&id='.$modId.'&p=1&action=3">'.$lang_newsletter_create.'</a></td></tr>';
				$list .= '<tr>';
				$list .= '<td><a href="index.php?a=112&id='.$modId.'&p=1&action=1&sortorder=date"><strong>'.$lang_newsletter_date.'</strong></a></td>';
				$list .= '<td width="50%"><a href="index.php?a=112&id='.$modId.'&p=1&action=1&sortorder=subject"><strong>'.$lang_newsletter_subject.'</strong></a></td>';
				// $list .= '<td><a href="index.php?a=112&id='.$modId.'&p=1&action=1&sortorder=status"><strong>'.$lang_newsletter_status.'</strong></a></td>';
				// $list .= '<td><a href="index.php?a=112&id='.$modId.'&p=1&action=1&sortorder=sent"><strong>'.$lang_newsletter_sent.'</strong></a></td>';
				$list .= '<td><strong>'.$lang_newsletter_action.'</strong></td>';
				$list .= '</tr>';
				$i=0;	
				while($i < $num){		
					$row = $modx->db->getRow($result);	
					$list .='<tr>';
					$list .= '<td>'.mysql_result($result,$i,"date").'</td>';
					$list .= '<td>'.mysql_result($result,$i,"subject").'</td>';
					// $list .= '<td>'.mysql_result($result,$i,"status").'</td>';
					// $list .= '<td>'.mysql_result($result,$i,"sent").'</td>';
					$list .= '<td><a href="index.php?a=112&id='.$modId.'&p=1&action=3&nid='.mysql_result($result,$i,"id").'">'.$lang_newsletter_edit.'</a> | <a href="index.php?a=112&id='.$modId.'&p=1&action=6&nid='.mysql_result($result,$i,"id").'" onclick=" delete_newsletter(\''.mysql_result($result,$i,"id").'\',\''.mysql_result($result,$i,"subject").'\'); return false;">'.$lang_newsletter_delete.'</a> | <a href="index.php?a=112&id='.$modId.'&p=1&action=7&nid='.mysql_result($result,$i,"id").'">'.$lang_newsletter_testmail.'</a> | <a href="index.php?a=112&id='.$modId.'&p=1&action=2&nid='.mysql_result($result,$i,"id").'" onclick=" send_newsletter(\''.mysql_result($result,$i,"id").'\',\''.mysql_result($result,$i,"subject").'\'); return false;">'.$lang_newsletter_send.'</a></td>';
					$list .= '</tr>';
					$i++;
				}
				$list .= '</table>';
				echo $list;
			} else {
				echo $lang_newsletter_noposts.' <a href="index.php?a=112&id='.$modId.'&p=1&action=3">'.$lang_newsletter_create.'</a>';
			}
		} elseif ($_GET['action'] == 2) {
			// Send newsletter
			$nid = $_GET['nid'];
			$sql = "SELECT * FROM `easynewsletter_newsletter` WHERE `id` = $nid";
			$result = $modx->db->query($sql);
			$newsletter_header = mysql_result($result,$i,"header");
			$newsletter_subject = mysql_result($result,$i,"subject");
			$newsletter_newsletter = mysql_result($result,$i,"newsletter");
			$newsletter_footer = mysql_result($result,$i,"footer");
			
			$sql = "SELECT * FROM `easynewsletter_config` WHERE `id` = 1";
			$result = $modx->db->query($sql);
			$mailmethod = mysql_result($result,$i,"mailmethod");
			$smtp = mysql_result($result,$i,"smtp");
			$fromname = stripslashes(mysql_result($result,$i,"sendername"));
			$from = mysql_result($result,$i,"senderemail");
			$auth = mysql_result($result,$i,"auth");
			$authuser = mysql_result($result,$i,"authuser");
			$authpassword = mysql_result($result,$i,"authpassword");
			
			include_once "../manager/includes/controls/class.phpmailer.php";
			$sql = "SELECT * FROM `easynewsletter_subscribers`";
			##
			$sql = "SELECT * FROM modx_web_user_attributes";
			$result = $modx->db->query($sql);
			$num = mysql_num_rows($result);
			$i=0;
			$sentsuccess=0;
			echo $lang_newsletter_sending;
			while($i < $num){
				$mail = new PHPMailer();
				if ($mailmethod == IsMail) {$mail->IsMail();}
				if ($mailmethod == IsSMTP) {
					$mail->IsSMTP();
					$mail->Host = $smtp;
					if ($auth == 'true') {
						$mail->SMTPAuth = true;
						$mail->Username = $authuser;
						$mail->Password = $authpassword;
					} else {
						$mail->SMTPAuth = false;
					}
				}
				if ($mailmethod == IsSendmail) {$mail->IsSendmail();}
				if ($mailmethod == IsQmail) {$mail->IsQmail();}
				$mail->CharSet = $modx->config['modx_charset'];
				$mail->From		= $from;
				$mail->FromName	= $fromname;
				$mail->Subject	= $newsletter_subject;
				$mail->Body		= $newsletter_newsletter;
				$mail->AltBody	= $newsletter_newsletter;
				$mail->AddAddress(mysql_result($result,$i,"email"));
				if(!$mail->send()) {
					echo $lang_newsletter_sending_done4;
					return 'Main mail: ' . $_lang['ef_mail_error'] . $mail->ErrorInfo;
				} else {
					$sentsuccess++;
				}
				$i++;
			}
			echo $lang_newsletter_sending_done1 . $sentsuccess . $lang_newsletter_sending_done2 . $num . $lang_newsletter_sending_done3;
		} elseif ($_GET['action'] == 3) {
			// Newsletter Rich Text Editor
			$action = 4;
			$nid = '';
			if (isset($_GET['nid'])) {
				$nid = $_GET['nid'];
				$sql = "SELECT * FROM `easynewsletter_newsletter` WHERE `id` = $nid";
				$result = $modx->db->query($sql);
				$subject = mysql_result($result,$i,"subject");
				$newsletter = mysql_result($result,$i,"newsletter");
				$action = 5;
			}
			
			echo '<div class="content_">
					<p>'.$lang_newsletter_edit_header.'</p>
					<form action="index.php?a=112&id='.$modId.'&p=1&action='.$action.'" method="post"><b>
					'.$lang_newsletter_edit_subject.'</b><br /><input type="hidden" name="xid" value="'.$nid.'"><input type="text" size="50" maxlength="50" name="subject" value="'.$subject.'"></input><br /><br />';
			
			// Get access to template variable function (to output the RTE)
			include_once($modx->config['base_path'].'manager/includes/tmplvars.inc.php');
		  
			$event_output = $modx->invokeEvent("OnRichTextEditorInit", array('editor'=>$modx->config['which_editor'], 'elements'=>array('tvmailMessage')));
		
			if(is_array($event_output)) {
				$editor_html = implode("",$event_output);
			}
			// Get HTML for the textarea, last parameters are default_value, elements, value
			$rte_html = renderFormElement('richtext', 'mailMessage', '', '', $newsletter);
			
			echo $rte_html;
			
			echo $editor_html;
			echo  '<br />
			<input type="submit" value="'.$lang_newsletter_edit_save.'"></input></div>';
	} elseif ($_GET['action'] == 4) {
		// insert correct path for images
		$testo = ereg_replace('src="assets/images/','src="'.$site_url.'assets/images/',$_POST['tvmailMessage']);
	
		// Insert newsletter into database
		$sql = "INSERT INTO easynewsletter_newsletter VALUES('', now(), '','', '', '".$_POST['subject']."', '".$testo."', '') ";
		$result = $modx->db->query($sql);
		echo $lang_newsletter_edit_create;
	} elseif ($_GET['action'] == 5) {
		// Update existing newsletter
				// insert correct path for images
		$testo = ereg_replace('src="assets/images/','src="'.$site_url.'assets/images/',$_POST['tvmailMessage']);

		$sql = "UPDATE easynewsletter_newsletter SET subject='".$_POST['subject']."', newsletter='".$testo."' WHERE id='".$_POST['xid']."'";
		$result = $modx->db->query($sql);
		echo $lang_newsletter_edit_update;
	} elseif ($_GET['action'] == 6) {
		// Delete newsletter
		$sql = "DELETE FROM easynewsletter_newsletter WHERE id='".$_GET['nid']."'";
		$result = $modx->db->query($sql);
		echo $lang_newsletter_edit_delete;
		} elseif ($_GET['action'] == 7) {
			// Send test newsletter
			$nid = $_GET['nid'];
			$sql = "SELECT * FROM `easynewsletter_newsletter` WHERE `id` = $nid";
			$result = $modx->db->query($sql);
			$newsletter_header = mysql_result($result,$i,"header");
			$newsletter_subject = mysql_result($result,$i,"subject");
			$newsletter_newsletter = mysql_result($result,$i,"newsletter");
			$newsletter_footer = mysql_result($result,$i,"footer");
			
			$sql = "SELECT * FROM `easynewsletter_config` WHERE `id` = 1";
			$result = $modx->db->query($sql);
			$smtp = mysql_result($result,$i,"smtp");
			$fromname = stripslashes(mysql_result($result,$i,"sendername"));
			$from = mysql_result($result,$i,"senderemail");
			$auth = mysql_result($result,$i,"auth");
			$authuser = mysql_result($result,$i,"authuser");
			$authpassword = mysql_result($result,$i,"authpassword");
			
			include_once "../manager/includes/controls/class.phpmailer.php";
			$sql = "SELECT * FROM `easynewsletter_subscribers`";
			$result = $modx->db->query($sql);
			$num = mysql_num_rows($result);
			echo $lang_newsletter_test;
			$mail = new PHPMailer();
			if ($mailmethod == IsMail) {$mail->IsMail();}
			if ($mailmethod == IsSMTP) {
				$mail->IsSMTP();
				$mail->Host = $smtp;
				if ($auth == 'true') {
					$mail->SMTPAuth = true;
					$mail->Username = $authuser;
					$mail->Password = $authpassword;
				} else {
					$mail->SMTPAuth = false;
				}
			}
			if ($mailmethod == IsSMTP) {$mail->Host = $smtp;}
			if ($mailmethod == IsSendmail) {$mail->IsSendmail();}
			if ($mailmethod == IsQmail) {$mail->IsQmail();}
			$mail->CharSet = $modx->config['modx_charset'];
			$mail->From		= $from;
			$mail->FromName	= $fromname;
			$mail->Subject	= $newsletter_subject;
			$mail->Body		= $newsletter_newsletter;
			$mail->AltBody	= $newsletter_newsletter;
			$mail->AddAddress($from);
			if(!$mail->send()) {
				echo $lang_newsletter_sending_done4;
				return 'Main mail: ' . $_lang['ef_mail_error'] . $mail->ErrorInfo;
			}
		}
	break;
	case "2":
		if ($_GET['action'] == 1) {
			// Show Configuration
			$sql = "SELECT *  FROM `easynewsletter_config` WHERE `id` = 1";
			$result = $modx->db->query($sql);
			$mailmethod = mysql_result($result,$i,"mailmethod");
			$auth = mysql_result($result,$i,"auth");
			$list = '<div class="content_">
					<p>'.$lang_config_header.'</p>
					<form action="index.php?a=112&id='.$modId.'&p=2&action=2" method="post"><b>';
			$list .= '<table style="margin-top:10px; font-size: 12px;">';
			
			$list .= '<tr><td><strong>'.$lang_config_sendername.'</strong></td><td>: <input type="text" size="100" maxlength="100" name="sendername" value="'.stripslashes(mysql_result($result,$i,"sendername")).'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_sendername_description.'</td></tr>';
			$list .= '<tr><td><strong>'.$lang_config_senderemail.'</strong></td><td>: <input type="text" size="100" maxlength="100" name="senderemail" value="'.mysql_result($result,$i,"senderemail").'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_senderemail_description.'</td></tr>';
			
			$list .= '<tr><td><strong>'.$lang_config_mail.'</strong></td><td>: <select name="mailmethod">';

			if($mailmethod == 'IsMail'){$dropdown = ' selected="selected"';} else {$dropdown = '';}
			$list .= '<option value="IsMail"'.$dropdown.'>PHP mail</option>';

			if($mailmethod == 'IsSMTP'){$dropdown = ' selected="selected"';} else {$dropdown = '';}
			$list .= '<option value="IsSMTP"'.$dropdown.'>SMTP</option>';

			if($mailmethod == 'IsSendmail'){$dropdown = ' selected="selected"';} else {$dropdown = '';}
			$list .= '<option value="IsSendmail"'.$dropdown.'>Sendmail</option>';

			if($mailmethod == 'IsQmail'){$dropdown = ' selected="selected"';} else {$dropdown = '';}
			$list .= '<option value="IsQmail"'.$dropdown.'>Qmail MTA</option>';
	
			$list .= '</select></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_mail_description.'</td></tr>';

			$list .= '<tr><td><strong>'.$lang_config_auth.'</strong></td><td>: <select name="auth">';

			if($auth == 'true'){$dropdown3 = ' selected="selected"';} else {$dropdown3 = '';}
			$list .= '<option value="true"'.$dropdown3.'>'.$lang_config_true.'</option>';

			if($auth == 'false'){$dropdown3 = ' selected="selected"';} else {$dropdown3 = '';}
			$list .= '<option value="false"'.$dropdown3.'>'.$lang_config_false.'</option>';
			
			$list .= '</select></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_auth_description.'</td></tr>';

			$list .= '<tr><td><strong>'.$lang_config_smtp.'</strong></td><td>: <input type="text" size="100" maxlength="100" name="smtp" value="'.mysql_result($result,$i,"smtp").'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_smtp_description.'</td></tr>';
			$list .= '<tr><td><strong>'.$lang_config_authuser.'</strong></td><td>: <input type="text" size="100" maxlength="100" name="authuser" value="'.mysql_result($result,$i,"authuser").'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_authuser_description.'</td></tr>';
			$list .= '<tr><td><strong>'.$lang_config_authpassword.'</strong></td><td>: <input type="password" size="100" maxlength="100" name="authpassword" value="'.mysql_result($result,$i,"authpassword").'"></input></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_authpassword_description.'</td></tr>';
// -------------------------------------------------		
			$list .= '<tr><td><strong>'.$lang_config_lang_website.'</strong></td><td>: <select name="lang_frontend">';
			if(mysql_result($result,$i,"lang_frontend") == 'english'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="english"'.$dropdown2.'>English</option>';
			if(mysql_result($result,$i,"lang_frontend") == 'danish'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="danish"'.$dropdown2.'>Dansk</option>';
			if(mysql_result($result,$i,"lang_frontend") == 'italian'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="italian"'.$dropdown2.'>Italiano</option>';
			if(mysql_result($result,$i,"lang_frontend") == 'german'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="german"'.$dropdown2.'>Deutsch</option>';
			$list .= '</select></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_lang_website_description.'</td></tr>';

			$list .= '<tr><td><strong>'.$lang_config_lang_manager.'</strong></td><td>: <select name="lang_backend">';			
			if(mysql_result($result,$i,"lang_backend") == 'english'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="english"'.$dropdown2.'>English</option>';
			if(mysql_result($result,$i,"lang_backend") == 'danish'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="danish"'.$dropdown2.'>Dansk</option>';
			if(mysql_result($result,$i,"lang_backend") == 'italian'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="italian"'.$dropdown2.'>Italiano</option>';
			if(mysql_result($result,$i,"lang_backend") == 'german'){$dropdown2 = ' selected="selected"';} else {$dropdown2 = '';}
			$list .= '<option value="german"'.$dropdown2.'>Deutsch</option>';
			$list .= '</select></td></tr>';
			$list .= '<tr><td>&nbsp;</td><td>&nbsp;&nbsp;'.$lang_config_lang_manager_description.'</td></tr>';
// -------------------------------------------------
			$list .= '</table>';
			$list .= '<br /><input type="submit" value="'.$lang_config_save.'"></input>';
			echo $list;
		} elseif ($_GET['action'] == 2) {
			// Update configuration
			$sql = "UPDATE easynewsletter_config SET mailmethod='".$_POST['mailmethod']."', smtp='".$_POST['smtp']."', auth='".$_POST['auth']."', authuser='".$_POST['authuser']."', authpassword='".$_POST['authpassword']."', sendername='".addslashes($_POST['sendername'])."', senderemail='".$_POST['senderemail']."', lang_frontend='".$_POST['lang_frontend']."', lang_backend='".$_POST['lang_backend']."' WHERE id='1'";	
			$result = $modx->db->query($sql);
			echo $lang_config_update;	
		}
	break;	
	
	default:
		if ($_GET['action'] == 1) {
			// List subscribers
			if (!isset($_GET['sortorder'])) {
				$sortorder = 'firstname';
			} else {
				$sortorder = $_GET['sortorder'];
			}
			$sql = "SELECT * FROM `easynewsletter_subscribers` ORDER BY `".$sortorder."` ASC";
			$result = $modx->db->query($sql);
			$num = mysql_num_rows($result);
			if ($num > 0) {
			$list = '<script type="text/javascript">
			<!--
			function delete_subscriber(a,b,c,d)
			{
			answer = confirm("'.$lang_subscriber_delete_alert.'\n"+b+" "+c+" - "+d)
			if (answer !=0)
				{
				location = "index.php?a=112&id='.$modId.'&action=4&nid="+a
				}
				}
				//-->
				</script>';
				$list .= '<table style="font-size: 12px;" width="700">';
				$list .= '<tr>';
				$list .= '<td><a href="index.php?a=112&id='.$modId.'&action=1&sortorder=firstname"><strong>'.$lang_subscriber_firstname.'</strong></a></td><td><a href="index.php?a=112&id='.$modId.'&action=1&sortorder=lastname"><strong>'.$lang_subscriber_lastname.'</strong></a></td><td><a href="index.php?a=112&id='.$modId.'&action=1&sortorder=email"><strong>'.$lang_subscriber_email.'</strong></a></td><td><a href="index.php?a=112&id='.$modId.'&action=1&sortorder=created"><strong>'.$lang_subscriber_created.'</strong></a></td><td><strong>'.$lang_subscriber_action.'</strong></td>';
				$list .= '</tr>';
				$i=0;	
				while($i < $num){		
					$row = $modx->db->getRow($result);	
					$list .=	'<tr>';
					$list .= '<td>'.mysql_result($result,$i,"firstname").'</td><td>'.mysql_result($result,$i,"lastname").'</td><td>'.mysql_result($result,$i,"email").'</td><td>'.mysql_result($result,$i,"created").'</td><td><a href="index.php?a=112&id='.$modId.'&action=2&nid='.mysql_result($result,$i,"id").'">'.$lang_newsletter_edit.'</a> | <a href="index.php?a=112&id='.$modId.'&action=4&nid='.mysql_result($result,$i,"id").'" onclick=" delete_subscriber(\''.mysql_result($result,$i,"id").'\',\''.mysql_result($result,$i,"firstname").'\',\''.mysql_result($result,$i,"lastname").'\',\''.mysql_result($result,$i,"email").'\'); return false;">'.$lang_newsletter_delete.'</a></td>';
					$list .= '</tr>';
					$i++;
				}
				$list .= '</table>';
				echo $list;
			} else {
				echo $lang_subscriber_noposts;
			}
		} elseif ($_GET['action'] == 2) {
			// Update existing subscriber form
			$sql = "SELECT * FROM `easynewsletter_subscribers` WHERE id = '".$_GET['nid']."'";
			$result = $modx->db->query($sql);
			echo '<div class="content_">
					<p><br />'.$lang_subscriber_edit_header.'</p>
					<form action="index.php?a=112&id='.$modId.'&action=3&nid='.$_GET['nid'].'" method="post">
					<b>'.$lang_subscriber_firstname.'</b><br /><input type="text" size="50" maxlength="50" name="firstname" value="'.mysql_result($result,$i,"firstname").'"></input><br />
					<b>'.$lang_subscriber_lastname.'</b><br /><input type="text" size="50" maxlength="50" name="lastname" value="'.mysql_result($result,$i,"lastname").'"></input><br />
					<b>'.$lang_subscriber_email.'</b><br /><input type="text" size="50" maxlength="50" name="email" value="'.mysql_result($result,$i,"email").'"></input><br /><br />
					<input type="submit" value="'.$lang_subscriber_edit_save.'"></input></div>';
		} elseif ($_GET['action'] == 3) {
	
			// Update existing subscriber
			$sql = "UPDATE easynewsletter_subscribers SET firstname='".$_POST['firstname']."', lastname='".$_POST['lastname']."', email='".$_POST['email']."' WHERE id='".$_GET['nid']."'";
			$result = $modx->db->query($sql);
			echo $lang_subscriber_edit_update;
		} elseif ($_GET['action'] == 4) {
			// Delete subscriber
			$sql = "DELETE FROM easynewsletter_subscribers WHERE id='".$_GET['nid']."'";
			$result = $modx->db->query($sql);
			echo $lang_subscriber_edit_delete;
		}
}
?>
