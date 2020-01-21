<?php

/**
 * Tester.su forms validation and users data verification.
 * Uses the RedBeanPHP library for a quality API with a database.
 *
 * @url https://www.redbeanphp.com/
 */

abstract class Base
{
	/**
	 * The abstract builder.
	 * Pattern to extend it in other handlers.
	 * Get input out form.
	 *
	 * @url https://ru.wikipedia.org/wiki/%D0%A1%D1%82%D1%80%D0%BE%D0%B8%D1%82%D0%B5%D0%BB%D1%8C_(%D1%88%D0%B0%D0%B1%D0%BB%D0%BE%D0%BD_%D0%BF%D1%80%D0%BE%D0%B5%D0%BA%D1%82%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F)
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
	 * @var array
	 */
	protected $image;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $text;

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
        * @using EVER
        */
        $this->csrf_token = @$this->data['csrf_token'];

		/**
		* @using singup, login, change-login
		*/
		$this->login = trim( htmlspecialchars( @$this->data['login'], ENT_QUOTES ) );

		/**
		* @using signup, recovery
		*/
		$this->email = trim( htmlspecialchars( @$this->data['email'], ENT_QUOTES) );

		/**
		* @using signup, login, change-password
		*/
		$this->password = htmlspecialchars( @$this->data['password'], ENT_QUOTES );

		/**
		* @using signup, change-password
		*/
		$this->password_2 = htmlspecialchars( @$this->data['password_2'], ENT_QUOTES );

		/**
		* @using activation
		*/
		$this->code = trim( htmlspecialchars( @$this->data['code'], ENT_QUOTES ) );

		/**
		* @using signup
		*/
		$this->check_box = trim( htmlspecialchars( @$this->data['check_box'], ENT_QUOTES) );

		/**
		* @using profile, ask
		*/
		$this->image = @$_FILES['image'];

		/**
		* @using ask
		*/
		$this->title = htmlspecialchars( @$this->data['title'], ENT_QUOTES );

		/**
		* @using ask
		*/
		$this->text = htmlspecialchars( @$this->data['text'], ENT_QUOTES );
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

        if ( $this->csrf_token != $_SESSION['csrf_token'] )
        {
           $this->errors[] = array( 'Invalid CSRF token!' ); 
        }
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
				$this->showSuccess( $template );
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
	abstract protected function showSuccess( $template );

	/**
	 * Executes if an error is found.
	 *
	 * @param string $template
	 *
	 * @param array $errors
	 *
	 * @return void
	 */
	protected function showErrors( $template, $errors )
	{
		load_template( $template, array(
			'message_color' => 'red',
			'message' => array_shift( $errors ) ) );
	}
}


trait validEmail
{
	/**
	 * The decorator.
	 * Standard email validation.
	 * Its shouldn't be empty and must be like email address.
	 */

	/**
	 * Use regex to email validation.
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

		if ( !preg_match( '/(.+@.+\..+){1,129}/i', $email, $matches) )
		{
			return array( 'Email is incorrect!' );	
		}
	}
}


trait newEmail
{
	/**
	 * The decorator.
	 * Validation of new email address.
	 * Inherited from standard email validation (validEmail).
	 * Its must be unique in the DB.
	 */

	use validEmail;

	/**
	 * Use isEmail function to standard validation.
	 *
	 * @param string $email
	 *
	 * @return array
	 */
	protected static function isNewEmail( $email )
	{
		if ( self::isEmail( $email ) )
		{
			return self::isEmail( $email );
		}

		if ( R::count( 'users', 'email = ?', array( $email ) ) )
		{
			return array( 'This email is already taken!' );
		}
	}
}


trait validLogin
{
	/**
	 * The decorator.
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


trait newLogin
{
	/**
	 * The decorator.
	 * Validation of new login.
	 * Inherited from standard login validation (validLogin).
	 * Its lengths must be not less 4 and not more 32.
	 * Its must be unique in the DB.
	 */

	use validlogin;

	/**
	 * Use isLogin function to standard validation.
	 *
	 * @param string $login
	 *
	 * @return array
	 */
	protected static function isNewLogin( $login )
	{
		$reallogin = htmlspecialchars_decode( $login, ENT_QUOTES );

		if ( self::isLogin( $login ) )
		{
			return self::isLogin( $login );
		}

		if ( strlen( $reallogin ) > 32 or strlen( $reallogin ) < 4 )
		{
			return array('Login lenght must be < 32 and > 4!');	
		}

		if ( R::count( 'users', 'login = ?', array( $login) ) > 0 )
		{
			return array( 'This login is already taken!' );
		}
	}
}


