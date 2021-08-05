<?php

use MediaWiki\Extension\Math\MathMathML;
use MediaWiki\MediaWikiServices;
use Wikimedia\TestingAccessWrapper;

/**
 * Test the MathML output format.
 *
 * @covers \MediaWiki\Extension\Math\MathMathML
 *
 * @group Math
 *
 * @license GPL-2.0-or-later
 */
class MathMathMLTest extends MediaWikiTestCase {
	use MockHttpTrait;

	protected function setUp(): void {
		parent::setUp();
		$this->setMwGlobals( 'wgMathoidCli', false );
	}

	/**
	 * @covers \MediaWiki\Extension\Math\MathMathML::__construct
	 */
	public function testMathMLConstructorWithPmml() {
		$mml = new MathMathML( '<mo>sin</mo>', [ 'type' => 'pmml' ] );
		$this->assertSame( 'pmml', $mml->getInputType() );
		$this->assertSame( '<math><mo>sin</mo></math>', $mml->getMathml() );
	}

	/**
	 * @covers \MediaWiki\Extension\Math\MathMathML::__construct
	 */
	public function testMathMLConstructorWithInvalidType() {
		$mml = new MathMathML( '<mo>sin</mo>', [ 'type' => 'invalid' ] );
		$this->assertSame( 'tex', $mml->getInputType() );
	}

	/**
	 * @covers \MediaWiki\Extension\Math\MathMathML::__construct
	 */
	public function testChangeRootElemts() {
		$mml = new MathMathML( '<mo>sin</mo>', [ 'type' => 'invalid' ] );
		$mml->setAllowedRootElements( [ 'a','b' ] );
		$this->assertSame( [ 'a','b' ], $mml->getAllowedRootElements() );
	}

	/**
	 * Tests behavior of makeRequest() that communicates with the host.
	 * Testcase: Invalid request.
	 * @covers \MediaWiki\Extension\Math\MathMathML::makeRequest
	 */
	public function testMakeRequestInvalid() {
		$this->installMockHttp(
			$this->makeFakeHttpRequest( 'Method Not Allowed', 405 )
		);

		$renderer = new MathMathML();
		$url = 'http://example.com/invalid';

		$requestReturn = $renderer->makeRequest( $url, 'a+b', $res, $error );
		$this->assertFalse( $requestReturn,
			"requestReturn is false if HTTP::post returns false." );
		$this->assertNull( $res,
			"res is null if HTTP::post returns false." );
		$errmsg = wfMessage( 'math_invalidresponse', '', $url, 'Method Not Allowed' )
			->inContentLanguage()
			->escaped();
		$this->assertStringContainsString( $errmsg, $error,
			"return an error if HTTP::post returns false" );
	}

	/**
	 * Tests behavior of makeRequest() that communicates with the host.
	 * Testcase: Valid request.
	 * @covers \MediaWiki\Extension\Math\MathMathML::makeRequest
	 */
	public function testMakeRequestSuccess() {
		$this->installMockHttp(
			$this->makeFakeHttpRequest( 'test content' )
		);
		$url = 'http://example.com/valid';
		$renderer = new MathMathML();

		$requestReturn = $renderer->makeRequest( $url, 'a+b', $res, $error );
		$this->assertTrue( $requestReturn, "successful call return" );
		$this->assertSame( 'test content', $res, 'successful call' );
		$this->assertSame( '', $error, "successful call error-message" );
	}

	/**
	 * Tests behavior of makeRequest() that communicates with the host.
	 * Testcase: Timeout.
	 * @covers \MediaWiki\Extension\Math\MathMathML::makeRequest
	 */
	public function testMakeRequestTimeout() {
		$this->installMockHttp(
			$this->makeFakeTimeoutRequest()
		);
		$url = 'http://example.com/timeout';
		$renderer = new MathMathML();

		$requestReturn = $renderer->makeRequest(
			$url, '$\longcommand$', $res, $error
		);
		$this->assertFalse( $requestReturn, "timeout call return" );
		$this->assertFalse( $res, "timeout call return" );
		$errmsg = wfMessage( 'math_timeout', '', $url )->inContentLanguage()->escaped();
		$this->assertStringContainsString( $errmsg, $error, "timeout call errormessage" );
	}

	/**
	 * Tests behavior of makeRequest() that communicates with the host.
	 * Test case: Get PostData.
	 * @covers \MediaWiki\Extension\Math\MathMathML::makeRequest
	 */
	public function testMakeRequestGetPostData() {
		$this->installMockHttp( $this->makeFakeHttpRequest() );
		$url = 'http://example.com/timeout';
		$renderer = $this->getMockBuilder( MathMathML::class )
			->onlyMethods( [ 'getPostData' ] )
			->getMock();
		$renderer->expects( $this->once() )->method( 'getPostData' );

		/** @var MathMathML $renderer */
		$renderer->makeRequest( $url, false, $res, $error );
	}

