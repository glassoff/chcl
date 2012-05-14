<?php
	$testEmail = ''; 
	$userid = $modx->getLoginUserID();
	$result = $modx->db->query("SELECT email FROM modx_user_attributes WHERE internalKey='$userid'");
	$row = $modx->db->getRow($result);
	$testEmail = $row['email'];

	$siteUrl = $modx->config['site_url'];
?>

<script>
siteUrl = "<?php echo $siteUrl;?>";
window.addEvent('domready', function(){
	$('newsletter').addEvent('change', function(){
		$('viewLink').set('href', siteUrl + "?id=" + this.get('value'));
	});
});
</script>

<p>
Письма находятся в каталоге с id <?php echo $folder?>.
</p>
<table cellpadding="5">
	<tr>
		<td align="right">Выберете письмо</td>
		<td>
		<?php 
			//show available newsletters
			$newsletters = $modx->getDocumentChildren($folder);
			echo '
					<select id="newsletter" name="newsletter" onchange="setSubject();">
						<option value=""></option>';
						foreach ($newsletters as &$nw)
						{
							echo '<option value="' . $nw['id'] . '">' . $nw['pagetitle'] . '</option>';
						}
			echo '</select>';		
		?> <a id="viewLink" target="_blank" href="<?php echo $siteUrl; ?>">просмотр</a>
		</td>
	</tr>
	<tr>
		<td align="right">Тема</td>
		<td><input size="50" type="text" id="subject" name="subject" /></td>
	</tr>
	<tr>
		<td align="right">Тестовый адрес <br/> &nbsp;</td>
		<td>
			<input type="text" id="testemail" name="testemail" size="35" value="<?php echo $testEmail;?>"/><br/>
			<small>Если задан тестовый адрес, то письмо будет отправлено только на него для проверки</small>
		</td>
	</tr>	
	<tr>
		<td></td>
		<td>Выбрано <span id="list-count">0</span> адресов</td>
	</tr>
</table>
<?php
/*
	//show available newsletters
	$newsletters = $modx->getDocumentChildren($folder);
	echo '	<h4 class="noTopMargin">' . $_lang['newsletter'] . ':</h4>
			<select id="newsletter" name="newsletter" onchange="setSubject();">
				<option value=""></option>';
				foreach ($newsletters as &$nw)
				{
					echo '<option value="' . $nw['id'] . '">' . $nw['pagetitle'] . '</option>';
				}
	echo '</select>';*/
?>

<!-- h4><?php echo $_lang['subject']; ?>:</h4><input type="text" id="subject" name="subject" /-->
<!--h4><?php echo $_lang['introduction']; ?>:</h4><input type="text" id="intro" name="intro" value="Dear [*name*]," /-->
<!-- h4><?php echo $_lang['testEmailAddress']; ?>:</h4><input type="text" id="testemail" name="testemail" />
<p><small><?php echo $_lang['testEmailNote']; ?></small></p-->
<input type="button" class="" onclick="postForm('send');return false;" value="Отправить" />
<!--/div-->

<!--table width="100%">
	<?php 
	foreach ($newsletters as &$nw){
		echo '
		<tr>
			<td>' . $nw['id'] . '</td>
			<td>' . $nw['pagetitle'] . '</td>
			<td></td>
		</tr>
		';
		
	}	
	?>
</table-->