trait validPassword
{
	/**
	 * The decorator.
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


trait newPassword
{
	/**
	 * The decorator.
	 * Validation of new password.
	 * Inherited from standard password validation (validPassword).
	 * Its lengths must be not less 8 and not more 64.
	 * Must have a regex pattern entry.
	 *
	 * @url https://stackoverflow.com/questions/19605150/regex-for-password-must-contain-at-least-eight-characters-at-least-one-number-a
	 */

	use validPassword;

	/**
	 * Use isPassword function to standard validation.
	 *
	 * @param string $password
	 *
	 * @return array
	 */
	protected static function isNewPassword( $password )
	{
		$realPassword = htmlspecialchars_decode( $password, ENT_QUOTES );

		if ( self::isPassword( $password ) )
		{
			return self::isPassword( $password );
		}

		if ( strlen( $realPassword ) > 64 or strlen( $realPassword ) < 8 )
		{
			return array( 'Password lenght must be < 64 and > 8!' );	
		}

		if ( !preg_match( '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}/', $realPassword, $matches ) or
			preg_match( '/([а-яё])+/ui', $realPassword, $matches ) )
		{
			return array(
				'Password must have at least:',
				'- One lowercase Latin character,',
				'- One uppercase Latin character,',
				'- One numeral,',
				'- One special character like: !@#№%^:&?* etc.',
				'And no other alphabets!' );	
		}
	}
}


trait validCode
{
	/**
	 * The decorator.
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


trait validImage
{
	/**
	 * The decorator.
	 * Standard image file validation.
	 * Its shouldn't be empty and must be image (.jpg, .jpeg, .png),
	 * Its size should be no more 1000000 byte.
	 *
	 * @param array $img
	 *
	 * @return array
	 */
	protected static function isImage( $image )
	{
		$filePath  = $image['tmp_name'];
		$errorCode = $image['error'];

		if ( $errorCode !== UPLOAD_ERR_OK || !is_uploaded_file( $filePath ) )
		{
			$errorMessages = [
				UPLOAD_ERR_INI_SIZE   => 'File size exceeds 1 MB! (code 1)',
				UPLOAD_ERR_FORM_SIZE  => 'File size exceeds 1 MB! (code 2)',
				UPLOAD_ERR_PARTIAL    => 'Incorrect file upload! (code 3)',
				UPLOAD_ERR_NO_FILE    => 'File was not uploaded! (code 4)',
				UPLOAD_ERR_NO_TMP_DIR => 'Server error! (code 5)',
				UPLOAD_ERR_CANT_WRITE => 'Server error! (code 6)',
				UPLOAD_ERR_EXTENSION  => 'Server error! (code 7)',
			];

			$unknownError = 'Unknown error (code 8)...';

			return array( isset( $errorMessages[$errorCode] ) ? $errorMessages[$errorCode] : $unknownError );
		}

		$fi = finfo_open( FILEINFO_MIME_TYPE );

		$mime = ( string ) finfo_file( $fi, $filePath );

		if ( strpos( $mime, 'image' ) === false ) return array( 'It is not image!' );

		$image = getimagesize( $filePath );

		$limitBytes  = 1000000;
		$limitWidth  = 1600;
		$limitHeight = 900;

		if ( filesize( $filePath ) > $limitBytes ) return array( 'File size exceeds 1 MB!' );
		if ( $image[1] > $limitHeight )            return array( 'Image width not must be more 1280px!' );
		if ( $image[0] > $limitWidth )             return array( 'Image height not must be more 768px!' );
	}

	/**
	 * Transfer images from a temporary folder to the cloud
	 *
	 * @param string $dir
	 *
	 * @return array
	 */
	protected static function save_img_to( $image, $dir )
	{
		$filePath = $image['tmp_name'];

		$name = md5( $filePath . time() );

		$extension = image_type_to_extension( getimagesize( $filePath )[2] );

		$format = str_replace( 'jpeg', 'jpg', $extension );

		$avatarAbsPath = $_SERVER['DOCUMENT_ROOT'] . '/cloud/' . $dir . '/' . $name . $format;

		$avatarAbsHTMLPath = '/cloud/' . $dir . '/' . $name . $format;

		if ( !move_uploaded_file( $filePath, $avatarAbsPath ) )
		{
			die( 'Server error... code (9)' );
		}

		return $avatarAbsHTMLPath;
	}
}


