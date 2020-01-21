<?php

/**
 * Displays some question on the forum with according id GET param.
 * If id is not transferred users will be redirect to the ask.php.
 * Otherwise, if the user is a guest, the site will offer him to fill out a sign up form.
 */

require_once 'includes/requires.php';
require_once 'controllers/PageLoader.php';

if ( isset( $_SESSION['user'] ) )
{
	$loader = Loader::initial( 'Forum', $_GET );

	$loader->showPage( 'forum.html' );
} else
{
	header( 'Location: signup.php' );
}
