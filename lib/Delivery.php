<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once 'OutputGenerator.php';
require_once 'DependencyResolver.php';

class Delivery {

	private $outputgenerator = null;
	private $dependencyresolver = null;

	private $globals = null;

	private $mainmodule;

	protected $signal = null;

	public function setOutputGenerator(OutputGenerator $generator) {
		$this->outputgenerator = $generator;
	}

	public function setDependencyResolver(DependencyResolver $resolver) {
		$this->dependencyresolver = $resolver;
	}

	public function getDependencyResolver() {
		return $this->dependencyresolver;
	}

	public function setSignalManager(\Aura\Signal\Manager $signal) {
		$this->signal = $signal;
	}

	public function getSignalManager() {
		return $this->signal;
	}


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $identifier Identifier for the module
	 */
	public function addModule($identifier) {
		$this->dependencyresolver->addModule($identifier);
	}


	/**
	 * Set the name of the main module
	 *
	 * Each module is wrapped in a function which isn't executed until the module is required, so
	 * a 'main' module needs to be 'required' automatically to kick off execution on the client.
	 *
	 * @param string $identifier Identifier for the module
	 */
	public function setMainModule($identifier) {
		$identifiermanager = $this->dependencyresolver->getIdentifierManager();
		$this->mainmodule = $identifiermanager->getTopLevelIdentifier($identifier);
	}


	/**
	 * Get the top level identifier for the main module
	 *
	 * @return string The top level identifier for the main module
	 */
	public function getMainModule() {
		return $this->mainmodule;
	}


	/**
	 * Set list of modules containing code to include globally, just outside normal module scope.
	 *
	 * @param array $identifies List of identifiers
	 */
	public function setGlobals(array $identifiers = null) {
		$this->globals = $identifiers;
	}

	public function getGlobals() {
		return $this->globals;
	}


	/**
	 * Get complete module output, including all added modules and dependencies
	 *
	 * This method is useful for generating a single file that can be loaded in one HTTP request.
	 *
	 * @throws Exception If the module is not found
	 *
	 * @param string $exportrequire Name of variable to export the require function as
	 * @return string Complete output
	 */
	public function getOutput($exportrequire = '') {
		$identifiermanager = $this->dependencyresolver->getIdentifierManager();
		$mainmodule = '';
		if ($this->mainmodule) {

			// Exception should be thrown by getFlattenedIdentifier if main module is not in list
			$mainmodule = $identifiermanager->getFlattenedIdentifier($this->mainmodule);
		}

		$globalscode = '';
		if ($this->globals) {
			foreach ($this->globals as $global) {
				$global = $identifiermanager->getTopLevelIdentifier($global);
				$globalscode .= $this->dependencyresolver->resolveDependencies($global);
			}
		}

		$allmodules = $this->dependencyresolver->getAllDependencies();
		return $this->outputgenerator->buildOutput($allmodules, $mainmodule, $globalscode, $exportrequire);
	}


	/**
	 * Get the maximum modified time of each of the module files, including dependencies
	 *
	 * @return int The maximum modified time of each of the module files
	 */
	public function getLastModTime() {
		$lastmodtime = 0;

		$dependencies = $this->dependencyresolver->getAllDependencies();
		foreach ($dependencies as &$module) {
			$lastmodtime = max($lastmodtime, $module->getModificationTime());
		}

		return $lastmodtime;
	}
}