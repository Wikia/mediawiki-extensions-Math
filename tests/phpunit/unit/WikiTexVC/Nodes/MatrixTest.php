<?php

namespace MediaWiki\Extension\Math\Tests\WikiTexVC\Nodes;

use ArgumentCountError;
use InvalidArgumentException;
use MediaWiki\Extension\Math\WikiTexVC\Nodes\Literal;
use MediaWiki\Extension\Math\WikiTexVC\Nodes\Matrix;
use MediaWiki\Extension\Math\WikiTexVC\Nodes\TexArray;
use MediaWikiUnitTestCase;
use TypeError;

/**
 * @covers \MediaWiki\Extension\Math\WikiTexVC\Nodes\Matrix
 */
class MatrixTest extends MediaWikiUnitTestCase {
	private $sampleMatrix;

	protected function setUp(): void {
		parent::setUp();
		$this->sampleMatrix = new Matrix( 'align',
			new TexArray( new TexArray( new Literal( 'a' ) ) ) );
	}

	public function testEmptyMatrix() {
		$this->expectException( ArgumentCountError::class );
		new Matrix();
		throw new TypeError( 'Should not create an empty matrix' );
	}

	public function testNestedArguments() {
		$this->expectException( InvalidArgumentException::class );
		new Matrix(
			'align',
			new TexArray(
				new Literal( 'a' ) ) );
		throw new TypeError( 'Nested arguments have to be type of TexArray' );
	}

	public function testInstanceOfTexArray() {
		$this->assertEquals( 'MediaWiki\\Extension\\Math\\WikiTexVC\\Nodes\\TexArray',
			get_parent_class( $this->sampleMatrix ),
			'Should create an instance of TexArray' );
	}

	public function testGetters() {
		$this->assertNotEmpty( $this->sampleMatrix->getTop() );
		$this->assertNotEmpty( $this->sampleMatrix->getMainarg() );
	}

	public function testRenderMatrix() {
		$this->assertEquals( '{\\begin{align}a\\end{align}}', $this->sampleMatrix->render() );
	}

	public function testExtractCurlies() {
		 $this->assertEquals( '{\\begin{align}a\\end{align}}', $this->sampleMatrix->inCurlies(),
			'Should not create extra curlies' );
	}

	public function testExtractIdentifiers() {
		$this->assertEquals( [ 'a' ], $this->sampleMatrix->extractIdentifiers(),
			'Should extract identifiers' );
	}

	public function testCreateFromMatrix() {
		$matrix = new Matrix( 'align',
			new TexArray( new TexArray( new Literal( 'a' ) ) ) );
		$newMatrix = new Matrix( 'align', $matrix );
		$this->assertEquals( $matrix, $newMatrix,
			'Should create a matrix from a matrix' );
	}

	public function testContainsTop() {
		$matrix = new Matrix( 'top',
			new TexArray( new TexArray( new Literal( 'a' ) ) ) );
		$this->assertTrue( $matrix->containsFunc( '\\begin{top}' ),
			'Should create top attribute' );
	}

	public function testContainsArgs() {
		$matrix = new Matrix( 'top',
			new TexArray( new TexArray( new Literal( '\\sin' ) ) ) );
		$this->assertTrue( $matrix->containsFunc( '\\sin' ),
			'Should contain inner elements' );
	}

	public function testRenderMML() {
		$matrix = new Matrix( 'matrix',
			new TexArray( new TexArray( new Literal( '\\sin' ) ) ) );
		$this->assertStringContainsString( 'mtable', $matrix->renderMML(),
			'Should render a matrix' );
	}

	public function testTop() {
		$this->sampleMatrix->setTop( 'abc' );
		$this->assertEquals( 'abc', $this->sampleMatrix->getTop() );
	}

	public function testColSpec() {
		$this->sampleMatrix->setColumnSpecs( TexArray::newCurly( new Literal( '2' ) ) );
		$this->assertEquals( '{2}', $this->sampleMatrix->getColumnSpecs()->render() );
	}
}
