<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class Delivery extends SignalSender {

	private $outputgenerator = null;
	private $dependencyresolver = null;

	private $globals = null;

	private $mainmodule;

	public function setOutputGenerator(OutputGenerator $generator) {
		$this->outputgenerator = $generator;
	}

	public function getOutputGenerator() {
		return $this->outputgenerator;
	}

	public function setDependencyResolver(DependencyResolverInterface $resolver) {
		$this->dependencyresolver = $resolver;
	}

	public function getDependencyResolver() {
		return $this->dependencyresolver;
	}


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $identifier Identifier for the module
	 * @param string $code (optional) Module code
	 */
	public function addModule($identifier, $code = null) {
		$this->dependencyresolver->addModule($identifier, $code);
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


	/**
	 * Get list of modules containing code to include globally, just outside normal module scope.
	 *
	 * @return array List of identifiers
	 */
	public function getGlobals() {
		return $this->globals;
	}


	/**
	 * @see IdentifierManagerInterface::setIncludes()
	 * @param array $includes
	 */
	public function setIncludes(array $includes = null) {
		$identifiermanager = $this->dependencyresolver->getIdentifierManager();
		$identifiermanager->setIncludes($includes);
	}


	/**
	 * @see IdentifierManagerInterface::getIncludes()
	 * @return array $includes
	 */
	public function getIncludes() {
		$identifiermanager = $this->dependencyresolver->getIdentifierManager();
		return $identifiermanager->getIncludes();
	}


	/**
	 * Get complete module output, including all added modules and dependencies
	 *
	 * This method is useful for generating a single file that can be loaded in one HTTP request.
	 *
	 * @param string $exportrequire Name of variable to export the require function as
	 * @return string Complete output
	 */
	public function getOutput($exportrequire = null) {
		$dependencyresolver = $this->dependencyresolver;
		$identifiermanager = $dependencyresolver->getIdentifierManager();
		$outputgenerator = $this->outputgenerator;

		$outputgenerator->setModules($this->dependencyresolver->getAllDependencies());

		if ($this->mainmodule) {
			$mainmodule = $dependencyresolver->getModule($this->mainmodule);
			$outputgenerator->setMainModule($mainmodule);
		}

		if ($this->globals) {
			foreach ($this->globals as $global) {
				$global = $identifiermanager->getTopLevelIdentifier($global);
				$globalscode = $dependencyresolver->resolveDependencies($global);
				$outputgenerator->addGlobalsCode($globalscode);
			}
		}

		$outputgenerator->setExportRequire($exportrequire);
		return $outputgenerator->buildOutput();
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


	/**
	 * @see DeliveryFactory::create
	 */
	public static function create(array $options = array()) {
		return DeliveryFactory::create($options);
	}
}
