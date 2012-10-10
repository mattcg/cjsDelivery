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

function create() {
	$hookmanager = hookManager\create();
	$namemanager = new nameManager();

	$outputrenderer = new outputRenderer();

	$resolver = new dependencyResolver($namemanager);
	$resolver->setHookManager($hookmanager);

	$generator = new outputGenerator($outputrenderer);
	$generator->setHookManager($hookmanager);

	$delivery = new cjsDelivery();
	$delivery->setGenerator($generator);
	$delivery->setResolver($resolver);

	return $delivery;
}

class cjsDelivery implements hookManager\client {
	private $generator = null;
	private $resolver  = null;

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

	public function setHookManager(hookManager\manager $hookmanager) {
		$this->hookmanager = $hookmanager;
	}

	public function getHookManager() {
		return $this->hookmanager;
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
	 * Get complete module output, including all added modules and dependencies
	 *
	 * This method is useful for generating a single file that can be loaded in one HTTP request.
	 *
	 * @throws cjsDeliveryException If the module is not found
	 *
	 * @return string Complete output
	 */
	public function getOutput() {
		return $this->generator->buildOutput($this->resolver->getAllDependencies());
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