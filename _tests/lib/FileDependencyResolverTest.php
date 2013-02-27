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

	public function testRelativeDependenciesAreResolved() {
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/pear/index';
		$this->assertFileExists($toplevelidentifier . '.js');
		$this->assertEquals("require('./pips');\nrequire('./stalk');\n", file_get_contents($toplevelidentifier . '.js'));

		$resolver = $this->getResolver();
		$resolver->addModule($toplevelidentifier);
		$this->assertTrue($resolver->hasModule($toplevelidentifier));
		$this->assertTrue($resolver->hasModule(CJSD_TESTMODS_DIR . '/pear/pips'));
		$this->assertTrue($resolver->hasModule(CJSD_TESTMODS_DIR . '/pear/stalk'));

		$dependencies = $resolver->getAllDependencies();
		$this->assertEquals("require('pips');\nrequire('stalk');\n", $dependencies[$toplevelidentifier]->getCode());
		$this->assertEquals("// Pips\n", $dependencies[CJSD_TESTMODS_DIR . '/pear/pips']->getCode());
		$this->assertEquals("// Stalk\n", $dependencies[CJSD_TESTMODS_DIR . '/pear/stalk']->getCode());
	}

	public function testDependenciesWithinIncludesAreResolved() {
		$identifier = 'pear/index';
		$this->assertFileExists('modules/' . $identifier . '.js');

		$resolver = $this->getResolver();

		$manager = $resolver->getIdentifierManager();
		$manager->setIncludes(array(CJSD_TESTMODS_DIR));

		$resolver->addModule($identifier);
		$this->assertTrue($resolver->hasModule(CJSD_TESTMODS_DIR . '/' . $identifier));
		$this->assertTrue($resolver->hasModule(CJSD_TESTMODS_DIR . '/pear/pips'));
		$this->assertTrue($resolver->hasModule(CJSD_TESTMODS_DIR . '/pear/stalk'));

		$dependencies = $resolver->getAllDependencies();
		$this->assertEquals("require('pips');\nrequire('stalk');\n", $dependencies[CJSD_TESTMODS_DIR . '/' . $identifier]->getCode());
		$this->assertEquals("// Pips\n", $dependencies[CJSD_TESTMODS_DIR . '/pear/pips']->getCode());
		$this->assertEquals("// Stalk\n", $dependencies[CJSD_TESTMODS_DIR . '/pear/stalk']->getCode());
	}
}
