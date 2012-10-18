<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

interface identifierManager {

	public function __construct(identifierGenerator $identifiergenerator);
	public function setIdentifierGenerator(identifierGenerator $identifiergenerator);
	public function getIdentifierGenerator();


	/**
	 * Get the 'resolved' identifier of a module that will actually be used in the JavaScript output
	 *
	 * @param string $toplevelidentifier The top level identifier of the module
	 * @return string The flattened identifier, including an incrementor in case of a collision
	 */
	public function getFlattenedIdentifier($toplevelidentifier);


	/**
	 * Get the top level identifier for the given relative identifier
	 *
	 * @param string $relativeidentifier Relative identifier for the module
	 * @return string The top level identifier for the module
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