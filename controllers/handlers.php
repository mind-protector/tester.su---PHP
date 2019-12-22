<?php

/**
 * Tester.su forms validation and users data verification.
 * Uses the RedBeanPHP library for a quality API with a database.
 *
 * @url https://www.redbeanphp.com/
 */

abstract class Main
{
	/**
	 * Pattern to extend it in other handlers.
	 * Get input out form.
	 */

	/**
	 * for Google reCaptcha
	 */
	protected const SECRETKEY = '6LcyjscUAAAAAOsnqWDkLnjAKWY2mY8tkb7z69zc';

	/**
	 * @var array
	 */
	protected $errors = array();

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @var string
	 */
	protected $login;

	/**
	 * @var string
	 */
	protected $email;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $password_2;

	/**
	 * @var string
	 */
	protected $code;

	/**
	 * @var string
	 */
	protected $check_box;

	/**
	 * Describes all possible users input.
	 * Note that: you should flag other pages which using something variable.
	 *
	 * @param array $post is users input
	 *
	 * @return void
	 */
	public function __construct( $post )
	{
		$this->data = $post;

		/**
	 	* @using singup, login
	 	*/
		$this->login = trim( htmlspecialchars( @$post['login'], ENT_QUOTES ) );

		/**
	 	* @using signup, recovery
	 	*/
		$this->email = trim( htmlspecialchars( @$post['email'], ENT_QUOTES) );

		/**
	 	* @using signup, login
	 	*/
		$this->password = htmlspecialchars( @$post['password'], ENT_QUOTES );

		/**
	 	* @using signup
	 	*/
		$this->password_2 = htmlspecialchars( @$post['password_2'], ENT_QUOTES );

		/**
	 	* @using activation
	 	*/
		$this->code = trim( htmlspecialchars( @$post['code'], ENT_QUOTES ) );

		/**
	 	* @using signup
	 	*/
	 	$this->check_box = trim( htmlspecialchars( @$post['check_box'], ENT_QUOTES) );
	}

	/**
	 * Confirming the humanity of the user by validating the response from the Google service by AJAX.
	 * Invisible ReCapthca v2.
	 * String $data['g-recaptcha-response'] getting with any form.
	 *
	 * @return void
	 */
	final protected function handlReCaptcha()
	{
		if ( !empty( $this->data['g-recaptcha-response'] ) )
		{ 	
			$response = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret='.self::SECRETKEY.
				'&response='.$this->data['g-recaptcha-response'].
				'&remoteip='.$_SERVER['REMOTE_ADDR'] );

			$response = json_decode( $response, TRUE ); 
			if ( $response['success'] !== TRUE )
			{ 
				$this->errors[] = array( 'Invalid server response (2)!' );
			}
		} else
		{
			$this->errors[] = array( 'Invalid server response (1)!' );
		}
	}

	/**
	 * Checking data for errors and commit it, if any.
	 *
	 * @return void
	 */
	protected function isValid()
	{
		$this->handlReCaptcha();
	}

	/**
	 * Load page depending on current errors.
	 *
	 * @param string $template
	 *
	 * @param array $errors
	 *
	 * @return void
	 */
	public function showResult( $template, $errors )
	{
		if ( $errors === null )
		{
			$this->showForm( $template );
		} else
		{
			if ( empty( $errors ) )
			{
				$this->showSuccess();
			} else
			{
				$this->showErrors( $template, $errors );
			}
		}
	}

	/**
	 * Executes if the form has not been submitted.
	 *
	 * @param string $template
	 *
	 * @return void
	 */
	protected function showForm( $template )
	{
		load_template( $template );
	}

	/**
	 * Executes if the error array is empty.
	 *
	 * @return void
	 */
	abstract protected function showSuccess();

	/**
	 * Executes if an error is found.
	 *
	 * @param string $template
	 *
	 * @param string $errors
	 *
	 * @return void
	 */
	protected function showErrors( $template, $errors )
	{
		load_template( $template, array(
			'message_color' => 'red',
			'message' => array_shift( $errors) ) );
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
	protected static function isEmail( $email )
	{
		if ( $email == '' )
		{
			return array( 'Enter your email!' );
		}

		if ( !preg_match( '/.+@.+\..+/i', $email, $matches) )
		{
			return array( 'Email is incorrect!' );	
		}
	}
}


trait validLogin
{
	/**
	 * Standard login validation.
	 * Its shouldn't be empty.
	 */

