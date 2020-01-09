<?php

/**
 * Password change procedure.
 */

require_once 'includes/requires.php';
require_once 'controllers/handlers.php';

if ( isset( $_SESSION['user'] ) )
{
	$Handler = new ChangePasswordHandler( $_POST );

	if ( isset( $_POST['g-recaptcha-response'] ) )
	{	
		$errors = $Handler->isValid();
	}

	$Handler->showResult( 'change-password.html', @$errors );
} else
{
	header( 'Location: signup.php' );
}
