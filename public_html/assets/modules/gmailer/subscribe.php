<?php
	$url = 'assets/modules/temailer/subscribe.php?action=subscribe';
			
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

	//subscribe stuff
	if (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && $_GET['action'] == 'subscribe')
	{
		include_once($path . '../../../manager/includes/config.inc.php');
		include_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');
		$modx = new DocumentParser;

			
		//add user to mailing list
		$sql = 'INSERT INTO ' . $modx->getFullTableName('temailinglist') . ' (name, email) VALUES (\'' . $_POST['name'] . '\', \'' . $_POST['email'] . '\')';
		$modx->db->query($sql);
		echo '<div style="text-align:center;margin:4px 0 4px 0;"><p class="normal" style="margin:-4px 0 0 0;">' . $_lang['subscribeThanks'] . '</p></div>';
	}
	else
	{
?>
		<div id="temailer_subscribe">
			<script type="text/javascript">
				function te_subscribe()
				{
					//signup
					new Ajax('<?php echo $url; ?>', 
							{
								update: $('temailer_form'),
								method:'post',
								postBody: 'name=' + $('temailer_name').value + '&email=' + $('temailer_email').value
							}).request();				
				}
			</script>

			<div id="temailer_form">
				<table style="margin-left:auto;margin-right:auto;">
					<tr>
						<td>
							<p class="normal" style="margin:-4px 0 -4px 0;font-size:85%"><?php echo $_lang['subscribeName']; ?>:</p>
						</td>
					</tr>
					<tr>
						<td>
							<input type="text" id="temailer_name" class="temailer input" />
						</td>
					</tr>
					<tr>
						<td>
							<p class="normal" style="margin:-4px 0 -4px 0;font-size:85%"><?php echo $_lang['subscribeEmail']; ?>:</p>
						</td>
					</tr>
					<tr>
						<td>
							<input type="text" id="temailer_email" class="temailer input" />
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<input type="button" value="Sign Up" class="temailer button" onclick="te_subscribe();" />
						</td>
					</tr>
				</table>
			</div>
		</div>
<?php
	}
?>