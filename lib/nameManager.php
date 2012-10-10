<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class nameManager {
	private $modules = array();


	/**
	 * Check for modules with the same name
	 *
	 * @param string $name The name to for duplicates of
	 * @return integer Number of modules with the same name
	 */
	private function checkSameName($name) {
		$i = 0;

		foreach ($this->modules as &$module) {
			if ($name === $module['name']) {
				$i++;
			}
		}

		return $i;
	}


	/**
	 * Get the 'resolved' name of a module that will actually be used in the JavaScript output
	 *
	 * @param string $realpath The resolved path to the module file
	 * @return string The resolved name, including an incrementor in case of a collision
	 */
	public function getResolvedName($realpath) {
		if (!isset($this->modules[$realpath])) {
			throw new cjsDeliveryException("Unknown module '$realpath'", cjsDeliveryException::UNKNOWN_MODULE);
		}

		$module = $this->modules[$realpath];
		if ($module['i'] > 0) {
			return $module['name'] . $module['i'];
		}

		return $module['name'];
	}


	/**
	 * @param string $name Name of the module to add
	 * @param string $filepath Path to the module file
	 * @return string The resolved module path
	 */
	public function addModule($name, $filepath) {
		$realpath = @realpath($filepath);

		// Check if the path was resolved
		if ($realpath === false) {
			throw new cjsDeliveryException("Module '$name' not found", cjsDeliveryException::MODULE_NOT_FOUND);
		}

		$this->modules[$realpath] = array('name' => $name, 'i' => $this->checkSameName($name));
		return $realpath;
	}
}