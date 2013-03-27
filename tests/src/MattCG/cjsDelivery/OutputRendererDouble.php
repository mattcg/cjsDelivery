<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2013, Matthew Caruana Galizia
 */

use MattCG\cjsDelivery\Module;

class OutputRendererDouble implements MattCG\cjsDelivery\OutputRendererInterface {

	public $modules = array(), $output = null;

	public function renderModule(Module &$module) {
		$this->modules[] = $module;
		return $module->getCode();
	}

	public function renderOutput(&$output, Module &$mainmodule = null, &$globalscode = null, $exportrequire = null) {
		$output = array($output, $mainmodule->getUniqueIdentifier(), $globalscode);
		$this->output = $output;
		return $output;
	}
}
