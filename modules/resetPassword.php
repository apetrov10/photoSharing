<?php
function resetPswdScrn($errorMsg = '') {
		
	global $tpl, $msgArray;
	
	if (!empty($errorMsg)) {
		$tpl->set('errorMsg', $msgArray[$errorMsg]);
	} else {
		$tpl->set('errorMsg', '');
	}
	
	$tpl->set('content', $tpl->fetch('_forgotten_pass.html'));
	print $tpl->fetch('_main_template.html');		
}

function checkMail($mail) {
		
	global $DBH, $tpl, $msgArray;
	$query =  "SELECT username, email FROM users WHERE email = '".$mail."'";
		if (!$result = mysql_query($query)) {
			resetPswdScrn('no_such_mail');
		}
		if (mysql_num_rows($result) > 0){
			sendMail($mail);
			getLoginScreen('link_sent'); 
		} else {
			resetPswdScrn('no_such_mail');
			exit; //
		}
		
}


function sendMail($email) {
		
	global $DBH, $tpl, $msgArray;
	
		$msg  = 'Hello World';
		$subject = 'Nova parola';
		$to   = $email;
		$from = 'apetrov10@gmail.com';
		$name = 'admin';
		$token = md5($email.time());
		$body = 'Za da smenite parolata si molya kliknete na linka '.' <a href="http://localhost/diplomna/index.php?cmd=frgPasswordChange&token='.$token.'">http://localhost/diplomna/index.php?cmd=frgPasswordChange&fpid='.$token.'</a> ';
		$query =  "UPDATE users
						SET token = '".$token."' WHERE email='".$email."'";
		if (!$result = mysql_query($query)) {
			exit; // 
		}

		$mail = new PHPMailer();
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = 'smtp.gmail.com';
		$mail->Port = 465;
		$mail->SMTPSecure = 'ssl';
        $mail->Username = 'apetrov10@gmail.com';  
        $mail->Password = '20089177';

        $mail->IsHTML(true);
        $mail->From="apetrov10@gmail.com";
        $mail->FromName="Admin";
        $mail->Sender=$from; // indicates ReturnPath header
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AddAddress($to);
        if(!$mail->Send())
        {
            $error = 'Mail error: '.$mail->ErrorInfo;
            return true;
        }
        else
        {
            return false;
        }
}

function changeFrgtnPassScr($token) {
		
	global $DBH, $tpl, $msgArray;
	$query =  "SELECT username, email FROM users WHERE token = '".$token."'";
		if (!$result = mysql_query($query)) {
			home();
			exit; // 
		}
	while ($row = mysql_fetch_assoc($result)) {		
		$tpl->set('user', $row['username']);
	}
	$tpl->set('content', $tpl->fetch('_change_forgotten_password.html'));
	print $tpl->fetch('_main_template.html');		
	}
	
	function changeFrgtnPassword($newPass, $user){
	global $DBH, $tpl;
	$msg='';
		$query = "SELECT * FROM users WHERE username = '".$user."'";
		if (!$result = mysql_query($query)) {
			print mysql_error();
			exit; 
		}
		$row = mysql_fetch_assoc($result);
		$row = mysql_fetch_assoc($result);
		$password = $newPass;
		$password = strip_tags($password);
		$password = md5($password);
			$query = "UPDATE users SET password='".$password."', token = 0 WHERE username = '".$user."'";
			$msg = 'change_succesfull';
			if (!$result = mysql_query($query)) {
				print mysql_error();
				exit; 
			}
		getLoginScreen();
	}
?>