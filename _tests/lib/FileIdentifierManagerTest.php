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


	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 */
	public function testNoticeTriggeredIfIdentifierContainsExtension() {
		$identifiermanager = $this->getManager();
		$identifiermanager->getTopLevelIdentifier(CJSD_TESTMODS_DIR . '/main.js');
	}
}
