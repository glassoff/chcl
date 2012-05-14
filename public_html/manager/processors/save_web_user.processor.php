<?php
if (IN_MANAGER_MODE != "true")
	die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODx Content Manager instead of accessing this file directly.");
if (!$modx->hasPermission('save_web_user')) {
	$e->setError(3);
	$e->dumpError();
}
?>
<?php


// Web alert -  sends an alert to web browser
function webAlert($msg) {
	global $id, $modx;
	global $dbase, $table_prefix;
	$mode = $_POST['mode'];
	$url = "index.php?a=$mode" . ($mode == '88' ? "&id=" . $id : "");
	$modx->manager->saveFormValues($mode);
	include_once "header.inc.php";
	$modx->webAlert($msg, $url);
	include_once "footer.inc.php";
}

// Generate password
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

$id = intval($_POST['id']);
$oldusername = $_POST['oldusername'];
$newusername = !empty ($_POST['newusername']) ? $_POST['newusername'] : "New User";
$fname = mysql_escape_string($_POST['fname']);
$sname = mysql_escape_string($_POST['sname']);
$lname = mysql_escape_string($_POST['lname']);


$region = mysql_escape_string($_POST['region']);
$town = mysql_escape_string($_POST['town']);
$street = mysql_escape_string($_POST['street']);
$house = $_POST['house'];
$kvartira = $_POST['kvartira'];
$korpus = $_POST['korpus'];
$postcode1 = $_POST['postcode1'];

$state = intval($_POST['state']);
$bonus = intval($_POST['bonus']);
$genpassword = $_POST['newpassword'];
$passwordgenmethod = $_POST['passwordgenmethod'];
$passwordnotifymethod = $_POST['passwordnotifymethod'];
$specifiedpassword = $_POST['specifiedpassword'];
$email = mysql_escape_string($_POST['email']);
$oldemail = $_POST['oldemail'];
$phone = mysql_escape_string($_POST['phone']);
$comment = mysql_escape_string($_POST['comment']);
$roleid = !empty($_POST['role']) ? $_POST['role'] : 0;
$failedlogincount = !empty($_POST['failedlogincount']) ? $_POST['failedlogincount'] : 0;
$blocked = !empty($_POST['blocked']) ? $_POST['blocked'] : 0;
$blockeduntil = !empty($_POST['blockeduntil']) ? ConvertDate($_POST['blockeduntil']) : 0;
$blockedafter = !empty($_POST['blockedafter']) ? ConvertDate($_POST['blockedafter']) : 0;
$user_groups = $_POST['user_groups'];

// verify password
if ($passwordgenmethod == "spec" && $_POST['specifiedpassword'] != $_POST['confirmpassword']) {
	webAlert("Password typed is mismatched");
	exit;
}

// verify email
if ($email == '' || !ereg("^[-!#$%&'*+./0-9=?A-Z^_`a-z{|}~]+", $email)) {
	webAlert("E-mail address doesn't seem to be valid!");
	exit;
}

