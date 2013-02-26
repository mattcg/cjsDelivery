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

	public function testGetModuleContents() {
		$resolver = $this->getResolver();
		$realpath = CJSD_TESTMODS_DIR . '/apple/index.js';
		$this->assertEquals("// Apple\n", $resolver->getModuleContents($realpath));
	}

	public function testAddModuleWithRelativePath() {
		$resolver = $this->getResolver();
		$filepath = './modules/apple/index';
		$this->assertEquals('index', $resolver->addModule($filepath));
	}

	public function testAddModuleWithRealPath() {
		$resolver = $this->getResolver();
		$realpath = CJSD_TESTMODS_DIR . '/apple/index';
		$this->assertEquals('index', $resolver->addModule($realpath));
	}

	public function testHasModule() {
		$resolver = $this->getResolver();
		$realpath = CJSD_TESTMODS_DIR . '/apple/index';
		$resolver->addModule($realpath);
		$this->assertTrue($resolver->hasModule($realpath));
	}
}
