<?php

/**
 * Tester.su pages rendering without forms.
 */


abstract class Loader
{
	/**
	 * The Factory Method.
	 * Get input out URL.
	 * @url https://ru.wikipedia.org/wiki/%D0%A4%D0%B0%D0%B1%D1%80%D0%B8%D1%87%D0%BD%D1%8B%D0%B9_%D0%BC%D0%B5%D1%82%D0%BE%D0%B4_(%D1%88%D0%B0%D0%B1%D0%BB%D0%BE%D0%BD_%D0%BF%D1%80%D0%BE%D0%B5%D0%BA%D1%82%D0%B8%D1%80%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F)
	 */

	/**
	 * Returns an object based on type.
	 * 
	 * @param string $page
	 *
	 * @param array $param
	 *
	 * @return object
	 */
	public static function initial( $page, $param )
	{
		return new $page( $param );
	}

	/**
	 * Validation and actions with data.
	 *
	 * @param array $get
	 *
	 * @return void
	 */
	public function __construct( $get )
	{
		/**
		 * @var array
		 */
		$this->data = $get;
	}

	/**
	 * Displays the template using params taken from the DB.
	 *
	 * @param template
	 *
	 * @return void
	 */
	abstract public function showPage( $template );
}


final class Index extends Loader
{
	
	/**
	 * Index.php.
	 * Displays the questions based on page param.
	 */

	/**
	 * @see Loader::__construct
	 *
	 * Page GET param validation and gets a questions information from the DB.
	 */
	public function __construct( $get )
	{
		parent::__construct( $get );

		/**
		 * @var integer
		 */
		$this->page = ( int ) @$this->data['page'];

		/**
		 * @var integer
		 */
		$this->all_pages = ceil( R::count( 'questions' ) / 10 );

		/**
		 * @var integer
		 */
		$this->all_questions = R::count( 'questions' );

		if ( !is_int( $this->page ) || !$this->page || !R::count( 'questions', 'id > ?', array( ( $this->page-1 ) * 10 ) ) )
		{
			$this->page = 1;
		}

		$beans[] = R::findAll( 'questions', 'WHERE date BETWEEN ? AND ? ORDER BY views DESC LIMIT ?',
			array( date( "Y-m-d", time() - 60 * 60 * 24 * 7 ), date( "Y-m-d" ), 10 ) );

		$beans[] = R::findAll( 'questions', 'WHERE id BETWEEN ? AND ? ORDER BY views DESC LIMIT ?',
			array( ( $this->page - 1 ) * 10,   $this->page * 10 , 10 ) );

		foreach ($beans as $item)
		{

			$tmp_array = array();

			foreach ($item as $i) {
				$tmp_array[] = $i;
			}

			$this->questions[] = $tmp_array;
		}

		/**
		 * @var array
		 */
		$this->page_numbers = self::getPagination();
	}

	/**
	 * Gets an information to the pagination to the template.
	 *
	 * @return array
	 */
	private function getPagination()
	{
		if ( $this->page == $this->all_pages )
		{
			for ($i=1; $i < 3; $i++)
			{
				if ( $this->page-$i > 0 )
				{
					$p[] = $this->page-$i;
				} else
				{
					break;
				}
			}
		} else
		{
			if ( $this->page-1 >= 1 )
			{
				$p[] = $this->page-1;
			}

			$p[] = $this->page;

			for ($i=1; $i < 3; $i++)
			{
				if ( $this->page+$i < $this->all_pages )
				{
					$p[] = $this->page+$i;
				} else
				{
					break;
				}
			}
		}

		$p[] = $this->all_pages;
		sort( $p );
		return $p;
	}

	/**
	 * @see Loader::showPage
	 */
	public function showPage( $template )
	{
		load_template( $template, array(
		'message_color' => 'green',
		'message' => 'Welcome to the forum, '.htmlspecialchars_decode( $_SESSION['user']->login, ENT_QUOTES).'!',
		'user' => $_SESSION['user'],
		'questions' => $this->questions,
		'all' => $this->all_questions,
		'p' => $this->page_numbers ) );
	}
}


final class Forum extends Loader
{

	/**
	 * Forum.php.
	 * Displays the question based on id param.
	 */

	/**
	 * @see Loader::__construct
	 *
	 * Id GET param validation and views increased, if need were.
	 */
	public function __construct( $get )
	{
		parent::__construct( $get );

		/**
		 * @var integer
		 */
		$this->id = ( int ) @$this->data['id'];

		if ( !is_int( $this->id ) || !$this->id )
		{
			header( 'Location: ask.php' );
		}

		if ( !R::count( 'questions', 'id = ?', array( $this->id ) ) )
		{
			header( 'Location: ask.php' );
		}

		self::viewsCounter();
	}

	/**
	 * Question views counter method.
	 * If the user not visit the question today,
	 * the views row will be increased for 1.
	 *
	 * @return void
	 */
	private function viewsCounter()
	{
		if ( !R::count( 'visitors', 'visistor_id = ? AND page_id = ? AND visit_date = ?',
			array( $_SESSION['user']->id, $this->id, date( "Y-m-d" ) ) ) )
		{
			/* DB cleaning */
			$trash_visit = R::findOne( 'visitors', 'visistor_id = ? AND page_id = ?', array(
				$_SESSION['user']->id, $this->id ) );
			
			if ( $trash_visit )
			{
				R::trash( $trash_visit );
			}

			/* Commits new visit */
			$visit = R::dispense( 'visitors' );

			$visit->visistor_id = $_SESSION['user']->id;
			$visit->page_id = $this->id;
			$visit->visit_date = date( "Y-m-d" );

			R::store( $visit );

			$question = R::findOne( 'questions', 'id = ?', array( $this->id ) );

			$question->views ++;

			R::store( $question );
		}
	}

	/**
	 * @see Loader::showPage
	 */
	public function showPage( $template )
	{
		$data = R::findOne( 'questions', 'id = ?', array( $this->id ) );

		$data  = array(
		'title' => $data['title'],
		'name' => $data['author'],
		'views' => $data['views'],
		'image' => $data['preview'],
		'text' => $data['body'],
		'date' => $data['date'] );

		load_template( $template, array(
			'data' => $data ) );
	}
}
