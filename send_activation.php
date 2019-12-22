<?php

/**
 * Start of the second phase of sign up.
 * Send activation code on the users email.
 */

require_once 'includes/requires.php';

if ( isset( $_SESSION['activation'] ) )
{
	SMTP::sendActivation();
	header( 'Location: activation.php' );
} else
{
	header( 'HTTP/1.0 404 Not Found' );
	die();
}
