<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require 'external/hookManager/hookManager.php';

require 'exception.php';
require 'module.php';

require 'identifierManager.php';
require 'fileIdentifierManager.php';

require 'identifierGenerator.php';
require 'flatIdentifierGenerator.php';
require 'minIdentifierGenerator.php';

require 'dependencyResolver.php';
require 'fileDependencyResolver.php';

require 'outputRenderer.php';
require 'templateOutputRenderer.php';

require 'outputGenerator.php';

require 'processHooks.php';

class delivery extends \hookManager\pluggable {

	private $outputgenerator = null;
	private $dependencyresolver = null;

	private $mainmodule;

	public function setOutputGenerator(outputGenerator $generator) {
		$this->outputgenerator = $generator;
	}

	public function setDependencyResolver(dependencyResolver $resolver) {
		$this->dependencyresolver = $resolver;
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
	 * Get complete module output, including all added modules and dependencies
	 *
	 * This method is useful for generating a single file that can be loaded in one HTTP request.
	 *
	 * @throws cjsDeliveryException If the module is not found
	 *
	 * @return string Complete output
	 */
	public function getOutput() {
		$identifiermanager = $this->dependencyresolver->getIdentifierManager();
		$mainmodule = '';
		if ($this->mainmodule) {
			$mainmodule = $identifiermanager->getFlattenedIdentifier($this->mainmodule);
		}

		$allmodules = $this->dependencyresolver->getAllDependencies();
		return $this->outputgenerator->buildOutput($allmodules, $mainmodule);
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