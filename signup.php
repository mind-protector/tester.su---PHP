<?php

/**
 * Tester.su start of the sign up process.
 */

require_once 'includes/requires.php';
require_once 'controllers/handlers.php';

$Handler = new SignupHandler( $_POST );

if ( isset( $_POST['g-recaptcha-response'] ) )
{ 
	$errors = $Handler->isValid();
}

$Handler->showResult( 'signup.html', @$errors );
