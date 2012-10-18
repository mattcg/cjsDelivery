<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once __DIR__.'/flatIdentifierGenerator.php';
require_once __DIR__.'/minIdentifierGenerator.php';

class delivery extends \hookManager\pluggable {

	private $generator = null;
	private $resolver  = null;

	private $mainmodule;

	public function setGenerator($generator) {
		$this->generator = $generator;
	}

	public function getGenerator() {
		return $this->generator;
	}

	public function setResolver(dependencyResolver $resolver) {
		$this->resolver = $resolver;
	}

	public function getResolver() {
		return $this->resolver;
	}


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $identifier Identifier for the module
	 */
	public function addModule($identifier) {
		$this->resolver->addModule($identifier);
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
		$identifiermanager = $this->resolver->getIdentifierManager();
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
		$identifiermanager = $this->resolver->getIdentifierManager();
		$mainmodule = '';
		if ($this->mainmodule) {
			$mainmodule = $identifiermanager->getFlattenedIdentifier($this->mainmodule);
		}

		$allmodules = $this->resolver->getAllDependencies();
		return $this->generator->buildOutput($allmodules, $mainmodule);
	}


	/**
	 * Set whether to use minified identifers like 'a' and 'Bb' in output instead of mnenomic ones
	 *
	 * @param bool $yes
	 */
	public function minifyIdentifiers($yes = true) {
		if ($yes) {
			$generator = new minIdentifierGenerator();
		} else {
			$generator = new flatIdentifierGenerator();
		}

		$identifiermanager = $this->resolver->getIdentifierManager();
		$identifiermanager->setIdentifierGenerator($generator);
	}


	/**
	 * Get the maximum modified time of each of the module files, including dependencies
	 *
	 * @return int The maximum modified time of each of the module files
	 */
	public function getLastModTime() {
		$lastmodtime = 0;

		$dependencies = $this->resolver->getAllDependencies();
		foreach ($dependencies as &$module) {
			$lastmodtime = max($lastmodtime, $module->getModificationTime());
		}

		return $lastmodtime;
	}
}