trait validTitle
{
	/**
	 * The decorator.
	 * Standard question title validation.
	 * Its shouldn't be empty and must be not less 10 and not more 86.
	 */

	/**
	 * @param string $title
	 *
	 * @return array
	 */
	protected static function isTitle( $title )
	{
		$realTitle = htmlspecialchars_decode( $title, ENT_QUOTES );

		if ( $title == '' )
		{
			return array( 'Enter the title!' );
		}

		if ( strlen( $realTitle ) > 86 or strlen( $realTitle ) < 10 )
		{
			return array( 'Title lenght must be < 86 and > 10!' );
		}
	}
}


trait validText
{
	/**
	 * The decorator.
	 * Standard textarea validation.
	 * Its shouldn't be empty.
	 */

	/**
	 * @param string $text
	 *
	 * @return array
	 */
	protected static function isText( $text )
	{
		if ( $text == '' )
		{
			return array( 'Write a few words!' );
		}
	}
}


final class RecoveryHandler extends Base
{
	/**
	 * The Singleton class.
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

		$_SESSION['email'] = $this->email;
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

		if ( !R::count( 'users', 'email = ?', array( $this->email ) ) )
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
	protected function showSuccess( $template )
	{
		$RandomPassword = self::getRandomPassword();

		$account = R::findOne( 'users', 'email = ?', array( $_SESSION['email'] ) );

		$newPassword = password_hash( $RandomPassword, PASSWORD_DEFAULT );

		$account->password = $newPassword;
		$login = $account->login;
		R::store( $account );

		@$_SESSION['user']->password = $newPassword;

		SMTP::sendRecovery( $login, $RandomPassword );
		unset( $_SESSION['email'] );

		load_template( $template, array(
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

	    while( $max-- )
	    $password[] = $CharSet[rand( 0, $size )];

		while(!preg_match( '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}/', implode($password), $matches) )
		$password[rand( 0, $max - 1 )] = $CharSet[rand( 0, $size )];

		return implode( $password );
	}
}


final class ActivationHandler extends Base
{
	/**
	 * The Singleton class.
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
	protected function showSuccess( $template )
	{
		$user = R::dispense( 'users' );
		$user->login = $_SESSION['login'];
		$user->email = $_SESSION['email'];
		$user->password = $_SESSION['password'];
		$user->avatar = 'https://via.placeholder.com/156x176?text='.preg_replace( '/\s/', '+', htmlspecialchars_decode( $_SESSION['login'], ENT_QUOTES ) );
		R::store( $user );

		$_SESSION['user'] = $user;

		unset( $_SESSION['password'] );
		unset( $_SESSION['activation'] );
		unset( $_SESSION['login'] );
		unset( $_SESSION['email'] );

		load_template( $template, array(
			'message_color' => 'green',
			'message' => 'Successful! You can go to the Main page') );
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
	 * @param array $errors
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


final class LoginHandler extends Base
{
	/**
	 * The Singleton class.
	 * Login.php data handler.
	 * Using post arguments: $login, $password.
	 */

	use validLogin, validPassword;

	/**
	 * Checking login for valid and commit errors, if any.
	 * Also commits an error, if user is not found.
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
			if ( !password_verify( $this->password, $user->password ) )
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
	protected function showSuccess( $template )
	{
		$_SESSION['user'] = $user;

		load_template( $template, array(
			'message_color' => 'green',
			'message' => 'Successful! You can go to the Main page' ) );
	}
}


final class SignupHandler extends Base
{
	/**
	 * The Singleton class.
	 * Signup.php data handler.
	 * Using post arguments: $login, $password, $email, $passwrod_2, $check_box.
	 */

	use newLogin, newEmail, newPassword;

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
	 * Also password must be equal to password_2 and check-box must be succeed.
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( self::isNewLogin( $this->login ) )
		{
			$this->errors[] = self::isNewLogin( $this->login );
		}

		if ( self::isNewEmail( $this->email ) )
		{
			$this->errors[] = self::isNewEmail( $this->email );
		}

		if ( self::isNewPassword( $this->password ) )
		{
			$this->errors[] = self::isNewPassword( $this->password );
		}

