<?php 
	$url = $_SERVER['PHP_SELF']; 
	if($_SERVER['QUERY_STRING'] != null)
		$url .= '?' . $_SERVER['QUERY_STRING'];

	include_once('headermin.php');

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
?>

<script src="<?php echo $path; ?>js/mootools-core.js" type="text/javascript"></script>
<script src="<?php echo $path; ?>js/mootools-more.js" type="text/javascript"></script>
<script src="<?php echo $path; ?>../../../manager/media/script/mootools/moodx.js" type="text/javascript"></script>
<script type="text/javascript">
	function postForm(action)
	{
		document.module.action.value=action;
		document.module.submit();
	}
	
	function setSubject()
	{
		$('subject').value = $('newsletter').options[$('newsletter').selectedIndex].text;
	}
</script>


           
           	
	<!--div id="header"><h1><?php echo $_lang['programName'] . ' (' . $_lang['programVersion'] . ')'; ?></h1></div>
	<div id="mailerNav">
		<a href="javascript:;" onclick="postForm('');return false;"><?php echo $_lang['mainLink']; ?></a> &nbsp;&bull;&nbsp;
		<a href="javascript:;" onclick="window.open('<?php echo $path; ?>edit.php','mailingListEditor','width=530,height=600,screenX=10,screenY=10,left=10,top=10')"><?php echo $_lang['editMailingListLink']; ?></a>
	</div><div id="mainArea"-->
<form id="module" name="module" method="post" action="<?php echo $url; ?>">
	<input id="action" name="action" type="hidden" value="" />
