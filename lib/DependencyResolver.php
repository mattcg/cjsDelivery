<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once 'IdentifierManager.php';

interface DependencyResolver {


	/**
	 * @param IdentifierManager $identifiermanager
	 */
	public function __construct(IdentifierManager $identifiermanager);


	/**
	 * @returns IdentifierManager
	 */
	public function getIdentifierManager();


	/**
	 * Get the raw contents from a module
	 *
	 * @throws Exception If the module is unreadable
	 * @param string $toplevelidentifier The top level identifier of the module
	 * @return string Raw module code
	 */
	public function getModuleContents($toplevelidentifier);


	/**
	 * Check whether a module has been added.
	 *
	 * @param string $toplevelidentifier The top level identifier of the module
	 * @returns boolean
	 */
	public function hasModule($toplevelidentifier);


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $identifier Identifier for the module
	 * @returns string Unique (but not canonicalized) identifier for the module
	 */
	public function addModule($identifier);


	/**
	 * Look for require statements in the code of the module with the given identifier and add referenced modules. Allows dependencies in arbitary modules to be resolved without adding the module itself to the final output.
	 *
	 * @param string $toplevelidentifier The top level identifier of the module
	 * @return string The code with resolved dependencies
	 */
	public function resolveDependencies($toplevelidentifier);


	/**
	 * Get all the resolved dependencies up to the current point
	 *
	 * @returns Module[]
	 */
	public function getAllDependencies();

}