		if ( $this->password != $this->password_2 )
		{
			$this->errors[] = array( 'Repeated password is incorrect!' );
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
	protected function showSuccess( $template )
	{
		$_SESSION['activation'] = rand( 1000000000000, 9999999999999 );
		header( 'Location: send_activation.php' );
	}

	/**
	 * Also passes login and email parameters in the template.
	 *
	 * @param string $template
	 *
	 * @param array $errors
	 *
	 * @return void
	 */
	protected function showErrors( $template, $errors )
	{
		load_template( $template, array(
			'login' => htmlspecialchars_decode( $_SESSION['login'], ENT_QUOTES ),
			'email' => htmlspecialchars_decode( $_SESSION['email'], ENT_QUOTES ),
			'message_color' => 'red',
			'message' => array_shift( $errors ) ) );
	}
}

final class ChangeAvatarHandler extends Base
{
	/**
	 * The Singleton class.
	 * Profile.php data handler.
	 * Using post arguments: $image.
	 */

	use validImage;

	/**
	 * @param array $post is users input
	 *
	 * @return void
	 */
	public function __construct( $post, $id )
	{
		parent::__construct( $post );

		$this->id = $id;
		$this->user = R::findOne( 'users', 'id = ?', array( $this->id ) );
		$this->user->login = htmlspecialchars_decode( $this->user->login, ENT_QUOTES );
		if ( !$this->user )
		{
			$this->user = $_SESSION['user'];
			$this->user->login = htmlspecialchars_decode( $this->user->login, ENT_QUOTES );
			$this->id = $this->user->id;
		}
	}

	/**
	 * Checking avatar for valid and commit errors, if any.
	 * Also commits an error, if user is not found.
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( self::isImage( $this->image ) )
		{
			$this->errors[] = self::isImage( $this->image );
		}

		return $this->errors;
	}

	/**
	 * Also passes user and id parameters in the template.
	 *
	 * @param string $template
	 *
	 * @return void
	 */
	protected function showForm( $template )
	{
		load_template( $template, array(
		'user' => $this->user,
		'id' => $_SESSION['user']->id) );
	}

	/**
	 * Also passes user and id parameters in the template.
	 *
	 * @param string $template
	 *
	 * @param array $errors
	 *
	 * @return void
	 */
	protected function showErrors( $template, $errors )
	{
		load_template( $template, array(
			'user' => $this->user,
			'id' => $_SESSION['user']->id,
			'message_color' => 'red',
			'message' => array_shift( $errors ) ) );
	}

	/**
	 * Upload the image, deletes old avatar and displays the new user avatar.
	 *
	 * @return void
	 */
	protected function showSuccess( $template )
	{
		self::save_img_to( $this->image, 'avatars' );

		$account = R::findOne( 'users', 'login = ?', array( $_SESSION['user']->login ) );

		if ( !preg_match( '/https/', $account->avatar, $matches ) )
		{
			unlink( $_SERVER['DOCUMENT_ROOT'] . $account->avatar );
		}

		$account->avatar = $avatarAbsHTMLPath;
		R::store( $account );

		$_SESSION['user']->avatar = $avatarAbsHTMLPath;

		header( 'Location: profile.php' );
	}
}


final class ChangeLoginHandler extends Base
{
	/**
	 * The Singleton class.
	 * Change-login.php data handler.
	 * Using post arguments: $login.
	 */

	use newLogin;

	/**
	 * @param array $post is users input
	 *
	 * @return void
	 */
	function __construct( $post )
	{
		parent::__construct( $post );
	}

	/**
	 * Checking login for valid and commit errors, if any.
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( self::isNewLogin( $this->login ) )
		{
			$this->errors[] = self::isNewLogin( $this->login );
		}

		return $this->errors;
	}

	/**
	 * Changes the login and redirects user to the profile.php.
	 *
	 * @return void
	 */
	protected function showSuccess( $template )
	{
		$account = R::findOne( 'users', 'id = ?', array( $_SESSION['user']->id ) );

		$account->login = $this->login;
		R::store( $account );

		R::exec( 'UPDATE questions SET author = ? WHERE author = ?', array( $this->login, $_SESSION['user']->login ) );

		$_SESSION['login'] = $this->login;

		header( 'Location: profile.php' );
	}
}


final class ChangePasswordHandler extends Base
{
	/**
	 * The Singleton class.
	 * Change-password.php data handler.
	 * Using post arguments: $password, $password_2.
	 */

