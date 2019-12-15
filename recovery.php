<?php
require_once 'includes/requires.php';
require_once 'includes/smtp.php';
require_once 'includes/functions.php';

$data = $_POST;
if (isset($data['g-recaptcha-response']))
{
	$errors = array();

	$email = trim(htmlspecialchars($data['email'], ENT_QUOTES));

	$_SESSION['email'] = $email;

	checkReCaptcha();
	
	if ($email == '')
	{
		$errors[] = array('Enter your email!');
	}

	if (!preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}+[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $email, $matches))
	{
		$errors[] = array('Email is incorrect!');	
	}

	if (R::count('users', 'email = ?', array($email)) == 0)
	{
		$errors[] = array('User not found!');
	}
}

if ( !isset($errors) )
{
	load_template('recovery.html');
} else
{
	if (empty($errors))
	{	
		$random_password = getRandomPassword();

		$account = R::findOne('users', 'email = ?', array($_SESSION['email']));
		$account->password = password_hash($random_password, PASSWORD_DEFAULT);
		$login = $account->login;
		R::store($account);

		send_recovery($login, $random_password);
		unset($_SESSION['email']);


		load_template('recovery.html', array(
			'message_color' => 'green',
			'message' => 'Successful! Check out your email address and can go to the authorization page',
			'account_datas' => array('login' => $login, 'password' => $random_password)));
	} else
	{
		load_template('recovery.html', array(
			'message_color' => 'red',
			'message' => array_shift($errors)));
	}
}
