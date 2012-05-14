<?
 /*    Copyright Jasper Koops 2006
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
    
    Updated 23-03-2006 to use default MODx RTE by ConseilsWeb
    http://modxcms.com/forums/index.php/topic,6450.msg87778.html#msg87778

********************************************************************************
	
*/ 
#error_reporting(0);
error_reporting(E_ALL ^ E_NOTICE);
if(!isset($_GET['p'])) { $_GET['p'] = ''; }
if(isset($_GET['u'])) { $subid = $_GET['u']; }

switch($_GET['p']) {
	case "1":
	// create newsletter
		echo '<div class="content_">
		        <p><br />Create your newsletter here.</p>
				<form action="index.php?a=112&id='.$modId.'&p=2" method="post"><b>
				'.$l_subject.':</b><br/><input type="text" size="50" maxlength="50" name="subject"></input><br /><br />';
		
		// Get access to template variable function (to output the RTE)
		include_once($modx->config['base_path'].'manager/includes/tmplvars.inc.php');
	  
		$event_output = $modx->invokeEvent("OnRichTextEditorInit", array('editor'=>$modx->config['which_editor'], 'elements'=>array('tvmailMessage')));
	
		if(is_array($event_output)) {
			$editor_html = implode("",$event_output);
		}
		// Get HTML for the textarea, last parameters are default_value, elements, value
		$rte_html = renderFormElement('richtext', 'mailMessage', '', '', '');
		
		echo $rte_html;
		
		echo $editor_html;
		echo  '<br />
			<input type="submit" value="'.$l_sendnewsletter.'"></input></div>';
	break;
	case "2":
	// automatically send newsletter
		require('Swift/Swift.php');
		require('Swift/plugins/Swift_Anti_Flood_Plugin.php');
		require('Swift/Swift/Swift_Sendmail_Connection.php');					
		$sql = "SELECT id, email FROM ".$prefix."subscribers";
		$result = $modx->db->query($sql);		
		$recipients = array();
		while($newArray  = $modx->db->getRow($result)){
			array_push($recipients, $newArray['email']);	
		}				
		$mail = new Swift(new Swift_Sendmail_Connection);		
		// convert [~ID~] urls 
		$text = preg_replace_callback(
			'!\[\~([0-9]+)\~\]!is',
			create_function(
				'$matches',
				'global $modx; return $modx->makeUrl($matches[1],\'\',\'\',\'full\');'
			), $_POST['tvmailMessage'], 1
		);
		
		// Make URLs absolute
		$text = preg_replace("@(<\s*(a|img)\s+[^>]*(href|src)\s*=\s*[\"'])(?!http)([^\"'>]+)([\"'>])@","$1".$modx->config['site_url']."$4$5",$text);		
		if(!$mail->hasFailed()){
			$mail->loadPlugin(new Swift_Anti_Flood_Plugin(100, 30));
			//Make the script run until it's finished in the background
			set_time_limit(0); ignore_user_abort();			
			$mail->addPart($text, 'text/html');
			$mail->send($recipients, '"'.$yourname.'" <'.$youremail.'>', $_POST['subject']);
			$mail->close();
			echo $sent.'.';
		}else{
			echo "The mailer failed to connect. Errors: ".print_r($mailer->errors, 1).". Log: ".print_r($mailer->transactions, 1);
		}
		break;
	
	case "3":
	// add subscriber
	echo addsubscriber($modId, $path, $modx, $prefix, $l_email, $l_tolist, $l_notvalid, $l_alreadysubscribed);
	break;

			   
	case "4":		
	// list subscribers
		echo listsubscribers($modId, $path, $modx, $prefix, $l_id, $l_email, $l_date, $l_delete, $l_page, $l_amountsubscribed);
		break;
	
	case "5":
	// list waiting for activation		
		$sql = "SELECT * 
				FROM ".$prefix."activation";
		$result = $modx->db->query($sql);
		$num2 = mysql_num_rows($result);				
		if(gettype($num2) == "NULL"){
			$num2 = 0;
		}		
		echo $l_amnotactivated.": <b>".$num2."</b>";		
		$timestamp = time() - 172800;		
		$sql = "SELECT * 
				FROM ".$prefix."activation
				WHERE `timestamp` < ".$timestamp;				
		$result = $modx->db->query($sql);
		$num = mysql_num_rows($result);		
		echo "<br/>".$l_amnotactivated." ".$l_48hours."<b>: ".$num."</b><br/>";		
		echo "<br/><a href=\"index.php?a=112&id=".$modId."&p=5\">".$l_removenotactivated.".</a>";
		break;
	
	case "5":
	//removebulk
		$timestamp = time() - 172800;	
		$sql = "DELETE FROM ".$prefix."activation
			WHERE `timestamp` < ".$timestamp;			
		$result = $modx->db->query($sql);
		break;
	
	case "6":	
	// delete user
		$sql = "DELETE FROM ".$prefix."subscribers
				WHERE id = ".$subid;			
		$result = $modx->db->query($sql);			
		echo "#".$subid." ".$l_deleted;	
	  break;
	  
	default:	
	// display stats					
		$sql = "SELECT COUNT(*)
				FROM ".$prefix."activation";			
		$result = $modx->db->query($sql);		
		$num = $modx->db->getValue($result);			
		echo "<br/>".$l_amnotactivated.":<b> ".$num[0]."</b>";		
		$timestamp = time() - 172800;		
		$sql = "SELECT COUNT(*) 
				FROM ".$prefix."activation
				WHERE `timestamp` < ".$timestamp;				
		$result = $modx->db->query($sql);
		$num = $modx->db->getValue($result);		
		echo "<br/>".$l_amnotactivated." ".$l_48hours."<b>: ".$num[0]."</b>";	
		$sql = "SELECT COUNT(*)
				FROM ".$prefix."subscribers";			
		$result = $modx->db->query($sql);	
		$row = $modx->db->getRow($result, 'num');
		echo "<br/>".$l_amountsubscribed.":<b> ".$row[0]."</b>";
}		
	
?>

