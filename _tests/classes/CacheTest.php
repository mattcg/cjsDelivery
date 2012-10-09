<?php
/**
 * CacheTest - PHPUnit test for cjsDelivery
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 */

require_once __DIR__ . '/DeliveryTest.php';

class CacheTest extends DeliveryTest {

	const CACHE_DIR = 'build/cache/';


	/**
	 * Clear the cache directory
	 */
	public static function clearCacheDir() {
		if (!is_dir(self::CACHE_DIR)) {
			return;
		}

		$files = glob(self::CACHE_DIR . '*' . self::JS_EXT, GLOB_MARK);
		foreach ($files as $file) {
			if (substr($file, -1) !== '/') {
				unlink($file);
			}
		}
	}


	/**
	 * Assert that the cache path can be set correctly
	 */
	public function testSetCachingPath() {
		$path = self::CACHE_DIR . $this->getName(false) . self::JS_EXT;

		$delivery = new cjsDelivery();

		$manager = new cacheManager($delivery);
		$manager->setCachePath($path);

		$this->assertEquals($path, $manager->getCachePath());
	}


	/**
	 * Assert that output is cached
	 */
	public function testSetCachingOutput() {
		self::clearCacheDir();

		$path = self::CACHE_DIR . $this->getName(false) . self::JS_EXT;
		$module = 'app';

		$delivery = new cjsDelivery();
		$delivery->addModule($module, self::getModulePath($module));

		$manager = new cacheManager($delivery);
		$manager->setCachePath($path);

		$output = $delivery->buildOutput();

		$this->assertStringEqualsFile($path, $output);
	}


	/**
	 * Assert that output is returned from the cache file when available
	 */
	public function testReturnsFromCache() {
		self::clearCacheDir();

		$path = self::CACHE_DIR . $this->getName(false) . self::JS_EXT;
		$module = 'app';

		$delivery = new cjsDelivery();
		$delivery->addModule($module, self::getModulePath($module));

		$manager = new cacheManager($delivery);
		$manager->setCachePath($path);

		$delivery->buildOutput();
		unset($delivery);
		unset($manager);

		$this->assertFileExists($path);

		$replace = 'R';
		file_put_contents($path, $replace);

		$this->assertStringEqualsFile($path, $replace);

		$delivery = new cjsDelivery();
		$delivery->addModule($module, self::getModulePath($module));

		$manager = new cacheManager($delivery);
		$manager->setCachePath($path);

		$output = $delivery->buildOutput();

		$this->assertEquals($replace, $output);
	}


	/**
	 * Assert that the cache is refresh when there are changes to module files
	 */
	public function testRefreshesCache() {
		clearstatcache();

		$module = 'app';
		$modulepath = self::getModulePath($module);
		$cachepath = self::CACHE_DIR . $this->getName(false) . self::JS_EXT;

		$delivery = new cjsDelivery();
		$delivery->addModule($module, $modulepath);

		$manager = new cacheManager($delivery);
		$manager->setCachePath($cachepath);

		$delivery->buildOutput();

		unset($delivery);

		$cachemtime = filemtime($cachepath);
		$modulemtime = filemtime($modulepath);

		$this->assertNotEquals($cachemtime, $modulemtime);

		sleep(1);

		$now = time();
		touch($modulepath, $now);

		sleep(1);

		clearstatcache();

		$this->assertEquals($now, filemtime($modulepath));
		$this->assertEquals($cachemtime, filemtime($cachepath));

		$delivery = new cjsDelivery();
		$delivery->addModule($module, $modulepath);

		$manager = new cacheManager($delivery);
		$manager->setCachePath($cachepath);

		$output = $delivery->buildOutput();

		clearstatcache();

		$this->assertNotEquals($cachemtime, filemtime($cachepath));
		$this->assertStringEqualsFile($cachepath, $output);

		// Reset the module modification time
		touch($modulepath, $modulemtime);
	}
}