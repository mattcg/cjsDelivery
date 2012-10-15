<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once __DIR__.'/identifierManager.php';

class fileIdentifierManager implements identifierManager {
	private $modules = array();


	/**
	 * Check for modules with the same base name
	 *
	 * @param string $basename The base name of a module identifier to check for duplicates of
	 * @return integer Number of modules with the same base name
	 */
	private function checkSameBaseName($basename) {
		$i = 0;
		foreach ($this->modules as &$module) {
			if ($basename === $module['basename']) {
				$i++;
			}
		}

		return $i;
	}


	/**
	 * @see identifierManager::getFlattenedIdentifier()
	 * @param string $realpath The canonicalized absolute pathname of the module
	 */
	public function getFlattenedIdentifier($realpath) {
		if (!isset($this->modules[$realpath])) {
			throw new cjsDeliveryException("Unknown module '$realpath'", cjsDeliveryException::UNKNOWN_MODULE);
		}

		$module = $this->modules[$realpath];
		if ($module['basenamecount'] > 0) {
			return $module['basename'] . $module['basenamecount'];
		}

		return $module['basename'];
	}


	/**
	 * @see identifierManager::getTopLevelIdentifier()
	 * @param string $filepath Path to the module file
	 * @return string The canonicalized absolute pathname of the module
	 */
	public function getTopLevelIdentifier($filepath) {
		$realpath = realpath($filepath);

		// Check if the path was resolved
		if ($realpath === false) {
			throw new cjsDeliveryException("Module not found at '$filepath'", cjsDeliveryException::MODULE_NOT_FOUND);
		}

		return $realpath;
	}


	/**
	 * @see identifierManager::addIdentifier()
	 * @param string $filepath Path to the module file
	 * @return string The canonicalized absolute pathname of the module
	 */
	public function addIdentifier($filepath) {
		$realpath = $this->getTopLevelIdentifier($filepath);
		if (!isset($this->modules[$realpath])) {
			$basename = basename($filepath, '.' . pathinfo($filepath, PATHINFO_EXTENSION));
	 		$this->modules[$realpath] = array(
	 			'basename' => $basename,
	 			'basenamecount' => $this->checkSameBaseName($basename)
	 		);
	 	}

		return $realpath;
	}
}