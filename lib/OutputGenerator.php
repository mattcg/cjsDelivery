<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once 'external/lib/Client.php';
require_once 'external/lib/Manager.php';

require_once 'Exception.php';
require_once 'OutputRenderer.php';
require_once 'processHooks.php';

class OutputGenerator implements \hookManager\Client {

	private $hookmanager = null;
	private $renderer    = null;

	public function __construct(OutputRenderer $renderer) {
		$this->renderer = $renderer;
	}

	public function setHookManager(\hookManager\Manager $hookmanager) {
		$this->hookmanager = $hookmanager;
	}

	public function getHookManager() {
		return $this->hookmanager;
	}


	/**
	 * Build complete module output, including all added modules and dependencies
	 *
	 * @throws Exception If the module is not found
	 *
	 * @param Module[] $modules List of modules from which to build output
	 * @param string $main Identifier of the main module
	 * @param string $globals Raw JavaScript included just outside module scope
	 * @return string Complete output
	 */
	public function buildOutput(array $modules, $main = '', &$globals = '') {
		if (empty($modules)) {
			throw new Exception('Nothing to build', Exception::NOTHING_TO_BUILD);
		}

		// If output is created by the hook callbacks, return it
		$output = '';
		if ($this->hookmanager) {
			$this->hookmanager->run(processHooks\BUILD_OUTPUT, $output);
			if ($output) {
				return $output;
			}
		}

		// Loop through the modules, render and look for the main module
		$concat = '';
		foreach ($modules as $realpath => &$module) {
			$concat .= $this->renderer->renderModule($module);
		}

		$output = $this->renderer->renderOutput($concat, $main, $globals);

		// Run hooks with the fully built output
		if ($this->hookmanager) {
			$this->hookmanager->run(processHooks\OUTPUT_READY, $output);
		}

		return $output;
	}
}