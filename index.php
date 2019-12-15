<?php
require_once 'includes/requires.php';

if (isset($_SESSION['user']))
{
	load_template('index.html', array(
		'message_color' => 'green',
		'message' => 'Hi, '.htmlspecialchars_decode($_SESSION['user']->login, ENT_QUOTES).'!'));
} else
{
	load_template('index.html');
}
