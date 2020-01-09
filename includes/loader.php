<?php

/**
 * Tester.su template loader using twig library.
 *
 * @url https://twig.symfony.com/
 */

require_once 'libs/vendor/autoload.php';

/**
 * Pass arguments to the .html template.
 *
 * @param string $template
 *
 * @param array $args
 *
 * @return void
 */
function load_template( $temp, $args = array() )
{
	/*
	 * Gives user avatar into template.
	 */
	$args['avatar'] = @$_SESSION['user']->avatar;

	$loader = new Twig_Loader_Filesystem( 'templates' );
	$twig = new Twig_Environment( $loader );

	$template = $twig->loadTemplate( $temp );
	echo $template->render( $args );
}
