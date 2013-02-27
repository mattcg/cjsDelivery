<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

// Class under test
require_once CJSD_LIB_DIR . '/OutputGenerator.php';

// Test dependencies
require_once CJSD_LIB_DIR . '/Module.php';

class OutputRendererDouble implements cjsDelivery\OutputRenderer {

	public $modules = array(), $output = null;

	public function renderModule(&$module) {
		$this->modules[] = $module;
		return $module->getCode();
	}

	public function renderOutput(&$output, $main = '', &$globals = '', $exportrequire = '') {
		$output = array($output, $main, $globals);
		$this->output = $output;
		return $output;
	}
}

class OutputGeneratorTest extends PHPUnit_Framework_TestCase {

	public function testOutputIsBuilt() {
		$renderer = new OutputRendererDouble();
		$generator = new cjsDelivery\OutputGenerator($renderer);

		$main = 'main';
		$globals = 'globals';

		$moduleAcode = 'alert("A");';
		$moduleA = new cjsDelivery\Module($moduleAcode);

		$moduleBcode = 'alert("A");';
		$moduleB = new cjsDelivery\Module($moduleBcode);

		$moduleCcode = 'alert("A");';
		$moduleC = new cjsDelivery\Module($moduleCcode);

		$generator->buildOutput(array($moduleA, $moduleB, $moduleC), $main, $globals);

		$this->assertEquals($moduleA, $renderer->modules[0]);
		$this->assertEquals($moduleB, $renderer->modules[1]);
		$this->assertEquals($moduleC, $renderer->modules[2]);

		$this->assertEquals(array($moduleAcode.$moduleBcode.$moduleCcode, $main, $globals), $renderer->output);
	}
}
