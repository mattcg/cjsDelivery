<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

interface nameManager {


	/**
	 * Get the 'resolved' name of a module that will actually be used in the JavaScript output
	 *
	 * @param string $canonicalname The canonical name of the module
	 * @return string The resolved name, including an incrementor in case of a collision
	 */
	public function getResolvedName($canonicalname);


	/**
	 * Get the canonical name of a module
	 *
	 * @param string $relname Non-canonical name of the module
	 * @return string The canonical module name
	 */
	public function getCanonicalName($relname);


	/**
	 * Add a module by name and path, which will automatically resolved
	 *
	 * @param string $name Name of the module to add
	 * @param string $relname Relative path to the module
	 * @return string The canonical module name
	 */
	public function addModule($name, $relname);

}