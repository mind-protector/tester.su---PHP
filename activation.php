<?php
require_once 'includes/requires.php';
require_once 'includes/functions.php';

if (isset($_SESSION['activation']))
{
	$data = $_POST;
	if (isset($data['g-recaptcha-response']))
	{
		$errors = array();

		$code = trim(htmlspecialchars($data['code'], ENT_QUOTES));

		checkReCaptcha();

		if ($code == '')
		{
			$errors[] = array('Enter activation code!');
		}

		if ($code != $_SESSION['activation'])
		{
			$errors[] = array('Code is incorrect!');
		}

	}
} else
{
	header('HTTP/1.0 404 Not Found');
	die();
}

if (!isset($errors))
{
	load_template('activation.html', array(
		'code' => $_SESSION['activation']));
} else
{
	if (empty($errors))
	{
		$user = R::dispense('users');
		$user->login = $_SESSION['login'];
		$user->email = $_SESSION['email'];
		$user->password = $_SESSION['password'];
		R::store($user);

		$_SESSION['user'] = $user;

		unset($_SESSION['password']);
		unset($_SESSION['activation']);
		unset($_SESSION['login']);
		unset($_SESSION['email']);

		load_template('activation.html', array(
			'message_color' => 'green',
			'message' => 'Successful! You can go to the main page'));
	} else
	{
		load_template('activation.html', array(
			'message_color' => 'red',
			'message' => array_shift($errors),
			'code' => $_SESSION['activation']));
	}
}
