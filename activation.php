<?php

/**
 * Second phase of sign up.
 * User needs to enter his activation code.
 */

require_once 'includes/requires.php';
require_once 'controllers/handlers.php';

$Handler = new ActivationHandler( $_POST );

if ( isset( $_SESSION['activation'] ) )
{
	if ( isset( $_POST['g-recaptcha-response'] ) )
	{
		$errors = $Handler->isValid();
	}
} else
{
	header( 'HTTP/1.0 404 Not Found' );
	die();
}

$Handler->showResult( 'activation.html', @$errors );