	/**
	 * Tests behavior of makeRequest() that communicates with the host.
	 * Test case: Get host.
	 * @covers \MediaWiki\Extension\Math\MathMathML::pickHost
	 */
	public function testMakeRequestGetHost() {
		$this->installMockHttp( $this->makeFakeHttpRequest() );
		$renderer = $this->getMockBuilder( MathMathML::class )
			->onlyMethods( [ 'getPostData', 'pickHost' ] )
			->getMock();
		$renderer->expects( $this->once() )->method( 'pickHost' );

		/** @var MathMathML $renderer */
		$renderer->makeRequest( false, false, $res, $error );
	}

	/**
	 * Checks if a String is a valid MathML element
	 * @covers \MediaWiki\Extension\Math\MathMathML::isValidMathML
	 */
	public function testisValidMathML() {
		$renderer = new MathMathML();
		$validSample = '<math>content</math>';
		$invalidSample = '<notmath />';
		$this->assertTrue( $renderer->isValidMathML( $validSample ),
			'test if math expression is valid mathml sample' );
		$this->assertFalse( $renderer->isValidMathML( $invalidSample ),
			'test if math expression is invalid mathml sample' );
	}

	/**
	 * @covers \MediaWiki\Extension\Math\MathMathML::isValidMathML
	 */
	public function testInvalidXml() {
		$renderer = new MathMathML();
		$invalidSample = '<mat';
		$this->assertFalse( $renderer->isValidMathML( $invalidSample ),
			'test if math expression is invalid mathml sample' );
		$renderer->setXMLValidation( false );
		$this->assertTrue( $renderer->isValidMathML( $invalidSample ),
			'test if math expression is invalid mathml sample' );
	}

	public function testintegrationTestWithLinks() {
		$this->markTestSkipped( 'All HTTP requests are banned in tests. See T265628.' );
		$p = MediaWikiServices::getInstance()->getParserFactory()->create();
		$po = ParserOptions::newFromAnon();
		$t = Title::newFromText( __METHOD__ );
		$res = $p->parse( '[[test|<math forcemathmode="png">a+b</math>]]', $t, $po )->getText();
		$this->assertStringContainsString( '</a>', $res );
		$this->assertStringContainsString( 'png', $res );
	}

	/**
	 * @covers \MediaWiki\Extension\Math\MathMathML::correctSvgStyle
	 * @see https://phabricator.wikimedia.org/T132563
	 */
	public function testMathMLStyle() {
		$m = new MathMathML();
		$m->setSvg( 'style="vertical-align:-.505ex" height="2.843ex" width="28.527ex"' );
		$style = '';
		$m->correctSvgStyle( $style );
		$this->assertSame( 'vertical-align:-.505ex; height: 2.843ex; width: 28.527ex;', $style );
		$m->setSvg( 'style=" vertical-align:-.505ex; \n" height="2.843ex" width="28.527ex"' );
		$this->assertSame( 'vertical-align:-.505ex; height: 2.843ex; width: 28.527ex;', $style );
	}

	public function testPickHost() {
		$hosts = [ 'a', 'b', 'c' ];
		$this->setMwGlobals( 'wgMathMathMLUrl', $hosts );
		srand( 0 ); // Make array_rand always return the same elements
		$h1 = $hosts[array_rand( $hosts )];
		$h2 = $hosts[array_rand( $hosts )];
		srand( 0 );
		/** @var MathMathML $m */
		$m = TestingAccessWrapper::newFromObject( new MathMathML() );
		$host1 = $m->pickHost();
		$this->assertSame( $h1, $host1, 'first call' );
		$host2 = $m->pickHost();
		$this->assertSame( $host1, $host2, 'second call' );
		/** @var MathMathML $m2 */
		$m2 = TestingAccessWrapper::newFromObject( new MathMathML() );
		$host3 = $m2->pickHost();
		$this->assertSame( $h2, $host3, 'third call' );
	}

	public function testWarning() {
		$this->markTestSkipped( 'All HTTP requests are banned in tests. See T265628.' );
		$this->setMwGlobals( "wgMathDisableTexFilter", 'always' );
		$renderer = new MathMathML();
		$rbi = $this->getMockBuilder( MathRestbaseInterface::class )
			->onlyMethods( [ 'getWarnings', 'getSuccess' ] )
			->setConstructorArgs( [ 'a+b' ] )
			->getMock();
		$rbi->method( 'getWarnings' )->willReturn( [ (object)[ 'type' => 'mhchem-deprecation' ] ] );
		$rbi->method( 'getSuccess' )->willReturn( true );
		$renderer->setRestbaseInterface( $rbi );
		$renderer->render();
		$parser = $this->createMock( Parser::class );
		$parser->method( 'addTrackingCategory' )->willReturn( true );
		$parser->expects( $this->once() )
			->method( 'addTrackingCategory' )
			->with( 'math-tracking-category-mhchem-deprecation' );
		$renderer->addTrackingCategories( $parser );
	}

	public function testGetHtmlOutputQID() {
		$math = new MathMathML( "a+b", [ "qid" => "Q123" ] );
		$out = $math->getHtmlOutput();
		$this->assertStringContainsString( "data-qid=\"Q123\"", $out );
	}

	public function testGetHtmlOutputInvalidQID() {
		// test with not valid ID. An ID must match /Q\d+/
		$math = new MathMathML( "a+b", [ "qid" => "123" ] );
		$out = $math->getHtmlOutput();
		$this->assertStringNotContainsString( "data-qid", $out );
	}
}
