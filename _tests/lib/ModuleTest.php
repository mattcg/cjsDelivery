<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

// Class under test
require_once CJSD_LIB_DIR . '/Module.php';

class ModuleTest extends PHPUnit_Framework_TestCase {

	public function testCodeCanBePassedToConstructor() {
		$code = 'alert()';
		$module = new cjsDelivery\Module($code);
		$this->assertEquals($code, $module->getCode());
	}

	public function testCanSetUniqueIdentifier() {
		$id = 1;
		$code = '';
		$module = new cjsDelivery\Module($code);
		$module->setUniqueIdentifier($id);
		$this->assertEquals($id, $module->getUniqueIdentifier());
	}

	public function testCanSetModificationTime() {
		$time = time();
		$code = '';
		$module = new cjsDelivery\Module($code);
		$module->setModificationTime($time);
		$this->assertEquals($time, $module->getModificationTime());
	}


	/**
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 6
	 */
	public function testSettingStringModificationTimeThrowsException() {
		$code = '';
		$module = new cjsDelivery\Module($code);
		$module->setModificationTime('bad');
	}


	/**
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 6
	 */
	public function testSettingBooleanModificationTimeThrowsException() {
		$code = '';
		$module = new cjsDelivery\Module($code);
		$module->setModificationTime(false);
	}


	/**
	 * @expectedException cjsDelivery\Exception
	 * @expectedExceptionCode 6
	 */
	public function testSettingNullModificationTimeThrowsException() {
		$code = '';
		$module = new cjsDelivery\Module($code);
		$module->setModificationTime(null);
	}
}
