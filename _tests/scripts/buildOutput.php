<?php
/**
 * Generate module output files for testing with JsTestDriver
 */

require 'cjsDelivery.php';

$basedir = dirname(__DIR__) . '/modules';

$testfiles = array(
	'require-returns-module',
	//'pathnames-are-parsed',
	//'module-id-is-set'
);

foreach ($testfiles as $testfile) {
	$delivery = new cjsDelivery();
	$delivery->addModule($testfile, $basedir . '/' . $testfile . '.js');
	$delivery->setMainModule($testfile);

	file_put_contents('build/output/' . $testfile . '.js', $delivery->buildOutput(), LOCK_EX);
}