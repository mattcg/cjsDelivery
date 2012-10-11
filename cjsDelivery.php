<?php
/**
 * cjsDelivery
 *
 * Write CommonJS-syntax JavaScript modules and deliver them to clients as a single file.
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once __DIR__.'/lib/external/hookManager/hookManager.php';
require_once __DIR__.'/lib/processHooks.php';
require_once __DIR__.'/lib/deliveryException.php';
require_once __DIR__.'/lib/dependencyResolver.php';
require_once __DIR__.'/lib/fileNameManager.php';
require_once __DIR__.'/lib/outputGenerator.php';
require_once __DIR__.'/lib/templateOutputRenderer.php';

function create() {
	$hookmanager = hookManager\create();
	$namemanager = new fileNameManager();

	$outputrenderer = new templateOutputRenderer();

	$resolver = new dependencyResolver($namemanager);
	$resolver->setHookManager($hookmanager);

	$generator = new outputGenerator($outputrenderer);
	$generator->setHookManager($hookmanager);

	$delivery = new cjsDelivery();
	$delivery->setHookManager($hookmanager);
	$delivery->setGenerator($generator);
	$delivery->setResolver($resolver);

	return $delivery;
}

class cjsDelivery extends \hookManager\pluggable {

	private $generator = null;
	private $resolver  = null;

	private $mainmodule;

	public function setGenerator($generator) {
		$this->generator = $generator;
	}

	public function getGenerator() {
		return $this->generator;
	}

	public function setResolver($resolver) {
		$this->resolver = $resolver;
	}

	public function getResolver() {
		return $this->resolver;
	}


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $name Name of the module to add
	 * @param string $filepath Path to the module file
	 */
	public function addModule($name, $filepath) {
		$this->resolver->addModule($name, $filepath);
	}


	/**
	 * Set the name of the main module
	 *
	 * Each module is wrapped in a function which isn't executed until the module is required, so
	 * a 'main' module needs to be 'required' automatically to kick off execution on the client.
	 *
	 * @param string $filepath The path to the main module
	 */
	public function setMainModule($filepath) {
		$namemanager = $this->resolver->getNameManager();
		$this->mainmodule = $namemanager->getCanonicalName($filepath);
	}


	/**
	 * Get the name of the main module
	 *
	 * @return string The name of the main module
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
		if (empty($this->mainmodule)) {
			throw new cjsDeliveryException('Main module not set', cjsDeliveryException::NO_MAIN);
		}

		$namemanager = $this->resolver->getNameManager();
		$mainmodule  = $namemanager->getResolvedName($this->mainmodule);
		$allmodules  = $this->resolver->getAllDependencies();
		return $this->generator->buildOutput($allmodules, $mainmodule);
	}


	/**
	 * Get the maximum modified time of each of the module files, including dependencies
	 *
	 * @return int The maximum modified time of each of the module files
	 */
	public function getLastModTime() {
		$lastmodtime = 0;

		$dependencies = $this->resolver->getAllDepencies();
		foreach ($dependencies as &$module) {
			$lastmodtime = max($lastmodtime, $module['filemtime']);
		}

		return $lastmodtime;
	}
}