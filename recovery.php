<?php

/**
 * Account Recovery Procedure.
 * Sends on users email his login and new his password.
 */

require_once 'includes/requires.php';
require_once 'controllers/handlers.php';

$Handler = new RecoveryHandler($_POST);

if ( isset( $_POST['g-recaptcha-response'] ) )
{
	$errors = $Handler->isValid();
}

$Handler->showResult( 'recovery.html', @$errors );
