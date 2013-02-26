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

	public function testGetModuleContentsAcceptsRelativePath() {
		$resolver = $this->getResolver();
		$filepath = './modules/apple/index';
		$this->assertEquals("// Apple\n", $resolver->getModuleContents($filepath));
	}

	public function testGetModuleContentsAcceptsTopLevelPath() {
		$resolver = $this->getResolver();
		$tlipath = CJSD_TESTMODS_DIR . '/apple/index';
		$this->assertEquals("// Apple\n", $resolver->getModuleContents($tlipath));
	}

	public function testAddModuleAcceptsRelativePath() {
		$resolver = $this->getResolver();
		$filepath = './modules/apple/index';
		$this->assertEquals('index', $resolver->addModule($filepath));
	}

	public function testAddModuleAcceptsTopLevelPath() {
		$resolver = $this->getResolver();
		$tlipath = CJSD_TESTMODS_DIR . '/apple/index';
		$this->assertEquals('index', $resolver->addModule($tlipath));
	}

	public function testHasModule() {
		$resolver = $this->getResolver();
		$tlipath = CJSD_TESTMODS_DIR . '/apple/index';
		$resolver->addModule($tlipath);
		$this->assertTrue($resolver->hasModule($tlipath));
	}
}
