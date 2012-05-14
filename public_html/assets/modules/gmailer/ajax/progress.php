<?php
	//this is required so the page won't be cached
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	include_once('../../../../manager/includes/config.inc.php');
	include_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');
	$modx = new DocumentParser;

	//include default language file (keep the parent directory dots on this file!)
	include_once($path . "../lang/english.inc.php");

	//include other language file if set.
	$form_language = isset($language)?$language:$modx->config['manager_language'];
	if($form_language!="english" && $form_language!='') {
		if(file_exists($path . "../lang/".$form_language.".inc.php"))
			include_once $path . "../lang/".$form_language.".inc.php";
		else
			if( $isDebug ) $debugText .= "<strong>Language file '$form_language.inc.php' not found!</strong><br />"; //always in english!
	}
	
	//is this a test email?
	$test = isset($_GET['test']) ? $_GET['test'] : '';	
	
	if ($test > 0)
	{
		$sent = $test;
		$total = $test;
	}
	else
	{
		//get total sent
		$query = $modx->db->select('COUNT(*) AS remaining', $modx->getFullTableName('temailinglist'),'sent=1 AND unsubscribe=0');
		$result = $modx->db->getRow($query);
		$sent = $result['remaining'];
		
		//get total
		$query = $modx->db->select('COUNT(*) AS total', $modx->getFullTableName('temailinglist'), 'unsubscribe=0');
		$result = $modx->db->getRow($query);
		$total = $result['total'];
	}
	
	if ($total > 0)
		$progress = round(($sent / $total) * 100, 0);
	else
		$progress = 100;
	
	if ($sent < $total)
	{
?>
		<div id="bar_container">
			<?php echo '<div class="right">' . $progress . '%</div>' . $sent . ' of ' . $total . ' mailed.'; ?>
			<div id="bar">
				<div id="innerbar" style="width: <?php echo $progress; ?>%;"></div>
			</div>
		</div>

<?php
	}
	elseif ($total == 0)
	{
		echo '<h2>' . $_lang['emptyList'] . '</h2><p>' . $_lang['emptyListMessage'] . '</p>';
	}
	else
	{
		echo '<h2>' . $_lang['success'] . '</h2><p>' . $_lang['successMessage1'] . $total . $_lang['successMessage2'] . '</p>';
	}
?>