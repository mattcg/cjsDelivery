<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once 'IdentifierGenerator.php';

interface IdentifierManager {

	public function __construct(IdentifierGenerator $identifiergenerator);
	public function setIdentifierGenerator(IdentifierGenerator $identifiergenerator);
	public function getIdentifierGenerator();

	/**
	 * Set the list of location stubs to use when searching for module files.
	 *
	 * @param array $includes
	 */
	public function setIncludes(array $includes = null);


	/**
	 * Get the 'resolved' identifier of a module that will actually be used in the JavaScript output
	 *
	 * @param string $toplevelidentifier The top level identifier of the module
	 * @return string|boolean The flattened identifier, including an incrementor in case of a collision
	 */
	public function getFlattenedIdentifier($toplevelidentifier);


	/**
	 * Get the top level identifier for the given relative identifier
	 *
	 * @param string $relativeidentifier Relative identifier for the module
	 * @return string|boolean The top level identifier for the module or false on failure
	 */
	public function getTopLevelIdentifier($relativeidentifier);


	/**
	 * Add a module by identifier
	 *
	 * @param string $identifier Relative or top level identifier for the module
	 * @return string The top level identifier for the module
	 */
	public function addIdentifier($identifier);

}
