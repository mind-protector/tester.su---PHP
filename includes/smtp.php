<?php

/**
 * Uses the PHPmailer library to send emails through SMTP more efficiently.
 * v6.13 is using.
 *
 * @url https://github.com/PHPMailer/PHPMailer/
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'libs/vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'libs/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once 'libs/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once 'includes/requires.php';


abstract class SMTP
{
	/**
	 * The Srevant class.
	 * Tester.su SMTP server settings.
	 *
	 * @url https://ru.wikipedia.org/wiki/%D0%A8%D0%B0%D0%B1%D0%BB%D0%BE%D0%BD_%D0%BF%D1%80%D0%BE%D0%B5%D0%BA%D1%82%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F
	 */

	/**
	 * @var object
	 */
	private static $mail;

	/**
	 * Setting to send mails.
	 *
	 * @return void
	 */
	private static function getSMTP()
	{
		static::$mail = new PHPMailer;
		static::$mail->CharSet = 'UTF-8';

		static::$mail->isSMTP();
		static::$mail->SMTPAuth = true;
		static::$mail->SMTPDebug = 0;

		static::$mail->Host = 'ssl://smtp.mail.ru';
		static::$mail->Port = 465;
		static::$mail->Username = 'tester01su@mail.ru';
		static::$mail->Password = 'ka8Jq1_$as';

		static::$mail->setFrom( 'tester01su@mail.ru', 'tester.su' );
	}

	/**
	 * Sends mail with activation code to complete the sign up process.
	 *
	 * @return void
	 */
	public static function sendActivation()
	{
		self::getSMTP();

		$body = '<p><b>Welcome to us!<br>This your activation code: '.$_SESSION['activation'].'</b></p>';

		static::$mail->addAddress( $_SESSION['email'], 'Dear Customer' );
		static::$mail->Subject = 'Sign up Completion';
		static::$mail->msgHTML( $body );

		static::$mail->send();
	}

	/**
	 * Sends mail with users login and a new password for account recovery procedure.
	 *
	 * @param string $login
	 *
	 * @param string $password
	 *
	 * @return void
	 */
	public static function sendRecovery( $login, $password )
	{
		self::getSMTP();

		$body = "<p><b>We received information that you lost your account<br>This your login: ".$login.
			";<br>And your new password: ".$password.".<br>If you have not left an application for
			account recovery just ignore this message.<br>But if you are the owner of the account, the attacker may have left this request.
			Then we recommend that you change the email address in your account settings</b></p>";

		static::$mail->addAddress( $_SESSION['email'], 'Dear Customer' );
		static::$mail->Subject = 'Account recovery';
		static::$mail->msgHTML( $body );

		static::$mail->send();
	}

	/**
	 * Sends mail with users login and a new password for account recovery procedure.
	 *
	 * @param string $login
	 *
	 * @param string $password
	 *
	 * @return void
	 */
	public static function sendActivationLink( $link )
	{
		self::getSMTP();

		$body = "<p><b>We received information that you want to change your email address.<br>If you have not left an application for
			change email address just ignore this message.<br>Follow this link to confirm you are owner of this account:<br>" . $link . "</b></p>";

		static::$mail->addAddress( $_SESSION['email'], 'Dear Customer' );
		static::$mail->Subject = 'Email change procedure';
		static::$mail->msgHTML( $body );

		static::$mail->send();
	}
}
