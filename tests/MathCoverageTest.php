<?php
/**
 * PHPUnit tests to test the wide range of all typical use cases for formulae at Wikipedia.
 * To generate the page https://www.mediawiki.org/wiki/Extension:Math/CoverageTest is used to
 * generate the test data.
 * The testData is generated by the maintenance script Math/maintenance/MathGenerateTests.php.
 * To update the test data locally with vagrant the following procedure is recommended:
 *
 * 1. copy the source from https://www.mediawiki.org/wiki/Extension:Math/CoverageTest to a new page e.g.
 *    MathTest at your local vagrant instance
 * 2. run <code>php MathGenerateTests.php MathTest</code> in the maitenance folder of the Math extension.
 * 3. Test local e.g. via
 *    <code>sudo php /vagrant/mediawiki/tests/phpunit/phpunit.php /vagrant/mediawiki/extensions/Math/tests/MathCoverageTest.php</code>
 *    (If you don't use sudo you might have problems with the permissions set at vagrant.)
 *
 * @group Extensions
 * @group Math
 */
class MathCoverageTest extends MediaWikiTestCase {
	protected static $hasTexvc;
	protected static $texvcPath;

	public static function setUpBeforeClass() {
		global $wgTexvc;

		if ( is_executable( $wgTexvc ) ) {
			wfDebugLog( __CLASS__, " using build in texvc from "
				. "\$wgMathTexvcCheckExecutable = $wgTexvc" );
			# Using build-in
			self::$hasTexvc = true;
			self::$texvcPath = $wgTexvc;
		} else {
			# Attempt to compile
			wfDebugLog( __CLASS__, " compiling texvc..." );
			$cmd = 'cd ' . dirname( __DIR__ ) . '/math; make --always-make 2>&1';
			wfShellExec( $cmd, $retval );
			if ( $retval === 0 ) {
				self::$hasTexvc = true;
				self::$texvcPath = dirname( __DIR__ ) . '/math/texvc';
				wfDebugLog( __CLASS__, ' compiled texvc at ' . self::$texvcPath );
			} else {
				wfDebugLog( __CLASS__, ' ocaml not available or compilation of texvc failed' );
			}
		}
	}

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		parent::setUp();

		if ( ! self::$hasTexvc ) {
			$this->markTestSkipped( "No texvc installed on server" );
		} else {
			$this->setMwGlobals( 'wgTexvc',
				self::$texvcPath );
		}
	}
	/**
	 * Loops over all test cases provided by the provider function.
	 * Compares each the rendering result of each input with the expected output.
	 * @dataProvider testProvider
	 */
	public function testCoverage( $input, $output )
	{
		// TODO: Make rendering mode configurable
		// TODO: Provide test-ids
		// TODO: Link to the wikipage that contains the reference rendering
		$this->assertEquals(
			$this->normalize( $output ),
			$this->normalize( MathRenderer::renderMath( $input, array(), MW_MATH_PNG ) ),
			"Failed to render $input"
		);
	}

	/**
	 * Gets the test-data from the file ParserTest.json
	 * @return array($input, $output) where $input is the test input string and $output is the rendered html5-output string
	 */
	public function testProvider()
	{
		return json_decode( file_get_contents( dirname( __FILE__ ) . '/ParserTest.json' ) );
	}

	private function normalize( $input ) {
		return preg_replace( '#src="(.*?)/(([a-f]|\d)*).png"#', 'src="\2.png"', $input );
	}
}