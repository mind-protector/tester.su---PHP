<?php
require_once 'includes/requires.php';

if (!empty($_SESSION))
{
	$_SESSION = array();
}

header('Location: index.php');
