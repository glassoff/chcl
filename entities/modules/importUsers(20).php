<?php
set_time_limit(0); 
global $map; 
$map = array(
	'0' => 'email',
	'1' => 'region',
	'2' => 'town',
	'3' => 'address',
	'4' => 'company',
	'5' => 'sname',
	'6' => '',
	'7' => '',
	'8' => 'phone',
	'9' => '',
	'10' => '',
	'11' => '',
	'12' => '',
	'13' => '',
	'14' => '',
	'15' => '',
);

if($_POST['send']){
	$file = $_FILES['usersFile']['tmp_name'];
	$pointer = fopen($file, 'r');
	$i = 0;
	while( $data = fgetcsv($pointer, 1000, ";") ){//print_r($data);
		//if($i > 2 && $i <= 3){
			$data = setMap($data);
			$data['email'] = getEmail($data['email']);
			$data['type'] = getCompanyType($data['company']);
			$data['phone'] = str_replace("\n", ", ", $data['phone']);
			//print_r($data);//die();
			//echo "====";
			addUser($data);		
			sleep(1);	
		//}
		$i++;
	}
	fclose($pointer);
}
function setMap($data){
	global $map;
	$array = array();
	foreach($data as $k=>$v){
		if($map[$k]){
			$array[$map[$k]] = $v;
		}
	}
	return $array;
}
function addUser($data){
	global $modx;
	if(!$data['email'])
		return false;
	$sql = "SELECT * FROM modx_web_user_attributes WHERE (email='".$data['email']."')";
	$result = $modx->db->query($sql);
	if($modx->db->getRecordCount($result) > 0){
		echo "USER " . $data['email'] . " exists <br>";
		return false;
	}
	else{
		echo $data['email'] . "<br>";
		$password = generate_password();
		echo 'pass: ' . $password . "<br>";
		$fields = array(
			'username' => $data['email'],
			'password' => md5($password)
		);
		if( $modx->db->insert( $fields, 'modx_web_users') && $userId = $modx->db->getInsertId() ){
			$result = $modx->db->query("SHOW COLUMNS FROM modx_web_user_attributes");
			$fields = array(
				'internalKey' => $userId,
				'reg_date' => time(),
				'subscribe' => '1',
				'comment' => 'auto added',
				'opt' => '1'
			);
			while($row = $modx->db->getRow($result)){
				$field = $row['Field'];
				if($data[$field]){
					$fields[$field] = mysql_escape_string($data[$field]);
				}
				else{
					continue;
				}
			}
			
			if( $modx->db->insert( $fields, 'modx_web_user_attributes') ){
				$modx->db->insert(array('webgroup' => '3', 'webuser' => $userId), 'modx_web_groups');
				$data['originalPassword'] = $password;
				sendEmail($data);
				echo "OK <br>";
			}	
			else{
				echo "INSERT ERROR <br>";
			}
		}
		else{
			echo "INSERT ERROR <br>";
		}
	}
}
function sendEmail($data){
	global $modx;
	include_once $modx->config['base_path']."manager/includes/controls/class.phpmailer.php";
	
	$messageTpl = $modx->config['websignupemail_message'];
	$email_signature = $modx->config['webemail_signature'];
	$myEmail = $modx->config['emailsender'];
        $emailSubject = $modx->config['emailsubject'];
	$siteName = $modx->config['site_name'];
	$siteURL = $modx->config['site_url'];				
	$message = str_replace('[+uid+]', $data['email'], $messageTpl);
        $message = str_replace('[+pwd+]', $data['originalPassword'], $message);
        $message = str_replace('[+ufn+]', $fullname, $message);
        $message = str_replace('[+sname+]', $siteName, $message);
        $message = str_replace('[+semail+]', $myEmail, $message);
        $message = str_replace('[+surl+]', $siteURL, $message);
	foreach ($_POST as $name => $value)
	{
		$toReplace = '[+post.'.$name.'+]';
		$message = str_replace($toReplace, $value, $message);
	}

	// Bring in php mailer!
	$Register = new PHPMailer();
	$Register->IsHTML(false);	
	$Register->CharSet="windows-1251";
	$Register->From = $myEmail;
	$Register->FromName = $siteName;
	$Register->Subject = $emailSubject;
	$message .= $email_signature; 
	$Register->Body = $message;
	$Register->AddAddress($data['email'], $fullname);
			
	if ($Register->Send()) {
		echo 'email send <br>';
		return true;			 
	} else {
		echo 'email send ERROR<br>';
		return false;	
	}
	
}
function getCompanyType($str){
	if(strstr($str, 'ИП')){
		return 'ИП';
	}
	elseif(strstr($str, 'ООО')){
		return 'ООО';
	}
	elseif(strstr($str, 'ЗАО')){
		return 'ЗАО';
	}
	elseif(strstr($str, 'ОАО')){
		return 'ОАО';
	}			
	return '';
}
function getEmail($value){
	preg_match('/(?:[a-z0-9+_-]+?\.)*?[a-z0-9_+-]+?@(?:[a-z0-9_-]+?\.)*?[a-z0-9_-]+?\.[a-z0-9]{2,5}/i', $value, $match);
	return $match[0];
}
function generate_password($length = 10) {
	$allowable_characters = "abcdefghjkmnpqrstuvxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
	$ps_len = strlen($allowable_characters);
	mt_srand((double) microtime() * 1000000);
	$pass = "";
	for ($i = 0; $i < $length; $i++) {
		$pass .= $allowable_characters[mt_rand(0, $ps_len -1)];
	}
	return $pass;
}

echo '
<html>
	<head></head>
	<body>
		<p>Выберете CSV файл для импорта</p>
		<form name="module" method="post" enctype="multipart/form-data">
			<input type="file" name="usersFile" /> 
			<input type="submit" name="send" value="Загрузить"/>
		</form>
	</body>
</html>
 
';
?>