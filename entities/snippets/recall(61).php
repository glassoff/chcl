<?php 

// &to - email list
$to = $to ? $to : "";
include_once $modx->config["base_path"] . "manager/includes/controls/class.phpmailer.php";
$resultArr = array(
    'code' => '1',//0 - no error
);
$emails = explode(',', $to);

if($_POST['_iqf_']=='send'){
    $name = $_POST['callback_name'];
    $phone = $_POST['callback_phone'];
    if($name && $phone){
        $name = iconv('UTF-8', 'WINDOWS-1251', $name);
        $body = '';
        $body = <<<EOF
    Здравствуйте.<br/>
    С сайта Children Club chcl.ru поступила новая заявка на перезвон.<br/><br/>
    <b>Имя:</b> <font size="4">$name</font><br/>
    <b>Номер:</b> <font size="4">$phone</font><br/>
    <br/>
    Перезвоните как можно быстрее по указанному телефону.
EOF;
        $mail = new PHPMailer();
        $mail->IsMail();
        $mail->CharSet = $modx->config['modx_charset'];
        $mail->IsHTML(true);
        $mail->From     = $modx->config['emailsender'];
        $mail->FromName = $modx->config['site_name'];
        $mail->Subject  = 'Новая заявка на перезвон';
        $mail->Body     = $body;
        
        if(count($emails) > 0){
            foreach($emails as $email){
                $mail->AddAddress($email);              
            }
            
            if($mail->send()){
                $resultArr['code'] = '0';
            }
            else{
                $resultArr['code'] = '2';
            }
                        
        }
        else{
            $resultArr['code'] = '4';   
        }
    
    }
    else{
        $resultArr['code'] = '3';
    }
}

return json_encode($resultArr);

?>
