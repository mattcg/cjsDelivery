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
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 2
	 */
	public function testExceptionThrownIfTopLevelIdentifierIsUnknown() {
		$real = realpath(CJSD_TESTMODS_DIR . '/main.js');
		$this->assertTrue($real !== false);

		$identifiermanager = $this->getManager();
		$identifiermanager->getFlattenedIdentifier($real);
	}


	/**
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 1
	 */
	public function testExceptionThrownIfFileIsNonexistent() {
		$identifiermanager = $this->getManager();
		$identifiermanager->getTopLevelIdentifier('./nonexistent.js');
	}

	public function testFileWithExactPathIsFound() {
		$path = CJSD_TESTMODS_DIR . '/main';

		$real = realpath($path . '.js');
		$this->assertTrue($real !== false);

		$identifiermanager = $this->getManager();
		$this->assertEquals($real, $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testIndexJsFileIsFound() {
		$path = CJSD_TESTMODS_DIR . '/apple';

		$real = realpath($path . '/index.js');
		$this->assertTrue($real !== false);

		$identifiermanager = $this->getManager();
		$this->assertEquals($real, $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testFileWithSameNameAsContainingDirectoryIsFound() {
		$path = CJSD_TESTMODS_DIR . '/banana';

		$real = realpath($path . '/banana.js');
		$this->assertTrue($real !== false);

		$identifiermanager = $this->getManager();
		$this->assertEquals($real, $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testOnlyFileInDirectoryIsFound() {
		$path = CJSD_TESTMODS_DIR . '/strawberry';

		$real = realpath($path . '/main.js');
		$this->assertTrue($real !== false);

		$identifiermanager = $this->getManager();
		$this->assertEquals($real, $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testFileSpecifiedInPackageJsonIsFound() {
		$path = CJSD_TESTMODS_DIR . '/grapefruit';

		$real = realpath($path . '/lib/grapefruit.js');
		$this->assertTrue($real !== false);

		$identifiermanager = $this->getManager();
		$this->assertEquals($real, $identifiermanager->getTopLevelIdentifier($path));
	}

	public function testIndexJsFileIsChosenOverFileWithSameNameAsDir() {
		$path = CJSD_TESTMODS_DIR . '/quince';

		$real = realpath($path . '/index.js');
		$this->assertTrue($real !== false);

		$identifiermanager = $this->getManager();
		$this->assertEquals($real, $identifiermanager->getTopLevelIdentifier($path));
	}


	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testNoticeTriggeredIfIdentifierContainsExtension() {
		$identifiermanager = $this->getManager();
		$identifiermanager->getTopLevelIdentifier(CJSD_TESTMODS_DIR . '/main.js');
	}
}
