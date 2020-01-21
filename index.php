<?php

/**
 * A user can use the site if they have logged in to their account.
 * Otherwise, Tester.su will offer him to create an account or login.
 */

require_once 'includes/requires.php';
require_once 'controllers/PageLoader.php';

if ( isset( $_SESSION['user'] ) )
{
	$loader = Loader::initial( 'Index', $_GET );

	$loader->showPage( 'index.html' );
} else
{
	load_template( 'index.html' );
}
