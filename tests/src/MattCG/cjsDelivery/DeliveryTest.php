<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

use MattCG\cjsDelivery\Delivery;

class DeliveryTest extends PHPUnit_Framework_TestCase {

	public function testCreate() {
		$delivery = Delivery::create();
		$this->assertInstanceOf('MattCG\cjsDelivery\Delivery', $delivery);
	}

	public function testGetLastModTime() {
		$delivery = Delivery::create();
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/pear/index';
		$delivery->addModule($toplevelidentifier);

		$maxmtime = max(filemtime($toplevelidentifier . '.js'), filemtime(CJSD_TESTMODS_DIR . '/pear/pips.js'), filemtime(CJSD_TESTMODS_DIR . '/pear/stalk.js'));
		$this->assertInternalType('integer', $maxmtime);
		$this->assertEquals($maxmtime, $delivery->getLastModTime());
	}

	public function testGetLastModTimeWorksWithManuallyAddedCode() {
		$delivery = Delivery::create();
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/apple/index';
		$code = '// Bogus';
		$delivery->addModule($toplevelidentifier, $code);
		$mtime = filemtime($toplevelidentifier . '.js');
		$this->assertInternalType('integer', $mtime);
		$this->assertEquals($mtime, $delivery->getLastModTime());
	}

	public function testGetOutput() {
		$delivery = Delivery::create();
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/apple/index';
		$delivery->addModule($toplevelidentifier);
		$code = trim(file_get_contents($toplevelidentifier . '.js'));
		$this->assertTrue(strpos($delivery->getOutput(), $code) !== false);
	}

	public function testGetOutputCanExportRequire() {
		$delivery = Delivery::create();
		$toplevelidentifier = CJSD_TESTMODS_DIR . '/apple/index';
		$delivery->addModule($toplevelidentifier);
		$exportrequire = 'justaverylongname';
		$this->assertTrue(strpos($delivery->getOutput($exportrequire), $exportrequire) !== false);
	}
}
