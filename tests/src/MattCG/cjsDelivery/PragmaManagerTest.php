<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

use MattCG\cjsDelivery\PragmaManager;
use MattCG\cjsDelivery\SignalSender;
use MattCG\cjsDelivery\FileIdentifierManager;
use MattCG\cjsDelivery\FileDependencyResolver;
use MattCG\cjsDelivery\FlatIdentifierGenerator;

class PragmaManagerTest extends PHPUnit_Framework_TestCase {

	private function getSignalManagerInstance() {
		return require __DIR__ . '/../../../../vendor/aura/signal/scripts/instance.php';
	}

	private function getDependencyResolverInstance() {
		$identifiermanager = new FileIdentifierManager(new FlatIdentifierGenerator());
		return new FileDependencyResolver($identifiermanager);
	}

	private function getPragmaManagerInstance() {
		$signalmanager = $this->getSignalManagerInstance();
		$dependencyresolver = $this->getDependencyResolverInstance();
		return new PragmaManager($signalmanager, $dependencyresolver);
	}

	public function testSetPragma() {
		$pragma = 'DEBUG';

		$pragmamanager = $this->getPragmaManagerInstance();
		$pragmamanager->setPragma($pragma);
		$this->assertTrue($pragmamanager->checkPragma($pragma));
	}

	public function testSetPragmas() {
		$pragmas = array('DEBUG', 'TEST');

		$pragmamanager = $this->getPragmaManagerInstance();
		$pragmamanager->setPragmas($pragmas);
		$this->assertEquals($pragmamanager->getPragmas(), $pragmas);
	}

	public function testUnsetPragma() {
		$pragma = 'DEBUG';

		$pragmamanager = $this->getPragmaManagerInstance();
		$pragmamanager->setPragma($pragma);
		$pragmamanager->unsetPragma($pragma);
		$this->assertFalse($pragmamanager->checkPragma($pragma));
	}

	public function testHandlerIsAttachedDuringConstruction() {
		$signalmanager = $this->getSignalManagerInstance();
		$dependencyresolver = $this->getDependencyResolverInstance();
		$pragmamanager = new PragmaManager($signalmanager, $dependencyresolver);

		$handlers = $signalmanager->getHandlers(SignalSender::PROCESS_MODULE);
		$this->assertNotEmpty($handlers);
	}

	public function testSetPragmasAreIncluded() {
		$pragmamanager = $this->getPragmaManagerInstance();
		$pragmamanager->setPragma('DEBUG');

		$code = '// ifdef DEBUG' . PHP_EOL . 'console.log("debug");' . PHP_EOL . '// endif DEBUG';

		$processedcode = $pragmamanager->processPragmas($code);
		$this->assertEquals($processedcode, $code);
	}

	public function testUnsetPragmasAreExcluded() {
		$pragmamanager = $this->getPragmaManagerInstance();

		$pre = 'console.log("app");' . PHP_EOL;
		$code = $pre . '// ifdef DEBUG' . PHP_EOL . 'console.log("debug");' . PHP_EOL . '// endif DEBUG';

		$processedcode = $pragmamanager->processPragmas($code);
		$this->assertEquals($processedcode, $pre);
	}

	public function testPragmasAreProcessedOnSignal() {
		$signalmanager = $this->getSignalManagerInstance();
		$dependencyresolver = $this->getDependencyResolverInstance();
		$pragmamanager = new PragmaManager($signalmanager, $dependencyresolver);

		$pre = 'console.log("app");' . PHP_EOL;
		$code = $pre . '// ifdef DEBUG' . PHP_EOL . 'console.log("debug");' . PHP_EOL . '// endif DEBUG';

		$signalmanager->send($dependencyresolver, SignalSender::PROCESS_MODULE, $code);
		$results = $signalmanager->getResults();
		$this->assertNotEmpty($results);
		$last = $results->getLast();
		$this->assertNotEmpty($last);
		$this->assertEquals($last->value, $pre);
	}
}
