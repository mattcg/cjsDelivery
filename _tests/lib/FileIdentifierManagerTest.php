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
	public function testNoticeTriggeredIfIdentifierContainsExtension() {
		$identifiermanager = $this->getManager();
		$identifiermanager->getTopLevelIdentifier(CJSD_TESTMODS_DIR . '/main.js');
	}


	/**
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 2
	 */
	public function testExceptionThrownIfTopLevelIdentifierIsUnknown() {
		$identifiermanager = $this->getManager();
		$identifiermanager->getFlattenedIdentifier(CJSD_TESTMODS_DIR . '/main');
	}


	/**
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 1
	 */
	public function testExceptionThrownIfFileIsNonexistent() {
		$identifiermanager = $this->getManager();
		$identifiermanager->getTopLevelIdentifier('./nonexistent');
	}

	public function testGetTopLevelIdentifierDoesNotReturnPathWithExtension() {
		$path = CJSD_TESTMODS_DIR . '/main';

		// Assert that the file actually has an extension
		$this->assertFalse(is_file($path));
		$this->assertTrue(is_file($path . '.js'));

		$identifiermanager = $this->getManager();
		$this->assertStringEndsWith('main', $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testFileWithExactPathIsFound() {
		$path = CJSD_TESTMODS_DIR . '/main';

		$identifiermanager = $this->getManager();
		$this->assertEquals($path, $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testIndexJsFileIsFound() {
		$path = CJSD_TESTMODS_DIR . '/apple';

		$identifiermanager = $this->getManager();
		$this->assertEquals($path . '/index', $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testFileWithSameNameAsContainingDirectoryIsFound() {
		$path = CJSD_TESTMODS_DIR . '/banana';

		$identifiermanager = $this->getManager();
		$this->assertEquals($path . '/banana', $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testOnlyFileInDirectoryIsFound() {
		$path = CJSD_TESTMODS_DIR . '/strawberry';

		$identifiermanager = $this->getManager();
		$this->assertEquals($path . '/main', $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testFileSpecifiedInPackageJsonIsFound() {
		$path = CJSD_TESTMODS_DIR . '/grapefruit';

		$identifiermanager = $this->getManager();
		$this->assertEquals($path . '/lib/grapefruit', $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testIndexJsFileIsChosenOverFileWithSameNameAsDir() {
		$path = CJSD_TESTMODS_DIR . '/quince';

		$identifiermanager = $this->getManager();
		$this->assertEquals($path . '/index', $identifiermanager->getTopLevelIdentifier($path));
	}
}
