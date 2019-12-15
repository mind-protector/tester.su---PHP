<?php
require_once 'libs/vendor/autoload.php';

function load_template($temp, $args=array())
{

	$loader = new Twig_Loader_Filesystem('templates');
	$twig = new Twig_Environment($loader);

	$template = $twig->loadTemplate($temp);
	echo $template->render($args);
}
