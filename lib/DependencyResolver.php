<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

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
	 * Check whether a module has been added.
	 *
	 * @param string $canonicalidentifier Canonicalised identifier for the module
	 * @returns boolean
	 */
	public function hasModule($canonicalidentifier);


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $identifier Identifier for the module
	 * @returns string Unique (but not canonicalized) identifier for the module
	 */
	public function addModule($identifier);


	/**
	 * Get all the resolved dependencies up to the current point
	 *
	 * @returns Module[]
	 */
	public function getAllDependencies();

}