	/**
	 * @param string $login
	 *
	 * @return array
	 */
	protected static function isLogin( $login )
	{
		if ( $login == '' )
		{
			return array( 'Enter your login!' );
		}
	}
}


trait validPassword
{
	/**
	 * Standard password validation.
	 * Its shouldn't be empty.
	 */

	/**
	 * @param string $password
	 *
	 * @return array
	 */
	protected static function isPassword( $password )
	{
		if ( $password == '' )
		{
			return array( 'Enter your password!' );
		}
	}
}


trait validCode
{
	/**
	 * Standard activation code validation.
	 * Its shouldn't be empty and must be equal to sended code.
	 */

	/**
	 * @param string $code
	 *
	 * @return array
	 */
	protected static function isCode( $code )
	{
		if ( $code == '' )
		{
			return array( 'Enter activation code!' );
		}

		if ( $code != $_SESSION['activation'] )
		{
			return array( 'Code is incorrect!' );
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
	 * @param array $post is users input
	 *
	 * @return void
	 */
	public function __construct( $post )
	{
		parent::__construct( $post );

		$_SESSION['email'] = $email;
	}

	/**
	 * Checking email for valid and commit errors, if any.
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( self::isEmail( $this->email ) )
		{
			$this->errors[] = self::isEmail( $this->email );
		}

		if ( R::count( 'users', 'email = ?', array( $this->email) ) == 0 )
		{
			$this->errors[] = array( 'User not found!' );
		}

		return $this->errors;
	}

	/**
	 * Page loading if the error array is empty
	 *
	 * @return void
	 */
	protected function showSuccess()
	{
		$RandomPassword = self::getRandomPassword();

		$account = R::findOne( 'users', 'email = ?', array( $_SESSION['email'] ) );
		if ( !empty( $account ) )
		{
			$account->password = password_hash( $RandomPassword, PASSWORD_DEFAULT );
			$login = $account->login;
			R::store( $account );
		}

		SMTP::sendRecovery( $login, $RandomPassword );
		unset( $_SESSION['email'] );

		load_template( 'recovery.html', array(
			'message_color' => 'green',
			'message' => 'Successful! Check out your email address and can go to the authorization page',
			'account_datas' => array( 'login' => $login, 'password' => $RandomPassword) ) );
	}

	/**
	 * Generate random careful password
	 *
	 * @return string
	 */
	private static function getRandomPassword()
	{
		$CharSet = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP@$!%*?&';
		$max = 10;
		$size = StrLen( $CharSet ) - 1;
		$password = array();

	    while($max--)
	    $password[] = $CharSet[rand( 0, $size )];

		while(!preg_match( '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}/', implode($password), $matches) )
		$password[rand( 0, $max - 1 )] = $CharSet[rand( 0, $size )];

		return implode( $password );
	}
}


final class ActivationHandler extends Main
{
	/**
	 * Activation.php data handler.
	 * Using post arguments: $code.
	 */

	use validCode;

	/**
	 * Checking activation code for valid and commit errors, if any.
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( self::isCode( $this->code ) )
		{
			$this->errors[] = self::isCode( $this->code );
		}

		return $this->errors;
	}

	/**
	 * Page loading if the error array is empty.
	 *
	 * @return void
	 */
	protected function showSuccess()
	{
		$user = R::dispense( 'users' );
		$user->login = $_SESSION['login'];
		$user->email = $_SESSION['email'];
		$user->password = $_SESSION['password'];
		R::store( $user );

		$_SESSION['user'] = $user;

		unset( $_SESSION['password'] );
		unset( $_SESSION['activation'] );
		unset( $_SESSION['login'] );
		unset( $_SESSION['email'] );

		load_template('activation.html', array(
			'message_color' => 'green',
			'message' => 'Successful! You can go to the main page') );
	}

	/**
	 * Also passes the code parameter in the template.
	 *
	 * @param string $template
	 *
	 * @return void
	 */
	protected function showForm( $template )
	{
		load_template( $template, array(
		'code' => $_SESSION['activation'] ) );
	}

	/**
	 * Also passes the code parameter in the template.
	 *
	 * @param string $template
	 *
	 * @param string $errors
	 *
	 * @return void
	 */
	protected function showErrors( $template, $errors )
	{
		load_template( $template, array(
			'message_color' => 'red',
			'message' => array_shift( $errors ),
			'code' => $_SESSION['activation'] ) );
	}
}


final class LoginHandler extends Main
{
	/**
	 * Login.php data handler.
	 * Using post arguments: $login, $password.
	 */

	use validLogin, validPassword;

	/**
	 * Checking login for valid and commit errors, if any.
	 * Also commit an error, if user is not found.
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( self::isLogin( $this->login ) )
		{
			$this->errors[] = self::isLogin( $this->login );
		}

		$user = R::findOne( 'users', 'login = ?', array( $this->login ) );
		if ( !$user )
		{
			$user = R::findOne( 'users', 'email = ?', array( $this->login ) );
		}

		if ( self::isPassword( $this->password ) )
		{
			$this->errors[] = self::isPassword( $this->password );
		}

		if ( $user )
		{
			if ( password_verify( $this->password, $user->password ) )
			{
				$_SESSION['user'] = $user;
			} else
			{
				$this->errors[] = array( 'Password is incorrect!' );
			}
		} else
		{
			$this->errors[] = array( 'User not found!' );
		}

		return $this->errors;
	}

	/**
	 * Page loading if the error array is empty.
	 *
	 * @return void
	 */
	protected function showSuccess()
	{
		load_template( 'login.html', array(
			'message_color' => 'green',
			'message' => 'Successful! You can go to the main page' ) );
	}
}


final class SignupHandler extends Main
{
	/**
	 * Signup.php data handler.
	 * Using post arguments: $login, $password, $email, $passwrod_2, $check_box.
	 */

	use validLogin, validEmail, validPassword;

	/**
	 * @param array $post is users input
	 *
	 * @return void
	 */
	public function __construct( $post )
	{
		parent::__construct( $post );

		$_SESSION['login'] = $this->login;
		$_SESSION['email'] = $this->email;
		$_SESSION['password'] = password_hash( $this->password, PASSWORD_DEFAULT );
	}

	/**
	 * Checking data for valid and commit errors, if any.
	 * Login must be < 32 and > 4.
	 * Password length must be < 64 and > 7.
	 * Password must have at least:
	 * - One lowercase Latin character,
	 * - One uppercase Latin character,
	 * - One numeral,
	 * - One special character like: !@#№%^:&?* etc.',
	 * - And no other alphabets!
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( self::isLogin( $this->login ) )
		{
			$this->errors[] = self::isLogin( $this->login );
		}

		if ( self::isEmail( $this->email ) )
		{
			$this->errors[] = self::isEmail( $this->email );
		}

		if ( self::isPassword( $this->password ) )
		{
			$this->errors[] = self::isPassword( $this->password );
		}

		if ( strlen( $this->login ) > 32 or strlen( $this->login ) < 4 )
		{
			$this->errors[] = array('Login lenght must be < 32 and > 4!');	
		}

		if ( strlen( $this->password ) > 64 or strlen( $this->password ) < 8 )
		{
			$this->errors[] = array( 'Password lenght must be < 64 and > 8!' );	
		}

		if ( !preg_match( '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}/', $password, $matches ) or
		preg_match( '/([а-яё])+/ui', $password, $matches ) )
		{
			$this->errors[] = array(
				'Password must have at least:',
				'- One lowercase Latin character,',
				'- One uppercase Latin character,',
				'- One numeral,',
				'- One special character like: !@#№%^:&?* etc.',
				'And no other alphabets!' );	
		}

		if ( $this->password != $this->password_2 )
		{
			$this->errors[] = array( 'Repeated password is incorrect!' );
		}

		if (R::count( 'users', 'login = ?', array( $this->login) ) > 0 )
		{
			$this->errors[] = array( 'This login is already taken!' );
		}

		if ( R::count( 'users', 'email = ?', array( $this->email) ) > 0 )
		{
			$this->errors[] = array( 'This email is already taken!' );
		}

		if ( !isset( $this->check_box ) )
		{
			$this->errors[] = array( 'You have not agreed to the project rules!' );	
		}

		return $this->errors;
	}

	/**
	 * The second sign up phase begins if the error array is empty.
	 *
	 * @return void
	 */
	protected function showSuccess()
	{
		$_SESSION['activation'] = rand( 1000000000000, 9999999999999 );
		header( 'Location: send_activation.php' );
	}

	/**
	 * Also passes login and email parameters to the template.
	 *
	 * @param string $template
	 *
	 * @param string $errors
	 *
	 * @return void
	 */
	protected function showErrors( $template, $errors )
	{
		load_template('signup.html', array(
			'login' => htmlspecialchars_decode( $_SESSION['login'], ENT_QUOTES ),
			'email' => htmlspecialchars_decode( $_SESSION['email'], ENT_QUOTES ),
			'message_color' => 'red',
			'message' => array_shift( $errors ) ) );
	}
}
