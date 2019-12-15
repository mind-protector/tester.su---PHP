<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'libs/vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'libs/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once 'libs/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once 'includes/requires.php';

$mail = new PHPMailer;
$mail->CharSet = 'UTF-8';

$mail->isSMTP();
$mail->SMTPAuth = true;
$mail->SMTPDebug = 0;

$mail->Host = 'ssl://smtp.mail.ru';
$mail->Port = 465;
$mail->Username = 'tester01su@mail.ru';
$mail->Password = 'ka8Jq1_$as';

$mail->setFrom('tester01su@mail.ru', 'tester.su');

function send_activation()
{
	global $mail;
	$body = '<p><b>Welcome to us!<br>This your activation code: '.$_SESSION['activation'].'</b></p>';

	$mail->addAddress($_SESSION['email'], 'Dear Customer');
	$mail->Subject = 'Sign up Completion';
	$mail->msgHTML($body);

	$mail->send();
}


function send_recovery($login, $password)
{
	global $mail;
	$body = "<p><b>We received information that you lost your account<br>This your login: ".$login.
		";<br>And your new password: ".$password.".<br>If you have not left an application for
		account recovery just ignore this message.<br>But if you are the owner of the account, the attacker may have left this request.
		Then we recommend that you change the email address in your account settings</b></p>";

	$mail->addAddress($_SESSION['email'], 'Dear Customer');
	$mail->Subject = 'Account recovery';
	$mail->msgHTML($body);

	$mail->send();
}