switch ($_POST['mode']) {
	case '87' : // new user
		// check if this user name already exist
		$sql = "SELECT id FROM $dbase.`" . $table_prefix . "web_users` WHERE username='$newusername'";
		if (!$rs = mysql_query($sql)) {
			webAlert("An error occurred while attempting to retrieve all users with username $newusername.");
			exit;
		}
		$limit = mysql_num_rows($rs);
		if ($limit > 0) {
			webAlert("User name is already in use!");
			exit;
		}

		// check if the email address already exist
		$sql = "SELECT id FROM $dbase.`" . $table_prefix . "web_user_attributes` WHERE email='$email'";
		if (!$rs = mysql_query($sql)) {
			webAlert("An error occurred while attempting to retrieve all users with email $email.");
			exit;
		}
		$limit = mysql_num_rows($rs);
		if ($limit > 0) {
			$row = mysql_fetch_assoc($rs);
			if ($row['id'] != $id) {
				webAlert("Email is already in use!");
				exit;
			}
		}

		// generate a new password for this user
		if ($specifiedpassword != "" && $passwordgenmethod == "spec") {
			if (strlen($specifiedpassword) < 6) {
				webAlert("Password is too short!");
				exit;
			} else {
				$newpassword = $specifiedpassword;
			}
		}
		elseif ($specifiedpassword == "" && $passwordgenmethod == "spec") {
			webAlert("You didn't specify a password for this user!");
			exit;
		}
		elseif ($passwordgenmethod == 'g') {
			$newpassword = generate_password(8);
		} else {
			webAlert("No password generation method specified!");
			exit;
		}

		// invoke OnBeforeWUsrFormSave event
		$modx->invokeEvent("OnBeforeWUsrFormSave", array (
			"mode" => "new",
			"id" => $id
		));

		// create the user account
		$sql = "INSERT INTO $dbase.`" . $table_prefix . "web_users` (username, password)
						VALUES('" . $newusername . "', md5('" . $newpassword . "'));";
		$rs = mysql_query($sql);
		if (!$rs) {
			webAlert("An error occurred while attempting to save the user.");
			exit;
		}
		// now get the id
		if (!$key = mysql_insert_id()) {
			//get the key by sql
		}


		//##
		$columns = array();
		$rs = $modx->db->query("SHOW COLUMNS FROM ".$table_prefix . 'web_user_attributes');
		while($row = $modx->db->getRow($rs)){
			$columns[] = $row['Field'];
		}
		
		//$ua_values = array($key,$fname,$sname, $lname, $region, $state, $roleid, $email, $phone, $comment, $blocked, $blockeduntil, $blockedafter);
		
		$ua_fields = array(
			'internalKey' => $key,
			'role' => $roleid,
			'reg_date' => time()
			
		);
		foreach($columns as $column){
			if( isset(${$column}) && ${$column} ){
				$ua_fields[$column] = ${$column};
			}
		}
		$rs = $modx->db->insert( $ua_fields, $table_prefix . 'web_user_attributes');
		//
		if (!$rs) {
			webAlert($sql."||An error occurred while attempting to save the user's attributes.".mysql_error());
			exit;
		}
		
		// Save User Settings
		saveUserSettings($key);

		// invoke OnWebSaveUser event
		$modx->invokeEvent("OnWebSaveUser", array (
			"mode" => "new",
			"userid" => $key,
			"username" => $newusername,
			"userpassword" => $newpassword,
			"useremail" => $email,
			"userfullname" => $fullname
		));

		// invoke OnWUsrFormSave event
		$modx->invokeEvent("OnWUsrFormSave", array (
			"mode" => "new",
			"id" => $key
		));

		/*******************************************************************************/
		// put the user in the user_groups he/ she should be in
		// first, check that up_perms are switched on!
		if ($use_udperms == 1) {
			if (count($user_groups) > 0) {
				for ($i = 0; $i < count($user_groups); $i++) {
					$sql = "INSERT INTO $dbase.`" . $table_prefix . "web_groups` (webgroup, webuser) values('" . intval($user_groups[$i]) . "', '" . $key . "')";
					$rs = mysql_query($sql);
					if (!$rs) {
						webAlert("An error occurred while attempting to add the user to a web group.");
						exit;
					}
				}
			}
		}
		// end of user_groups stuff!

		if ($passwordnotifymethod == 'e') {
			sendMailMessage($email, $newusername, $newpassword, $fullname);
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "88&id=$id" : "87";
				$header = "Location: index.php?a=" . $a . "&r=2&stay=" . $_POST['stay'];
				header($header);
			} else {
				$header = "Location: index.php?a=99&r=2";
				header($header);
			}
		} else {
			include_once "header.inc.php";
?>
			<div class="subTitle">
			<span class="right"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/_tx_.gif" width="1" height="5"><br /><?php echo $_lang['web_user_title']; ?></span>
			<table cellpadding="0" cellspacing="0" class="actionButtons">
				<tr>
					<td id="Button1"><a href="index.php?a=99&r=2"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" align="absmiddle"> <?php echo $_lang['close']; ?></a></td>
					</td>
				</tr>
			</table>
			</div>
			<div class="sectionHeader"><img src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/misc/dot.gif' alt="." />&nbsp;<?php echo $_lang['web_user_title']; ?></div>
			<div class="sectionBody">
			<div id="disp">
			<p /><br />
			<?php echo sprintf($_lang["password_msg"], $newusername, $newpassword); ?>
			<p />
			</div>
			</div>
		<?php

			include_once "footer.inc.php";
		}
		break;
	case '88' : // edit user
		// generate a new password for this user
		if ($genpassword == 1) {
			if ($specifiedpassword != "" && $passwordgenmethod == "spec") {
				if (strlen($specifiedpassword) < 6) {
					webAlert("Password is too short!");
					exit;
				} else {
					$newpassword = $specifiedpassword;
				}
			}
			elseif ($specifiedpassword == "" && $passwordgenmethod == "spec") {
				webAlert("You didn't specify a password for this user!");
				exit;
			}
			elseif ($passwordgenmethod == 'g') {
				$newpassword = generate_password(8);
			} else {
				webAlert("No password generation method specified!");
				exit;
			}
			$updatepasswordsql = ", password=MD5('$newpassword') ";
		}
		if ($passwordnotifymethod == 'e') {
			sendMailMessage($email, $newusername, $newpassword, $fullname);
		}

		// check if the username already exist
		$sql = "SELECT id FROM $dbase.`" . $table_prefix . "web_users` WHERE username='$newusername'";
		if (!$rs = mysql_query($sql)) {
			webAlert("An error occurred while attempting to retrieve all users with username $newusername.");
			exit;
		}
		$limit = mysql_num_rows($rs);
		if ($limit > 0) {
			$row = mysql_fetch_assoc($rs);
			if ($row['id'] != $id) {
				webAlert("User name is already in use!");
				exit;
			}
		}

		// check if the email address already exists
		$sql = "SELECT internalKey FROM $dbase.`" . $table_prefix . "web_user_attributes` WHERE email='$email'";
		if (!$rs = mysql_query($sql)) {
			webAlert("An error occurred while attempting to retrieve all users with email $email.");
			exit;
		}
		$limit = mysql_num_rows($rs);
		if ($limit > 0) {
			$row = mysql_fetch_assoc($rs);
			if ($row['internalKey'] != $id) {
				webAlert("Email is already in use!");
				exit;
			}
		}

		// invoke OnBeforeWUsrFormSave event
		$modx->invokeEvent("OnBeforeWUsrFormSave", array (
			"mode" => "upd",
			"id" => $id
		));

		// update user name and password
		$sql = "UPDATE $dbase.`" . $table_prefix . "web_users` SET username='$newusername'" . $updatepasswordsql . " WHERE id=$id";
		if (!$rs = mysql_query($sql)) {
			webAlert("An error occurred while attempting to update the user's data.");
			exit;
		}

		$sql = "UPDATE $dbase.`" . $table_prefix . "web_user_attributes` SET 
					fname='$fname',
					bonus = '$bonus', 
					postcode1='$postcode1',
					sname='$sname', 
					lname='$lname',										
					region='$region',
					street='$street',
					town='$town',
					kvartira='$kvartira',
					korpus='$korpus',
					house='$house', 
					housing='$housing', 
					apartment='$apartment', 
					state=$state,
					role='$roleid', 
					email='$email', 
					phone='$phone',					 
					comment='$comment',
					failedlogincount='$failedlogincount', 
					blocked=$blocked, 
					blockeduntil=$blockeduntil, 
					blockedafter=$blockedafter
					WHERE internalKey=$id";
		
		if (!$rs = mysql_query($sql)) {
			webAlert("An error occurred while attempting to update the user's attributes.$sql");
			exit;
		}

		// Save User Settings
		saveUserSettings($id);

		// invoke OnWebSaveUser event
		$modx->invokeEvent("OnWebSaveUser", array (
			"mode" => "upd",
			"userid" => $id,
			"username" => $newusername,
			"userpassword" => $newpassword,
			"useremail" => $email,
			"userfullname" => $fullname,
			"oldusername" => (($oldusername != $newusername
		) ? $oldusername : ""), "olduseremail" => (($oldemail != $email) ? $oldemail : "")));

		// invoke OnWebChangePassword event
		if ($updatepasswordsql)
			$modx->invokeEvent("OnWebChangePassword", array (
				"userid" => $id,
				"username" => $newusername,
				"userpassword" => $newpassword
			));

		// invoke OnWUsrFormSave event
		$modx->invokeEvent("OnWUsrFormSave", array (
			"mode" => "upd",
			"id" => $id
		));

		/*******************************************************************************/
		// put the user in the user_groups he/ she should be in
		// first, check that up_perms are switched on!
		if ($use_udperms == 1) {
			// as this is an existing user, delete his/ her entries in the groups before saving the new groups
			$sql = "DELETE FROM $dbase.`" . $table_prefix . "web_groups` WHERE webuser=$id;";
			$rs = mysql_query($sql);
			if (!$rs) {
				webAlert("An error occurred while attempting to delete previous user_groups entries.");
				exit;
			}
			if (count($user_groups) > 0) {
				for ($i = 0; $i < count($user_groups); $i++) {
					$sql = "INSERT INTO $dbase.`" . $table_prefix . "web_groups` (webgroup, webuser) VALUES('" . intval($user_groups[$i]) . "', '$id')";
					$rs = mysql_query($sql);
					if (!$rs) {
						webAlert("An error occurred while attempting to add the user to a user_group.<br />$sql;");
						exit;
					}
				}
			}
		}
		// end of user_groups stuff!
		/*******************************************************************************/

		if ($genpassword == 1 && $passwordnotifymethod == 's') {
			include_once "header.inc.php";
?>
			<div class="subTitle">
			<span class="right"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/_tx_.gif" width="1" height="5"><br /><?php echo $_lang['web_user_title']; ?></span>
			<table cellpadding="0" cellspacing="0" class="actionButtons">
				<tr>
					<td id="Button1"><a href="index.php?a=99&r=2"><img src="media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/icons/save.gif" align="absmiddle"> <?php echo $_lang['close']; ?></a></td>
					</td>
				</tr>
			</table>
			</div>
			<div class="sectionHeader"><img src='media/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>images/style/<?php echo $manager_theme ? "$manager_theme/":""; ?>misc/dot.gif' alt="." />&nbsp;<?php echo $_lang['web_user_title']; ?></div>
			<div class="sectionBody">
			<div id="disp">
			<p /><br />
			<?php echo sprintf($_lang["password_msg"], $newusername, $newpassword); ?>
			<p />
			</div>
			</div>
		<?php

			include_once "footer.inc.php";
		} else {
			if ($_POST['stay'] != '') {
				$a = ($_POST['stay'] == '2') ? "88&id=$id" : "87";
				$header = "Location: index.php?a=" . $a . "&r=2&stay=" . $_POST['stay'];
				header($header);
			} else {
				$header = "Location: index.php?a=99&r=2";
				header($header);
			}
		}
		break;
	default :
		webAlert("Unauthorized access");
		exit;
}

