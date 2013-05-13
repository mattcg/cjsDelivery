<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

interface IdentifierManagerInterface {

	public function __construct(IdentifierGeneratorInterface $identifiergenerator);
	public function setIdentifierGenerator(IdentifierGeneratorInterface $identifiergenerator);
	public function getIdentifierGenerator();

	/**
	 * Set the list of location stubs to use when searching for module files.
	 *
	 * @param array $includes
	 */
	public function setIncludes(array $includes = null);


	/**
	 * Get the list of location stubs to use when searching for module files.
	 *
	 * @return array
	 */
	public function getIncludes();


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
	 * Get the system path to the module file
	 *
	 * @param string $relativeidentifier Relative identifier for the module
	 * @return string The system path to the module
	 */
	public function getRealpath($identifier);


	/**
	 * Add a module by identifier
	 *
	 * @param string $identifier Relative or top level identifier for the module
	 * @return string The top level identifier for the module
	 */
	public function addIdentifier($identifier);


	/**
	 * Check whether the given identifier refers to a JSON file or not
	 *
	 * @param string $identifier Relative or top level identifier for the module
	 * @return boolean
	 */
	public function isJson($identifier);

}
