<?php

/**
 * Email change procedure.
 * Otherwise, if the user is a guest, the site will offer him to fill out a sign up form.
 */

require_once 'includes/requires.php';
require_once 'controllers/handlers.php';

if ( isset( $_SESSION['user'] ) )
{
	$Handler = new ChangeEmailHandler( $_POST );

	if ( isset( $_POST['g-recaptcha-response'] ) )
	{	
		$errors = $Handler->isValid();
	}

	$Handler->showResult( 'change-email.html', @$errors );
} else
{
	header( 'Location: signup.php' );
}