// Send an email to the user
function sendMailMessage($email, $uid, $pwd, $ufn) {
	global $websignupemail_message;
	global $emailsubject, $emailsender;
	global $site_name, $site_start, $site_url;
	$message = sprintf($websignupemail_message, $uid, $pwd); // use old method
	// replace placeholders
	$message = str_replace("[+uid+]", $uid, $message);
	$message = str_replace("[+pwd+]", $pwd, $message);
	$message = str_replace("[+ufn+]", $ufn, $message);
	$message = str_replace("[+sname+]", $site_name, $message);
	$message = str_replace("[+saddr+]", $emailsender, $message);
	$message = str_replace("[+semail+]", $emailsender, $message);
	$message = str_replace("[+surl+]", $site_url, $message);
	if (ini_get('safe_mode') == FALSE) {
		if (!mail($email, $emailsubject, $message, "From: " . $emailsender . "\r\n" . "X-Mailer: Content Manager - PHP/" . phpversion(), "-f $emailsender")) {
			webAlert("Error while sending mail to $mailto");
			exit;
		}
	} elseif (!mail($email, $emailsubject, $message, "From: " . $emailsender . "\r\n" . "X-Mailer: Content Manager - PHP/" . phpversion())) {
		webAlert("Error while sending mail to $email");
		exit;
	}
}

// Save User Settings
function saveUserSettings($id) {
	global $dbase, $table_prefix;

	$settings = array (
		"login_home",
		"allowed_ip",
		"allowed_days"
	);

	mysql_query("DELETE FROM $dbase.`" . $table_prefix . "web_user_settings` WHERE webuser='$id'");

	for ($i = 0; $i < count($settings); $i++) {
		$n = $settings[$i];
		$vl = $_POST[$n];
		if (is_array($vl))
			$vl = implode(",", $vl);
		if ($vl != '')
			mysql_query("INSERT INTO $dbase.`" . $table_prefix . "web_user_settings` (webuser,setting_name,setting_value) VALUES($id,'$n','" . mysql_escape_string($vl) . "')");
	}
}

// converts date format dd-mm-yyyy to php date
function ConvertDate($date) {
	if ($date == "")
		return "0";
	list ($d, $m, $Y, $H, $M, $S) = sscanf($date, "%2d-%2d-%4d %2d:%2d:%2d");
	if (!$H && !$M && !$S)
		return strtotime("$m/$d/$Y");
	else
		return strtotime("$m/$d/$Y $H:$M:$S");
}
?>
