<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class outputGenerator implements hookManager\client {

	const DEFAULT_MAIN = 'app';

	private $hookmanager = null;
	private $renderer = null;

	private $mainmodule;

	public function __construct(outputRender $renderer) {
		$this->renderer = $renderer;
	}

	public function setHookManager(hookManager\manager $hookmanager) {
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
	 * @param array $modules List of modules from which to build output
	 * @return string Complete output
	 */
	public function buildOutput(array $modules) {
		$output = '';

		// If output is created by the hook callbacks, return it
		$this->hookmanager->run(processHooks\BUILD_OUTPUT, $output);
		if ($output) {
			return $output;
		}

		// Get the name of the main module
		if ($this->mainmodule) {
			$main = $this->mainmodule;
		} else {
			$main = self::DEFAULT_MAIN;
		}

		$concat  = '';
		$hasmain = false;

		// Loop through the modules, render and look for the main module
		foreach ($modules as $realpath => &$module) {
			$concat .= $this->renderer->renderModule($module['name'], $module['code']);
			if (!$hasmain and $main === $module['name']) {
				$hasmain = true;
			}
		}

		// Output can't be built without a main module
		if (!$hasmain) {
			throw new cjsDeliveryException("Main module '$main' not found in module list", cjsDeliveryException::NO_MAIN);
		}

		$output = $this->renderer->renderOutput($concat, $main);

		// Run hooks with the fully built output
		$this->hooks->run(processHooks\OUTPUT_READY, $output);
		return $output;
	}


	/**
	 * Set the name of the main module
	 *
	 * Each module is wrapped in a function which isn't executed until the module is required, so
	 * a 'main' module needs to be 'required' automatically to kick off execution on the client.
	 *
	 * @param string $name The name of the main module
	 */
	public function setMainModule($name) {
		$this->mainmodule = $name;
	}


	/**
	 * Get the name of the main module
	 *
	 * @return string The name of the main module
	 */
	public function getMainModule() {
		return $this->mainmodule;
	}
}