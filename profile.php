<?php

/**
 * Displays user information with according id GET param.
 * The user has the ability to change their personal data.
 * Otherwise, if the user is a guest, the site will offer him to fill out a sign up form.
 */

require_once 'includes/requires.php';
require_once 'controllers/handlers.php';

if ( isset( $_SESSION['user'] ) )
{
	$id = @$_GET['id'];
	if ( !$id )
	{
		$id = $_SESSION['user']->id;
	}

	/**
	 * For email change procedure.
	 */
	if ( $_GET['code'] == $_SESSION['code'] && isset( $_GET['code'] ) && isset( $_SESSION['code'] ) )
	{
		$account = R::findOne( 'users', 'id = ?', array( $_SESSION['user']->id ) );

		$account->email = $_SESSION['email'];
		R::store( $account );

		unset( $_SESSION['email'] );
		unset( $_SESSION['code'] );

		echo '<script>alert("New email saved successfully!");</script>';
	}

	$Handler = new ChangeAvatarHandler( $_POST, $id );

	if ( isset( $_POST['g-recaptcha-response'] ) )
	{	
		$errors = $Handler->isValid();
	}

	$Handler->showResult( 'profile.html', @$errors );
} else
{
	header( 'Location: signup.php' );
}
