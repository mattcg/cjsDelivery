<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once __DIR__.'/identifierGenerator.php';

class flatIdentifierGenerator implements identifierGenerator {
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
	 * @see identifierGenerator::generateFlattenedIdentifier
	 */
	public function generateFlattenedIdentifier($toplevelidentifier)  {
		if (!isset($this->modules[$toplevelidentifier])) {
			$basename = basename($toplevelidentifier, '.' . pathinfo($toplevelidentifier, PATHINFO_EXTENSION));
	 		$this->modules[$toplevelidentifier] = array(
	 			'basename' => $basename,
	 			'basenamecount' => $this->checkSameBaseName($basename)
	 		);
		}

		$module = $this->modules[$toplevelidentifier];
		if ($module['basenamecount'] > 0) {
			return $module['basename'] . $module['basenamecount'];
		}

		return $module['basename'];
	}
}