<?php

namespace Handlers;
/**
 * Tester.su forms validation and users data verification.
 */
require_once 'includes/requires.php';


abstract class Main
{
	/**
	 * Pattern to extend it in other handlers.
	 * Get input out form.
	 */

	/**
	 * for Google reCaptcha
	 */
	protected const SECRET_KEY = '6LcyjscUAAAAAOsnqWDkLnjAKWY2mY8tkb7z69zc';

	/**
	 * @var array
	 */
	public $errors = array();

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * Describes all possible users input.
	 * Note that: you should flag other pages which using something variable.
	 *
	 * @param array $post is users input
	 *
	 * @return void
	 */
	public function __construct($post)
	{
		$this->data = $post;

		/**
	 	* @var string; using: singup, login
	 	*/
		protected $login = trim(htmlspecialchars(@$post['login'], ENT_QUOTES));

		/**
	 	* @var string; using: signup, login, recovery
	 	*/
		protected $email = trim(htmlspecialchars(@$post['email'], ENT_QUOTES));

		/**
	 	* @var string; using: signup, login
	 	*/
		protected $password = htmlspecialchars(@$post['password'], ENT_QUOTES);

		/**
	 	* @var string; using: signup
	 	*/
		protected $password_2 = htmlspecialchars(@$post['password_2'], ENT_QUOTES);

		/**
	 	* @var string; using: activation
	 	*/
		protected $code = trim(htmlspecialchars(@$post['code'], ENT_QUOTES));
	}

	/**
	 * Confirming the humanity of the user by validating the response from the Google service by AJAX.
	 * Invisible ReCapthca v2.
	 * String $data['g-recaptcha-response'] getting with any form.
	 *
	 * @return array
	 */
	final protected function handlReCaptcha()
	{
		if (!empty($this->data['g-recaptcha-response']))
		{ 	
			$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.SECRET_KEY.
				'&response='.$this->data['g-recaptcha-response'].
				'&remoteip='.$_SERVER['REMOTE_ADDR']);

			$response = json_decode($response, TRUE); 
			if ($response['success'] !== TRUE)
			{ 
				return array('Invalid server response (2)!');
			}
		} else
		{
			return array('Invalid server response (1)!');
		}

		return array();
	}

	/**
	 * Checking data for errors and commit it, if any.
	 *
	 * @return void
	 */
	protected function isValid()
	{
		$this->errors[] = $this->handlReCaptcha();
	}
}


trait validEmail
{
	/**
	 * Standard email validation.
	 * Its shouldn't be empty and must be like email address.
	 */

	/**
	 * Use regexp to email validation.
	 *
	 * @url https://habr.com/ru/post/175375/
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	protected function isEmail($email)
	{
		if ($email == '')
		{
			return array('Enter your email!');
		}

		if (!preg_match('/.+@.+\..+/i', $email, $matches))
		{
			return array('Email is incorrect!');	
		}
	}
}


final class RecoveryHandler extends Main
{
	/**
	 * Recovery.php data handler.
	 * Using post arguments: $email.
	 */

	use validEmail;

	/**
	 * Checking email for valid and commit errors, if any.
	 *
	 * @return void
	 */
	final public function isValid()
	{
		$_SESSION['email'] = $email;

		parent::isValid();

		$this->errors[] = isEmail($this->email);

		if (R::count('users', 'email = ?', array($this->email)) == 0)
		{
			$this->errors[] = array('User not found!');
		}
	}
}