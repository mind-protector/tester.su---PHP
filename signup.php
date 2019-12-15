<?php
require_once 'includes/requires.php';
require_once 'includes/functions.php';

$data = $_POST;
if (isset($data['g-recaptcha-response']))
{ 
	$errors = array();
	
	$login = trim(htmlspecialchars($data['login'], ENT_QUOTES));
	$email = trim(htmlspecialchars($data['email'], ENT_QUOTES));
	$password = htmlspecialchars($data['password'], ENT_QUOTES);
	$password_2 = htmlspecialchars($data['password_2'], ENT_QUOTES);

	$_SESSION['login'] = $login;
	$_SESSION['email'] = $email;
	$_SESSION['password'] = password_hash($password, PASSWORD_DEFAULT);

	checkReCaptcha();

	if ($login == '')
	{
		$errors[] = array('Enter your login!');
	}

	if ($email == '')
	{
		$errors[] = array('Enter your email address!');
	}

	if ($password == '')
	{
		$errors[] = array('Enter your password!');
	}

	if (!isset($data['check_box']))
	{
		$errors[] = array('You have not agreed to the project rules!');	
	}

	if (StrLen($login) > 32 or strLen($login) < 4)
	{
		$errors[] = array('Login lenght must be < 32 and > 4!');	
	}

	if (StrLen($password) > 64 or StrLen($password) < 8)
	{
		$errors[] = array('Password lenght must be < 64 and > 8!');	
	}

	if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}/', $password, $matches) or
		preg_match('/([а-яё])+/ui', $password, $matches))
	{
		$errors[] = array(
			'Password must have at least:',
			'- One lowercase Latin character,',
			'- One uppercase Latin character,',
			'- One numeral,',
			'- One special character like: !@#№%^:&?* etc.',
			'And no other alphabets!');	
	}

	if (!preg_match('/^((([0-9A-Za-z]{1}[-0-9A-z\.]{1,}[0-9A-Za-z]{1})|([0-9А-Яа-я]{1}[-0-9А-я\.]{1,}+[0-9А-Яа-я]{1}))@([-A-Za-z]{1,}\.){1,2}[-A-Za-z]{2,})$/u', $email, $matches))
	{
		$errors[] = array('Email is incorrect!');	
	}

	if ($password != $password_2)
	{
		$errors[] = array('Repeated password is incorrect!');
	}

	if (R::count('users', 'login = ?', array($login)) > 0)
	{
		$errors[] = array('This login is already taken!');
	}

	if (R::count('users', 'email = ?', array($email)) > 0)
	{
		$errors[] = array('This email is already taken!');
	}
}

if (!isset($errors))
{
	load_template('signup.html');
} else
{
	if (empty($errors))
	{
		$_SESSION['activation'] = rand(1000000000000, 9999999999999);
		header('Location: send_activation.php');
	} else
	{
		load_template('signup.html', array(
			'login' => htmlspecialchars_decode($_SESSION['login'], ENT_QUOTES),
			'email' => htmlspecialchars_decode($_SESSION['email'], ENT_QUOTES),
			'message_color' => 'red',
			'message' => array_shift($errors)));
	}
}
