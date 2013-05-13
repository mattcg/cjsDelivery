<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

class FileIdentifierManagerTest extends PHPUnit_Framework_TestCase {

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
	 * @expectedExceptionMessage Module identifiers may not have file-name extensions like ".js" (found "main.js").
	 */
	public function testNoticeTriggeredByGetTopLevelIdentifierIfIdentifierContainsJsExtension() {
		$identifiermanager = $this->getManager();
		$identifier = CJSD_TESTMODS_DIR . '/main.js';

		// Assert that the file exists and is readable
		$this->assertFileExists($identifier);
		$this->assertTrue(is_readable($identifier));
		$identifiermanager->getTopLevelIdentifier($identifier);
	}


	/**
	 * @expectedException PHPUnit_Framework_Error_Notice
	 * @expectedExceptionMessage Module identifiers may not have file-name extensions like ".json" (found "data.json").
	 */
	public function testNoticeTriggeredByGetTopLevelIdentifierIfIdentifierContainsJsonExtension() {
		$identifiermanager = $this->getManager();
		$identifier = CJSD_TESTMODS_DIR . '/data.json';

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
		$this->assertFileExists($identifier . '.js');
		$this->assertTrue(is_readable($identifier . '.js'));
		$identifiermanager->getFlattenedIdentifier($identifier);
	}


	/**
	 * @expectedException MattCG\cjsDelivery\Exception
	 * @expectedExceptionCode 1
	 */
	public function testExceptionThrownIfFileIsNonexistent() {
		$identifiermanager = $this->getManager();
		$identifier = './nonexistent';

		$this->assertFileNotExists($identifier . '.js');
		$identifiermanager->getTopLevelIdentifier($identifier);
	}

	public function testGetTopLevelIdentifierReturnsTopLevelIdentifier() {
		$identifier = CJSD_TESTMODS_DIR . '/main';
		$this->assertFileExists($identifier . '.js');

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier, $identifiermanager->getTopLevelIdentifier($identifier));
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
		$this->assertEquals($identifier, $identifiermanager->addIdentifier($identifier));

		// Even if the passed identifier has an extension...
		$this->assertEquals($identifier, @$identifiermanager->getTopLevelIdentifier($identifier . '.js'));
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
		$this->assertFileExists($identifier . '.js');
		$this->getManager()->addIdentifier($identifier);
	}

	public function testIncludesAreFound() {
		$identifier = 'main';
		$this->assertFileExists('modules/' . $identifier . '.js');

		$identifiermanager = $this->getManager();
		$identifiermanager->setIncludes(array(CJSD_TESTMODS_DIR));
		$this->assertEquals(CJSD_TESTMODS_DIR . '/' . $identifier, $identifiermanager->addIdentifier($identifier));
	}

	public function testAddIdentifierHandlesJSON() {
		$identifier = CJSD_TESTMODS_DIR . '/data';
		$this->assertFileExists($identifier . '.json');

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier, $identifiermanager->addIdentifier($identifier));
	}

	public function testIsJson() {
		$identifier = CJSD_TESTMODS_DIR . '/data';
		$this->assertFileExists($identifier . '.json');

		// Test with relative identifier
		$identifiermanager = $this->getManager();
		$this->assertTrue($identifiermanager->isJson($identifier));

		// Test with top level identifier
		$identifiermanager = $this->getManager();
		$this->assertTrue($identifiermanager->isJson($identifiermanager->addIdentifier($identifier)));
	}

	public function testJsExtensionIsPreferredOverJson() {
		$identifier = CJSD_TESTMODS_DIR . '/strawberry/main';
		$this->assertFileExists($identifier . '.js');
		$this->assertFileExists($identifier . '.json');

		$identifiermanager = $this->getManager();
		$this->assertEquals($identifier, $identifiermanager->addIdentifier($identifier));
		$this->assertTrue(!$identifiermanager->isJson($identifiermanager->getTopLevelIdentifier($identifier)));
	}
}
