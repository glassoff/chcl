<?php
	
	//if($_POST['tolist']){
	$modx->db->query("TRUNCATE TABLE " . $modx->getFullTableName('temailinglist'));
	//create mailing list
	$tolist = (array)$_POST['tolist'];	
	
	$userresult = $modx->db->query("SELECT * FROM modx_web_users");
	while($userrow = $modx->db->getRow($userresult)){
		$customer_id = $userrow['id'];
		
		$sql = "SELECT * FROM modx_web_user_attributes WHERE (internalKey='$customer_id')";
		$result = $modx->db->query($sql);
		$row = $modx->db->getRow($result);
		
		$email = $row['email'];
		$unsubscribe = 1; 
		
		if(in_array($customer_id, $tolist)){
			$unsubscribe = 0;
		}
		else{
			$unsubscribe = 1;
		}
		
		$sql = "INSERT INTO " . $modx->getFullTableName('temailinglist') . "(email, unsubscribe, internalKey) VALUES ('$email', '$unsubscribe', '$customer_id')";
		
		$modx->db->query($sql);			
	}

	
	//}	
	//die();
	//clear the "sent" flags
	$modx->db->update('sent=0', $modx->getFullTableName('temailinglist'), 'unsubscribe=0');

	//get post items
	$id = isset($_POST['newsletter']) ? $modx->db->escape($_POST['newsletter']) : -1;
	$subject = isset($_POST['subject']) ? $modx->db->escape($_POST['subject']) : 'Your Newsletter';
	$testemail = isset($_POST['testemail']) ? $modx->db->escape($_POST['testemail']) : '';
	//$intro = isset($_POST['intro']) ? $modx->db->escape($_POST['intro']) : '';

	if ($testemail != '')
	{
		$num_comma = count(explode(",",$testemail));
		$num_semi = count(explode(";",$testemail));
		$test_count = $num_comma > $num_semi ? $num_comma : $num_semi;		
	}
	else
	{
		$test_count = 0;
	}

?>

<div id="progress"></div>
<div id="sender"></div>
<script language="JavaScript" type="text/javascript">
	
	window.addEvent('domready', function() 
	{		
		//progress bar
		//limit 2 hours
		new Request.HTML({
			update: $('progress'),
			method: 'post',
			url: '<?php echo $path; ?>ajax/progress.php?test=<?php if ($testemail != '') echo $test_count; ?>',
			initialDelay: 100,
			delay: 10000,
			limit: 7200000
		}).startTimer();
		
		//send the mail
		//limit 2 hours
		var sendData = {
			newsletter: '<?php echo $id; ?>',
			subject: '<?php echo $subject; ?>',
			testemail: '<?php echo $testemail; ?>',
			fromemail: '<?php echo $fromemail; ?>',
			fromname: '<?php echo $fromname; ?>',
			intro: '<?php echo $intro; ?>'
		};
		var sendRequest = new Request.HTML({
			update: $('sender'),
			method: 'post',
			url: '<?php echo $path; ?>ajax/send.php',
			initialDelay: 10,
			delay: 30000,
			limit: 7200000,
			onSuccess: function(){
				if(sendData.testemail != ''){sendRequest.stopTimer();}
			}
		}).startTimer(sendData);

	});
</script>
