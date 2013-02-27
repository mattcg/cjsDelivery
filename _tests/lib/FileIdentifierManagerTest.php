<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

// Class under test
require_once CJSD_LIB_DIR . '/FileIdentifierManager.php';

// Dependencies of the class under test
require_once CJSD_LIB_DIR . '/FlatIdentifierGenerator.php';

class FileIdentifierManagerTest extends PHPUnit_Framework_TestCase {

	private function getManager() {
		return new cjsDelivery\FileIdentifierManager(new cjsDelivery\FlatIdentifierGenerator());
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
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 2
	 */
	public function testExceptionThrownIfTopLevelIdentifierIsUnknown() {
		$identifiermanager = $this->getManager();
		$identifier = CJSD_TESTMODS_DIR . '/main';

		// Assert that the file exists and is readable
		$this->assertFileExists($identifier . '.js');
		$this->assertTrue(is_readable($identifier . '.js'));
		$identifiermanager->getFlattenedIdentifier($identifier);
	}


	/**
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 1
	 */
	public function testExceptionThrownIfFileIsNonexistent() {
		$identifiermanager = $this->getManager();
		$identifier = './nonexistent';

		$this->assertFileNotExists($identifier . '.js');
		$identifiermanager->getTopLevelIdentifier($identifier);
	}

	public function testGetTopLevelIdentifierDoesNotReturnPathWithExtension() {
		$identifier = CJSD_TESTMODS_DIR . '/main';

		// Assert that the file actually has an extension
		$this->assertFileNotExists($identifier);
		$this->assertFileExists($identifier . '.js');

		$identifiermanager = $this->getManager();
		$this->assertStringEndsWith('main', $identifiermanager->getTopLevelIdentifier($identifier));

		// Even if the passed identifier has an extension...
		$this->assertStringEndsWith('main', @$identifiermanager->getTopLevelIdentifier($identifier . '.js'));
	}

	public function testAddIdentifierReturnsTopLevelIdentifier() {
		$identifier = CJSD_TESTMODS_DIR . '/main';

		// Assert that the file actually has an extension
		$this->assertFileNotExists($identifier);
		$this->assertFileExists($identifier . '.js');

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier . '.js', $identifiermanager->addIdentifier($identifier));

		// Even if the passed identifier has an extension...
		$this->assertEquals($identifier . '.js', @$identifiermanager->getTopLevelIdentifier($identifier . '.js'));
	}

	public function testFileWithExactPathIsFound() {
		$identifier = CJSD_TESTMODS_DIR . '/main';
		$this->assertFileExists($identifier . '.js');

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
}
