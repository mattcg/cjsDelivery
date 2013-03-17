<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

interface IdentifierGeneratorInterface {


	/**
	 * Generate a flattened top level identifier
	 *
	 * This method must be idempotent and multiple calls must return the same value.
	 *
	 * @param string $toplevelidentifier The top level identifier to minify
	 * @returns string The minified top level identifier
	 */
	public function generateFlattenedIdentifier($toplevelidentifier);

}