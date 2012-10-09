<?php
/**
 * ErrorTest - PHPUnit test for cjsDelivery
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 */

require_once __DIR__ . '/DeliveryTest.php';

class ErrorTest extends DeliveryTest {


	/**
	 * Assert that an error is thrown when building output with no main module found
	 *
	 * @expectedException LogicException
	 */
	public function testNoMainModule() {
		$module = 'some-module';

		$delivery = new cjsDelivery();
		$delivery->addModule($module, self::getModulePath($module));

		$delivery->buildOutput();
	}


	/**
	 * Assert that an error is thrown when attempting to add a nonexistent module
	 *
	 * @expectedException RuntimeException
	 */
	public function testModuleNotFound() {
		$module = 'nonexistent' . rand();

		$delivery = new cjsDelivery();
		$delivery->addModule($module, $module . self::JS_EXT);
	}
}