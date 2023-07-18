<?php

namespace MediaWiki\Extension\Math\Tests;

use MediaWikiIntegrationTestCase;
use RequestContext;
use Title;
use User;

/**
 * @covers \MediaWiki\Extension\Math\HookHandlers\PreferencesHooksHandler
 */
class PreferencesIntegrationTest extends MediaWikiIntegrationTestCase {

	public function testInvalidDefaultOptionFixed() {
		$this->mergeMwGlobalArrayValue( 'wgDefaultUserOptions', [ 'math' => 'garbage' ] );
		$this->assertContains(
			$this->getServiceContainer()->getUserOptionsLookup()->getDefaultOption( 'math' ),
			$this->getServiceContainer()->get( 'Math.Config' )->getValidRenderingModes()
		);
	}

	public function testMathOptionRegistered() {
		$context = new RequestContext();
		$context->setTitle( Title::makeTitle( NS_MAIN, 'Dummy' ) );
		$allPreferences = $this->getServiceContainer()
			->getPreferencesFactory()
			->getFormDescriptor( $this->createMock( User::class ), $context );
		$this->assertArrayHasKey( 'math', $allPreferences );
		$mathPrefs = $allPreferences['math'];
		$this->assertSame( 'radio', $mathPrefs['type'] );
		$this->assertSame(
			$this->getServiceContainer()->getUserOptionsLookup()->getDefaultOption( 'math' ),
			$mathPrefs['default']
		);
	}
}
