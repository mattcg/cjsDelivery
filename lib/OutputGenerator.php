<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once 'Exception.php';
require_once 'OutputRenderer.php';
require_once 'processHooks.php';

class OutputGenerator {

	private $renderer = null;

	protected $signal = null;

	public function __construct(OutputRenderer $renderer) {
		$this->renderer = $renderer;
	}

	public function setSignalManager(\Aura\Signal\Manager $signal) {
		$this->signal = $signal;
	}

	public function getSignalManager() {
		return $this->signal;
	}


	/**
	 * Build complete module output, including all added modules and dependencies
	 *
	 * @throws Exception If the module is not found
	 *
	 * @param Module[] $modules List of modules from which to build output
	 * @param string $main Identifier of the main module
	 * @param string $globals Raw JavaScript included just outside module scope
	 * @param string $exportrequire Name of variable to export the require function as
	 * @return string Complete output
	 */
	public function buildOutput(array $modules, $main = '', &$globals = '', $exportrequire = '') {
		if (empty($modules)) {
			throw new Exception('Nothing to build', Exception::NOTHING_TO_BUILD);
		}

		// If output is created by the hook callbacks, return it
		$output = '';
		if ($this->signal) {
			$result = $this->signal->send($this, processHooks\BUILD_OUTPUT, $output)->getLast();
			if ($result and $result->value) {
				return $result->value;
			}
		}

		// Loop through the modules, render and look for the main module
		$concat = '';
		foreach ($modules as $realpath => &$module) {
			$concat .= $this->renderer->renderModule($module);
		}

		$output = $this->renderer->renderOutput($concat, $main, $globals, $exportrequire);

		// Run hooks with the fully built output
		if ($this->signal) {
			$this->signal->send($this, processHooks\OUTPUT_READY, $output);
		}

		return $output;
	}
}