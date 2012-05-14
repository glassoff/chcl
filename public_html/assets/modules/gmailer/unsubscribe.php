<?php
	include_once('../../../manager/includes/config.inc.php');
	include_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');
	$modx = new DocumentParser;

	//include default language file
	include_once($path . "lang/english.inc.php");

	//include other language file if set.
	$form_language = isset($language)?$language:$modx->config['manager_language'];
	if($form_language!="english" && $form_language!='') {
		if(file_exists($path ."lang/".$form_language.".inc.php"))
			include_once $path ."lang/".$form_language.".inc.php";
		else
			if( $isDebug ) $debugText .= "<strong>Language file '$form_language.inc.php' not found!</strong><br />"; //always in english!
	}
		
	//get variables from url
	$url_id = isset($_GET['item']) ? $modx->db->escape($_GET['item']) : '';
	$url_key = isset($_GET['key']) ? $modx->db->escape($_GET['key']) : '';
	
	if ($url_id != '' && $url_key != '')
	{
		//get mailing list item
		$query = $modx->db->select('id, email', $modx->getFullTableName('temailinglist'), 'id=' . $url_id);
		$result = $modx->db->getRow($query);
		$hash = md5($result['id'] . $result['email']);
		
		if ($hash == $url_key)
		{
			//change the email address to unsubscribe
			$modx->db->update('unsubscribe=1', $modx->getFullTableName('temailinglist'), 'id=' . $url_id);
			
			//success message
			echo $_lang['unsubscribeSuccess1'] . $result['email'] . $_lang['unsubscribeSuccess2'];
		}
		else
		{
			echo '<h4>' . $_lang['error'] . ':</h4>' . $_lang['errorInvalidInfo'] . '<br />';
			echo $result['id'] . $result['email'] . '<br />' . $hash;
		}
	}
	else
	{
		echo '<h4>' . $_lang['error'] . ':</h4>' . $_lang['errorMissingInfo'];
	}
?>