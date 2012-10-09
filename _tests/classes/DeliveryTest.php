<?php
/**
 * Base class for PHPUnit test for cjsDelivery
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 */

require_once 'cjsDelivery.php';

abstract class DeliveryTest extends PHPUnit_Framework_TestCase {

	const EXP_DIR = '_tests/expected/';
	const MOD_DIR = '_tests/modules/';

	const JS_EXT = cjsDelivery::EXT_JS;

	protected static function getExpectedPath($name) {
		return self::EXP_DIR . $name . self::JS_EXT;
	}

	protected static function getModulePath($name) {
		return self::MOD_DIR . $name . self::JS_EXT;
	}
}