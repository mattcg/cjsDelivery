<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class outputGenerator implements \hookManager\client {

	private $hookmanager = null;
	private $renderer    = null;

	public function __construct(outputRenderer $renderer) {
		$this->renderer = $renderer;
	}

	public function setHookManager(\hookManager\manager $hookmanager) {
		$this->hookmanager = $hookmanager;
	}

	public function getHookManager() {
		return $this->hookmanager;
	}


	/**
	 * Build complete module output, including all added modules and dependencies
	 *
	 * @throws cjsDeliveryException If the module is not found
	 *
	 * @param module[] $modules List of modules from which to build output
	 * @param string $main Name of the main module
	 * @return string Complete output
	 */
	public function buildOutput(array $modules, $main) {
		$output = '';

		// If output is created by the hook callbacks, return it
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

		$output = $this->renderer->renderOutput($concat, $main);

		// Run hooks with the fully built output
		if ($this->hookmanager) {
			$this->hookmanager->run(processHooks\OUTPUT_READY, $output);
		}

		return $output;
	}
}