	use newPassword;

	/**
	 * @param array $post is users input
	 *
	 * @return void
	 */
	function __construct( $post )
	{
		parent::__construct( $post );
	}

	/**
	 * Checking password for valid and commit errors, if any.
	 * Also commits an errors if the old password is not equal to $password post
	 * argumen. And if the old password is equal to $password_2 post argument.
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( !password_verify( $this->password, $_SESSION['user']->password ) )
		{
			$this->errors[] = array( 'Incorrect old password!' );
		}

		if ( $this->password == $this->password_2 )
		{
			$this->errors[] = array( 'The old password is equal to the new password!' );
		}

		if ( self::isNewPassword( $this->password_2 ) )
		{
			$this->errors[] = self::isNewPassword( $this->password_2 );
		}

		return $this->errors;
	}

	/**
	 * Changes the password and displays the success message.
	 *
	 * @return void
	 */
	protected function showSuccess( $template )
	{
		$account = R::findOne( 'users', 'id = ?', array( $_SESSION['user']->id ) );

		$newPassword = password_hash( $this->password_2, PASSWORD_DEFAULT );

		$account->password = $newPassword;
		R::store( $account );

		$_SESSION['user']->password = $newPassword;

		load_template( $template, array(
			'message_color' => 'green',
			'message' => 'Successful! You can go to the Main page') );
	}
}


final class ChangeEmailHandler extends Base
{
	/**
	 * The Singleton class.
	 * Change-emial.php data handler.
	 * Using post arguments: $email.
	 */

	use newEmail;

	/**
	 * @param array $post is users input
	 *
	 * @return void
	 */
	public function __construct( $post )
	{
		parent::__construct( $post );

		$_SESSION['email'] = $this->email;
	}

	/**
	 * Checking email for valid and commit errors, if any.
	 *
	 * @return array
	 */
	public function isValid()
	{
		parent::isValid();

		if ( self::isNewEmail( $this->email ) )
		{
			$this->errors[] = self::isNewEmail( $this->email );
		}

		return $this->errors;
	}

	/**
	 * Page loading if the error array is empty.
	 *
	 * @return void
	 */
	protected function showSuccess( $template )
	{
		$code = rand( 1000000000000, 9999999999999 );
		$link = 'http://tester.su/profile.php?code=' . $code;
		SMTP::sendActivationLink( $link );

		$_SESSION['code'] = $code;

		load_template( $template, array(
			'message_color' => 'green',
			'message' => 'Successful! Check out your email address and can go to the authorization page',
			'link' => $link ) );
	}
}


final class QuestionHandler extends Base
{
	/**
	 * The Singleton class.
	 * Ask.php data handler.
	 * Using post arguments: $image, $title, $text.
	 */

	use validImage, validTitle, validText;

	/**
	 * @param array $post is users input
	 *
	 * @return void
	 */
	public function __construct( $post )
	{
		parent::__construct( $post );
	}

	/**
	 * Checking data for valid and commit errors, if any.
	 * Also commits an error if text length is more then 1000 symbols.
	 *
	 * @return array
	 */
	public function isValid()
	{
		$realText = htmlspecialchars_decode( $this->text, ENT_QUOTES );

		parent::isValid();

		if ( self::isImage( $this->image ) )
		{
			$this->errors[] = self::isImage( $this->image );
		}

		if ( self::isTitle( $this->title ) )
		{
			$this->errors[] = self::isTitle( $this->title );
		}

		if ( self::isText( $this->text ) )
		{
			$this->errors[] = self::isText( $this->text );
		}

		if ( strlen( $realText ) > 1000 )
		{
			$this->errors[] = array( 'Question body is too long!' );
		}

		return $this->errors;
	}

	/**
	 * Saves a question in the DB and and displays the success message.
	 *
	 * @return void
	 */
	protected function showSuccess( $template )
	{
		$previewPath = self::save_img_to( $this->image, 'previews' );

		$question = R::dispense( 'questions' );
		$question->title = $this->title;
		$question->preview = $previewPath;
		$question->author = htmlspecialchars_decode( $_SESSION['user']->login, ENT_QUOTES );
		$question->body = $this->text;
		$question->date = date( "Y-m-d" );
		$question->views = 0;
		R::store( $question );

		load_template( $template, array(
			'message_color' => 'green',
			'message' => 'Successful post your question!') );
	}
}
