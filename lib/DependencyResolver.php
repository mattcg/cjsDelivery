<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

interface DependencyResolver {


	/**
	 * @param identifierManager $identifiermanager
	 */
	public function __construct(identifierManager $identifiermanager);


	/**
	 * @returns identifierManager
	 */
	public function getIdentifierManager();


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $identifier Identifier for the module file
	 */
	public function addModule($identifier);


	/**
	 * Get all the resolved dependencies up to the current point
	 *
	 * @returns module[]
	 */
	public function getAllDependencies();

}