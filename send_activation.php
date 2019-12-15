<?php
require_once 'includes/requires.php';
require_once 'includes/smtp.php';

if (isset($_SESSION['activation']))
{
	send_activation();
	header('Location: activation.php');
} else
{
	header('HTTP/1.0 404 Not Found');
	die();
}
