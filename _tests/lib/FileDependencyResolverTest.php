<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

// Class under test
require_once CJSD_LIB_DIR . '/FileDependencyResolver.php';

// Dependencies of the class under test
require_once CJSD_LIB_DIR . '/FileIdentifierManager.php';
require_once CJSD_LIB_DIR . '/FlatIdentifierGenerator.php';

class FileDependencyResolverTest extends PHPUnit_Framework_TestCase {

	private function getResolver() {
		$identifiermanager = new cjsDelivery\FileIdentifierManager(new cjsDelivery\FlatIdentifierGenerator());
		return new cjsDelivery\FileDependencyResolver($identifiermanager);
	}

	public function testAddModuleAcceptsRelativePath() {
		$identifier = './modules/apple/index';
		$this->assertFileExists($identifier . '.js');

		$resolver = $this->getResolver();
		$this->assertEquals('index', $resolver->addModule($identifier));
	}

	public function testAddModuleAcceptsTopLevelPath() {
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/apple/index';
		$this->assertFileExists($toplevelidentifier . '.js');

		$resolver = $this->getResolver();
		$this->assertEquals('index', $resolver->addModule($toplevelidentifier));
	}


	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testAddModuleTriggersNoticeIfIdentifierContainsExtension() {
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/apple/index.js';
		$this->assertFileExists($toplevelidentifier);

		$resolver = $this->getResolver();
		$this->assertEquals('index', $resolver->addModule($toplevelidentifier));
	}

	public function testHasModule() {
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/apple/index';
		$this->assertFileExists($toplevelidentifier . '.js');

		$resolver = $this->getResolver();
		$resolver->addModule($toplevelidentifier);
		$this->assertTrue($resolver->hasModule($toplevelidentifier));

		$toplevelidentifier = CJSD_TESTMODS_DIR . '/nonexistent';
		$this->assertFileNotExists($toplevelidentifier . '.js');

		$this->assertFalse($resolver->hasModule($toplevelidentifier));
	}
}
