<?php

/**
 * Account Login Procedure.
 */

require_once 'includes/requires.php';
require_once 'controllers/handlers.php';

$Handler = new LoginHandler( $_POST );

if ( isset( $_POST['g-recaptcha-response'] ) )
{	
	$errors = $Handler->isValid();
}

$Handler->showResult( 'login.html', @$errors );
