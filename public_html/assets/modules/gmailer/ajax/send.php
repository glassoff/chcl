<?php
	//this is required so the page won't be cached
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	
	//allow plenty of time for the script to execute
	set_time_limit(60 * 1); //1 minute
	
	define('MODX_API_MODE', true);
	require_once('../../../../manager/includes/protect.inc.php');
	include_once('../../../../manager/includes/config.inc.php');
	include_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');
	$modx = new DocumentParser;
	$modx->db->connect(); 
	$modx->getSettings();
	
	preg_match('#^((:?http|https)://.+?)/#i', $modx->config['site_url'], $match);
	$modx->config['site_url'] = $match[1] . "/";
		 
	global $modx;//print_r($modx->config);die();
	//die($modx->makeUrl(2));
		
	$charset = $modx->config['modx_charset'];//die($charset);
	
	//get post items
	//print_r($_POST);die();
	$id = isset($_POST['newsletter']) ? $_POST['newsletter'] : -1;
	$subject = isset($_POST['subject']) ? $_POST['subject'] : 'Your Newsletter';
	$testemail = isset($_POST['testemail']) ? $_POST['testemail'] : '';	
	$fromemail = isset($_POST['fromemail']) ? $_POST['fromemail'] : 'newsletter@mysite.com';
	$fromname = isset($_POST['fromname']) ? $_POST['fromname'] : 'The Newsletter Mailer';
	$intro = isset($_POST['intro']) ? $_POST['intro'] : '';
		
	$fromname = iconv('UTF-8', $charset, $fromname);
	
	// To send HTML mail, the Content-type header must be set
	/*$headers  = "MIME-Version: 1.0" . "\n";
	$headers .= "Content-type: text/html; charset=\"UTF-8\"" . "\n";

   //set the from name and email address and other common headers
	$headers .= "From: ".$fromname." <".$fromemail .">"."\n";
   // these two to set reply address
    $headers .= "Reply-To: ".$fromname." <".$fromemail.">"."\n";
    $headers .= "Return-Path: ".$fromname." <".$fromemail.">"."\n";
   // These two to help avoid spam-filters    
    $headers .= "Message-ID: <".time()."-".$fromemail.">"."\n";
    $headers .= "X-Mailer: PHP v".phpversion()."\n";*/

	//get the selected newsletter
	$doc = $modx->getDocument($id);

	//get the template if one exists
	if (isset($doc['template']))
	{
		$templates_table = $modx->getFullTableName('site_templates');
		$templates_query = $modx->db->select('content', $templates_table,'id=' . $doc['template'],'id ASC');
		$template = $modx->db->getRow($templates_query);
		
		//replace template variables for proper output
		$body = insert('content', $intro . $doc['content'], $template['content']);
		$body = insert('longtitle', $doc['longtitle'], $body);
		$body = insert('id', $id, $body);					
	}
	else
	{
		$body = $doc['content'];
	}

	//$subject = iconv($charset, 'UTF-8', $subject);
	
	$subject = iconv('UTF-8', $charset, $subject);
		
	//send the test email and quit
	if ($testemail != '')
	{
		//$body = iconv($charset, 'UTF-8', $body);
		//$sent = mail($testemail, $subject, $body, $headers);
		$result = $modx->db->query("SELECT * FROM modx_web_user_attributes WHERE(email='$testemail')");
		$info = $modx->db->getRow($result);
		$name = getName($info);//die($name);
		//intro
		//if ($name != '')
			$body = insert('name', $name, $body); //personalize the intro
		//else
		//	$body = str_replace($intro, '', $body); //empty name, remove intro
					
		$sent = sendEmail($testemail, $subject, $body, $fromemail, $fromname);
	}
	else
	{
		//get unsent email addresses (10 at a time, mail.php will continually run this)
		$query = $modx->db->select('id, email, internalKey', $modx->getFullTableName('temailinglist'), 'sent=0 AND unsubscribe=0', 'id ASC', '10');

		while ($row = $modx->db->getRow($query)) 
		{
			$result = $modx->db->query("SELECT * FROM modx_web_user_attributes WHERE (internalKey='".$row['internalKey']."')");
			$info = $modx->db->getRow($result);
			
			$name = getName($info);
			//intro
			//if ($name != '')
				$newbody = insert('name', $name, $body); //personalize the intro
			//else
			//	$newbody = str_replace($intro, '', $body); //empty name, remove intro
				
			//unsubscribe link/text
			$url = str_replace('ajax/send.php', 'unsubscribe.php', $_SERVER['PHP_SELF']);
			$newbody = insert('unsubscribe_base', $url, $newbody);
			$newbody = insert('item', $row['id'], $newbody);
			$newbody = insert('key', md5($row['id'] . $row['email']), $newbody);
			$newbody = insert('email', $row['email'], $newbody);
			
			//$newbody = iconv($charset, 'UTF-8', $newbody);
			
			//send the mail
			$sent = sendEmail($row['email'], $subject, $newbody, $fromemail, $fromname);
			//$sent = mail($row['email'], $subject, $newbody, $headers);

			//update the sent flag
			$modx->db->update("sent=1", $modx->getFullTableName('temailinglist'), "id=" . $row['id']);
			
			//slow down the mailer so it doesn't bomb
			sleep(1);
		}	
	}
	
	function insert($key, $value, $body)
	{
		return str_replace('[*' . $key . '*]', $value, $body);
	}
	
	function sendEmail($to, $subject, $body, $from, $fromname){
		global $modx;
		//print_r($modx);die();
		include_once $modx->config['base_path'] . "manager/includes/controls/class.phpmailer.php";
		
		$body = setSystemVars($body);
		
		$base_url = $modx->config['site_url'];
		//preg_match('#^((:?http|https)://.+?)/#i', $base_url, $match);
		//$base_url = $match[1];
		//die($base_url);
		$body = abs_url_text ($body, $base_url);

		$mail = new PHPMailer();
		$mail->IsMail();
	
		$mail->CharSet = $modx->config['modx_charset'];
		$mail->From		= $from;
		$mail->FromName	= $fromname;
		$mail->Subject	= $subject;
		$mail->Body		= $body;
		$mail->AltBody	= $body;
		$mail->AddAddress($to);
		if(!$mail->send()) {
			$error = $mail->ErrorInfo;
			return false;
		}
		return true;
	}
	function abs_url ($link, $base_url) {
	   
	    if (!$link) return $base_url;
	   
	    $parse_url = parse_url($link);
	    $base      = parse_url($base_url);
	    $host_url  = $base['scheme'] . "://" . $base['host'];
	   
	    if ($parse_url['scheme']) {
		$abs_url = $link;
	    } elseif ($parse_url['host']) {
		$abs_url = "http://" . $link;
	    } else {                        // ссылка относительная
		if (preg_match("!^/!", $link)) {
		    $abs_url = $host_url . $link;
		} elseif (preg_match("!^(\.\./)+!", $link, $tt0)) {
		    $num = preg_match_all("!\.\./!", $tt0['0'], $tt1);
		    preg_match("!(.*)/(?:.+?/){{$num}}$!", dirname($base['path']) . "/", $tt2);
		    $abs_url = $host_url . $tt2['1'] . "/" . preg_replace("!^(\.\./){{$num}}!", "", $link);
		} elseif (preg_match("!^\./!", $link)) {
		    $abs_url = $host_url . dirname($base['path']) . substr($link, 1);
		} else {
		    $abs_url = $base_url . ((preg_match("!/$!", $base_url))?"":"/") . $link;
		}
	    }
	   
	    return $abs_url;
	   
	}
	function abs_url_text ($text, $base_url) {
	   
	    define(BASE_URL, $base_url); // хм...
	   
	    $pattern = "!(src|href|background)\s*=\s*[\"']*(.*?)(?:[\"']|\s|>)!i";
	 
	    $text = preg_replace_callback(
		        $pattern,
		        create_function(
		            '$matches',
		            'return $matches[1] . "=\"" . abs_url($matches[2], BASE_URL) . "\"";'
		        ),
		        $text
		    );
	   
	    return $text;
	   
	}
	function setSystemVars($content){
		global $modx;
		//print_r($modx->config);die();
		$content = preg_replace_callback('#\[~(\d+?)~\]#is', 'makeUrl', $content);
		return $content;
	}
	function makeUrl($matches){
		global $modx;
		return $modx->makeUrl($matches[1]);
	}
	function getName($info){
		$name = "";
		
		if($info['fname'] && $info['sname'] && $info['lname'])
			$name = $info['fname'] . " " . $info['sname'] . " " . $info['lname'];
		elseif($info['sname'])
			$name = $info['sname']; 
		
		return $name;		
	}	
?>
