<?php

/**
 * Login change procedure.
 */

require_once 'includes/requires.php';
require_once 'controllers/handlers.php';

if ( isset( $_SESSION['user'] ) )
{
	$Handler = new ChangeLoginHandler( $_POST );

	if ( isset( $_POST['g-recaptcha-response'] ) )
	{	
		$errors = $Handler->isValid();
	}

	$Handler->showResult( 'change-login.html', @$errors );
} else
{
	header( 'Location: signup.php' );
}
