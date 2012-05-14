<?php

		require_once 'manager/includes/controls/class.phpmailer.php';
			$Notify = new PHPMailer();
			//$Notify->IsSendmail();

				
$email = 'admin@mail.ru';
$fullname = 'Admin';
$notifySubject = 'test message4';
$notification = 'test body';


$notify = 'dmitry.glassoff@gmail.com, dima_rdm@mail.ru, dmitry.ryumin@elecard.ru';
			$emailList = str_replace(', ', ',', $notify);
			$emailArray = explode(',', $emailList);//print_r($emailArray);die();

//$emailArray = array('dima_rdm@mail.ru','dmitry.glassoff@gmail.com');

			$Notify->CharSet="windows-1251";

			foreach ($emailArray as $address)

			{

				$Notify->From = $email;

				$Notify->FromName = $fullname;

				$Notify->Subject = $notifySubject;

				$notification .= $email_signature; 

				$Notify->Body = $notification;

				$Notify->AddAddress($address);
//sleep(1);
				if (!$Notify->Send())

				{

					echo($Notify->ErrorInfo);

					return; 

				}

				$Notify->ClearAddresses();

			}
?>
