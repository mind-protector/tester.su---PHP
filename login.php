<?php
require_once 'includes/requires.php';
require_once 'includes/functions.php';

$data = $_POST;
if (isset($data['g-recaptcha-response']))
{	
	$errors = array();

	$login = trim(htmlspecialchars($data['login'], ENT_QUOTES));
	$password = htmlspecialchars($data['password'], ENT_QUOTES);

	checkReCaptcha();

	if ($login == '')
	{
		$errors[] = array('Enter your login or email!');
	}

	$user = R::findOne('users', 'login = ?', array($login));
	if (!$user): $user = R::findOne('users', 'email = ?', array($login)); endif;

	if ($user)
	{
		if (password_verify($password, $user->password))
		{
			$_SESSION['user'] = $user;
		} else
		{
			$errors[] = array('Password is incorrect!');
		}
	} else
	{
		$errors[] = array('User not found!');
	}
}

if (!isset($errors))
{
	load_template('login.html');
} else
{
	if (empty($errors))
	{
		load_template('login.html', array(
			'message_color' => 'green',
			'message' => 'Successful! You can go to the main page'));
	} else
	{
		load_template('login.html', array(
			'message_color' => 'red',
			'message' => array_shift($errors)));
	}
}
