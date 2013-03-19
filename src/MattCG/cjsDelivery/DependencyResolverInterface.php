<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

interface DependencyResolverInterface extends SignalSenderInterface {


	/**
	 * @param IdentifierManagerInterface $identifiermanager
	 */
	public function __construct(IdentifierManagerInterface $identifiermanager);


	/**
	 * @returns IdentifierManagerInterface
	 */
	public function getIdentifierManager();


	/**
	 * Check whether a module has been added.
	 *
	 * @param string $toplevelidentifier The top level identifier of the module
	 * @returns boolean
	 */
	public function hasModule($toplevelidentifier);


	/**
	 * Get a module that has been added.
	 *
	 * @throws Exception If the module is not found
	 *
	 * @param string $toplevelidentifier The top level identifier of the module
	 * @returns Module
	 */
	public function getModule($toplevelidentifier);


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies.
	 *
	 * @param string $identifier Identifier for the module
	 * @returns string Unique (but not canonicalized) identifier for the module
	 * @param string $code (optional) Module code
	 */
	public function addModule($identifier, &$code = null);


	/**
	 * Look for require statements in the code of the module with the given identifier and add referenced modules. Allows dependencies in arbitary modules to be resolved without adding the module itself to the final output.
	 *
	 * @param string $toplevelidentifier The top level identifier of the module
	 * @param string $code (optional) Module code
	 * @return string The code with resolved dependencies
	 */
	public function resolveDependencies($toplevelidentifier, &$code = null);


	/**
	 * Get all the resolved dependencies up to the current point
	 *
	 * @returns Module[]
	 */
	public function getAllDependencies();

}