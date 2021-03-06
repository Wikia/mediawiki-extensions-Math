<?php


/**
 * Test the MathML output format.
 *
 * @covers MathMathML
 *
 * @group Math
 *
 * @license GNU GPL v2+
 */
class MathMathMLTest extends MediaWikiTestCase {

	// State-variables for HTTP Mockup classes
	public static $content = null;
	public static $good = false;
	public static $html = false;
	public static $timeout = false;

	/**
	 * Set the mock values for the HTTP Mockup classes
	 *
	 * @param bool $good
	 * @param mixed $html HTML of the error message or false if no error is present.
	 * @param bool $timeout true if
	 */
	public static function setMockValues( $good, $html, $timeout ) {
		self::$good = $good;
		self::$html = $html;
		self::$timeout = $timeout;
	}

	protected function setUp() {
		parent::setUp(); // TODO: Change the autogenerated stub
		$this->setMwGlobals( 'wgMathoidCli', false );
	}

	/**
	 * Tests behavior of makeRequest() that communicates with the host.
	 * Testcase: Invalid request.
	 * @covers MathMathML::makeRequest
	 */
	public function testMakeRequestInvalid() {
		self::setMockValues( false, false, false );
		$url = 'http://example.com/invalid';

		$renderer = $this->getMockBuilder( 'MathMathML' )
				->setMethods( null )
				->disableOriginalConstructor()
				->getMock();
		$requestReturn = $renderer->makeRequest( $url, 'a+b', $res, $error,
			'MathMLHttpRequestTester' );
		$this->assertEquals( false, $requestReturn,
			"requestReturn is false if HTTP::post returns false." );
		$this->assertEquals( false, $res,
			"res is false if HTTP:post returns false." );
		$errmsg = wfMessage( 'math_invalidresponse', '', $url, '' )->inContentLanguage()->escaped();
		$this->assertContains( $errmsg, $error,
			"return an error if HTTP::post returns false" );
	}

	/**
	 * Tests behavior of makeRequest() that communicates with the host.
	 * Testcase: Valid request.
	 * @covers MathMathML::makeRequest
	 */
	public function testMakeRequestSuccess() {
		self::setMockValues( true, true, false );
		self::$content = 'test content';
		$url = 'http://example.com/valid';
		/** @var MathMathML */
		$renderer = $this->getMockBuilder( 'MathMathML' )
				->setMethods( null )
				->disableOriginalConstructor()
				->getMock();
		$requestReturn = $renderer->makeRequest( $url, 'a+b', $res, $error,
			'MathMLHttpRequestTester' );
		$this->assertEquals( true, $requestReturn, "successful call return" );
		$this->assertEquals( 'test content', $res, 'successful call' );
		$this->assertEquals( $error, '', "successful call error-message" );
	}

	/**
	 * Tests behavior of makeRequest() that communicates with the host.
	 * Testcase: Timeout.
	 * @covers MathMathML::makeRequest
	 */
	public function testMakeRequestTimeout() {
		self::setMockValues( false, true, true );
		$url = 'http://example.com/timeout';
		$renderer = $this->getMockBuilder( 'MathMathML' )
				->setMethods( null )
				->disableOriginalConstructor()
				->getMock();
		$requestReturn = $renderer->makeRequest(
			$url, '$\longcommand$', $res, $error, 'MathMLHttpRequestTester'
		);
		$this->assertEquals( false, $requestReturn, "timeout call return" );
		$this->assertEquals( false, $res, "timeout call return" );
		$errmsg = wfMessage( 'math_timeout', '', $url )->inContentLanguage()->escaped();
		$this->assertContains( $errmsg, $error, "timeout call errormessage" );
	}

	/**
	 * Checks if a String is a valid MathML element
	 * @covers MathMathML::isValidMathML
	 */
	public function testisValidMathML() {
		$renderer = $this->getMockBuilder( 'MathMathML' )
				->setMethods( null )
				->disableOriginalConstructor()
				->getMock();
		$validSample = '<math>content</math>';
		$invalidSample = '<notmath />';
		$this->assertTrue( $renderer->isValidMathML( $validSample ),
			'test if math expression is valid mathml sample' );
		$this->assertFalse( $renderer->isValidMathML( $invalidSample ),
			'test if math expression is invalid mathml sample' );
	}

	public function testintegrationTestWithLinks() {
		$p = new Parser();
		$po = new ParserOptions();
		$t = new Title( "test" );
		$res = $p->parse( '[[test|<math forcemathmode="png">a+b</math>]]', $t, $po )->getText();
		$this->assertContains( '</a>', $res );
		$this->assertContains( '.png', $res );
	}

	/**
	 * @covers MathMathML::correctSvgStyle
	 * @see https://phabricator.wikimedia.org/T132563
	 */
	public function testMathMLStyle() {
		$m = new MathMathML();
		$m->setSvg( 'style="vertical-align:-.505ex" height="2.843ex" width="28.527ex"' );
		$style = '';
		$m->correctSvgStyle( $style );
		$this->assertEquals( 'vertical-align:-.505ex; height: 2.843ex; width: 28.527ex;', $style );
		$m->setSvg( 'style=" vertical-align:-.505ex; \n" height="2.843ex" width="28.527ex"' );
		$this->assertEquals( 'vertical-align:-.505ex; height: 2.843ex; width: 28.527ex;', $style );
	}
}

/**
 * Helper class for testing
 * @author physikerwelt
 * @see MWHttpRequestTester
 */
class MathMLHttpRequestTester {

	public static function factory() {
		return new MathMLHttpRequestTester();
	}

	public static function execute() {
		return new MathMLTestStatus();
	}

	public static function getContent() {
		return MathMathMLTest::$content;
	}

}

/**
 * Helper class for testing
 * @author physikerwelt
 * @see Status
 */
class MathMLTestStatus {

	static function isGood() {
		return MathMathMLTest::$good;
	}

	static function hasMessage( $s ) {
		if ( $s == 'http-timed-out' ) {
			return MathMathMLTest::$timeout;
		} else {
			return false;
		}
	}

	static function getHtml() {
		return MathMathMLTest::$html;
	}

	static function getWikiText() {
		return MathMathMLTest::$html;
	}

}
