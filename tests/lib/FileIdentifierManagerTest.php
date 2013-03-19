<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

class FileIdentifierManagerTest extends PHPUnit_Framework_TestCase {

	const JS_SUFFIX = '.js';

	private function getManager() {
		return new MattCG\cjsDelivery\FileIdentifierManager(new MattCG\cjsDelivery\FlatIdentifierGenerator());
	}


	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testNoticeTriggeredByAddIdentifierIfIdentifierContainsExtension() {
		$identifiermanager = $this->getManager();
		$identifier = CJSD_TESTMODS_DIR . '/main.js';

		// Assert that the file exists and is readable
		$this->assertFileExists($identifier);
		$this->assertTrue(is_readable($identifier));
		$identifiermanager->addIdentifier($identifier);
	}


	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testNoticeTriggeredByGetTopLevelIdentifierIfIdentifierContainsExtension() {
		$identifiermanager = $this->getManager();
		$identifier = CJSD_TESTMODS_DIR . '/main.js';

		// Assert that the file exists and is readable
		$this->assertFileExists($identifier);
		$this->assertTrue(is_readable($identifier));
		$identifiermanager->getTopLevelIdentifier($identifier);
	}


	/**
	 * @expectedException MattCG\cjsDelivery\Exception
	 * @expectedExceptionCode 2
	 */
	public function testExceptionThrownIfTopLevelIdentifierIsUnknown() {
		$identifiermanager = $this->getManager();
		$identifier = CJSD_TESTMODS_DIR . '/main';

		// Assert that the file exists and is readable
		$this->assertFileExists($identifier . self::JS_SUFFIX);
		$this->assertTrue(is_readable($identifier . self::JS_SUFFIX));
		$identifiermanager->getFlattenedIdentifier($identifier);
	}


	/**
	 * @expectedException MattCG\cjsDelivery\Exception
	 * @expectedExceptionCode 1
	 */
	public function testExceptionThrownIfFileIsNonexistent() {
		$identifiermanager = $this->getManager();
		$identifier = './nonexistent';

		$this->assertFileNotExists($identifier . self::JS_SUFFIX);
		$identifiermanager->getTopLevelIdentifier($identifier);
	}

	public function testGetTopLevelIdentifierReturnsTopLevelIdentifier() {
		$identifier = CJSD_TESTMODS_DIR . '/main';
		$this->assertFileExists($identifier . self::JS_SUFFIX);

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier, $identifiermanager->getTopLevelIdentifier($identifier));
	}

	public function testGetTopLevelIdentifierDoesNotReturnPathWithExtension() {
		$identifier = CJSD_TESTMODS_DIR . '/main';

		// Assert that the file actually has an extension
		$this->assertFileNotExists($identifier);
		$this->assertFileExists($identifier . self::JS_SUFFIX);

		$identifiermanager = $this->getManager();
		$this->assertStringEndsWith('main', $identifiermanager->getTopLevelIdentifier($identifier));

		// Even if the passed identifier has an extension...
		$this->assertStringEndsWith('main', @$identifiermanager->getTopLevelIdentifier($identifier . self::JS_SUFFIX));
	}

	public function testAddIdentifierReturnsTopLevelIdentifier() {
		$identifier = CJSD_TESTMODS_DIR . '/main';

		// Assert that the file actually has an extension
		$this->assertFileNotExists($identifier);
		$this->assertFileExists($identifier . self::JS_SUFFIX);

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier, $identifiermanager->addIdentifier($identifier));

		// Even if the passed identifier has an extension...
		$this->assertEquals($identifier, @$identifiermanager->getTopLevelIdentifier($identifier . self::JS_SUFFIX));
	}

	public function testFileWithExactPathIsFound() {
		$identifier = CJSD_TESTMODS_DIR . '/main';
		$this->assertFileExists($identifier . self::JS_SUFFIX);

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier, $identifiermanager->getTopLevelIdentifier($identifier));
	}

	public function testIndexJsFileIsFound() {
		$identifier = CJSD_TESTMODS_DIR . '/apple';
		$this->assertFileExists($identifier . '/index.js');

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier . '/index', $identifiermanager->getTopLevelIdentifier($identifier));
	}

	public function testFileWithSameNameAsContainingDirectoryIsFound() {
		$identifier = CJSD_TESTMODS_DIR . '/banana';
		$this->assertFileExists($identifier . '/banana.js');

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier . '/banana', $identifiermanager->getTopLevelIdentifier($identifier));
	}

	public function testOnlyFileInDirectoryIsFound() {
		$identifier = CJSD_TESTMODS_DIR . '/strawberry';
		$this->assertFileExists($identifier . '/main.js');
		$this->assertEquals(1, count(glob($identifier . '/*.js')));

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier . '/main', $identifiermanager->getTopLevelIdentifier($identifier));
	}

	public function testFileSpecifiedInPackageJsonIsFound() {
		$identifier = CJSD_TESTMODS_DIR . '/grapefruit';
		$this->assertFileExists($identifier . '/lib/grapefruit.js');

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier . '/lib/grapefruit', $identifiermanager->getTopLevelIdentifier($identifier));
	}

	public function testIndexJsFileIsChosenOverFileWithSameNameAsDir() {
		$identifier = CJSD_TESTMODS_DIR . '/quince';
		$this->assertFileExists($identifier . '/index.js');
		$this->assertFileExists($identifier . '/quince.js');

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier . '/index', $identifiermanager->getTopLevelIdentifier($identifier));
	}


	/**
	 * @expectedException MattCG\cjsDelivery\Exception
	 * @expectedExceptionCode 1
	 */
	public function testExceptionThrownForAbsolutePathWithNoIncludesSpecified() {
		$identifier = 'modules/main';
		$this->assertFileExists($identifier . self::JS_SUFFIX);
		$this->getManager()->addIdentifier($identifier);
	}

	public function testIncludesAreFound() {
		$identifier = 'main';
		$this->assertFileExists('modules/' . $identifier . self::JS_SUFFIX);

		$identifiermanager = $this->getManager();
		$identifiermanager->setIncludes(array(CJSD_TESTMODS_DIR));
		$this->assertEquals(CJSD_TESTMODS_DIR . '/' . $identifier, $identifiermanager->addIdentifier($identifier));
	}
}
