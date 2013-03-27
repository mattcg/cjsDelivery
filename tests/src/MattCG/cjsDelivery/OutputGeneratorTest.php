<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

use MattCG\cjsDelivery\Module;

require_once 'OutputRendererDouble.php';

class OutputGeneratorTest extends PHPUnit_Framework_TestCase {

	public function testOutputIsBuilt() {
		$renderer = new OutputRendererDouble();
		$generator = new MattCG\cjsDelivery\OutputGenerator($renderer);

		$globals = 'globals';

		$moduleAcode = 'alert("A");';
		$moduleA = new Module($moduleAcode);
		$moduleA->setUniqueIdentifier('a');

		$moduleBcode = 'alert("B");';
		$moduleB = new Module($moduleBcode);
		$moduleB->setUniqueIdentifier('b');

		$moduleCcode = 'alert("C");';
		$moduleC = new Module($moduleCcode);
		$moduleC->setUniqueIdentifier('c');

		$main = $moduleA;

		$generator->setModules(array($moduleA, $moduleB, $moduleC));
		$generator->setMainModule($main);
		$generator->setGlobalsCode($globals);
		$generator->buildOutput();

		$this->assertEquals($moduleA, $renderer->modules[0]);
		$this->assertEquals($moduleB, $renderer->modules[1]);
		$this->assertEquals($moduleC, $renderer->modules[2]);

		$this->assertEquals(array($moduleAcode.$moduleBcode.$moduleCcode, 'a', $globals), $renderer->output);
	}
}
