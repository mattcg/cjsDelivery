<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

class FileDependencyResolverTest extends PHPUnit_Framework_TestCase {

	private function getResolver() {
		$identifiermanager = new MattCG\cjsDelivery\FileIdentifierManager(new MattCG\cjsDelivery\FlatIdentifierGenerator());
		return new MattCG\cjsDelivery\FileDependencyResolver($identifiermanager);
	}

	private function getFileContents($realpath) {
		return file_get_contents($realpath, false);
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
	 * @expectedExceptionMessage Module identifiers may not have file-name extensions like ".js" (found "index.js").
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
		$realpath = $toplevelidentifier . '.js';
		$this->assertFileExists($realpath);
		$this->assertEquals("require('./pips');\nrequire('./stalk');\n", $this->getFileContents($realpath));

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

	public function testAddModuleAcceptsCode() {
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/apple/index';
		$realpath = $toplevelidentifier . '.js';
		$this->assertFileExists($realpath);
		$code = $this->getFileContents($realpath);

		// Sneakily inject a dependency on pear into the code
		$code .= PHP_EOL . "require('../pear/index');";

		$resolver = $this->getResolver();
		$this->assertEquals('index1', $resolver->addModule($toplevelidentifier, $code));
		$this->assertTrue($resolver->hasModule(CJSD_TESTMODS_DIR . '/pear/index'));
		$this->assertTrue($resolver->hasModule(CJSD_TESTMODS_DIR . '/pear/pips'));
		$this->assertTrue($resolver->hasModule(CJSD_TESTMODS_DIR . '/pear/stalk'));
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

	public function testJsonFileIsTransformedToModule() {
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/data';
		$realpath = $toplevelidentifier . '.json';
		$this->assertFileExists($realpath);
		$this->assertEquals("{\n\t\"test\": \"hello\"\n}\n", $this->getFileContents($realpath));

		$resolver = $this->getResolver();
		$resolver->addModule($toplevelidentifier);
		$this->assertTrue($resolver->hasModule($toplevelidentifier));

		$module = $resolver->getModule($toplevelidentifier);
		$this->assertEquals("module.exports = {\n\t\"test\": \"hello\"\n};\n", $module->getCode());
	}
}
