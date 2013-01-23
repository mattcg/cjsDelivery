<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class FileDependencyResolver implements \hookManager\Client, DependencyResolver {

	const REQUIRE_PREG = '/require\((\'|")(.*?)\1\)/';

	private $modules = array();

	private $identifiermanager = null;
	private $hookmanager = null;

	public function __construct(IdentifierManager $identifiermanager) {
		$this->identifiermanager = $identifiermanager;
	}

	public function setHookManager(\hookManager\Manager $hookmanager) {
		$this->hookmanager = $hookmanager;
	}

	public function getHookManager() {
		return $this->hookmanager;
	}

	public function getIdentifierManager() {
		return $this->identifiermanager;
	}


	/**
	 * @see dependencyResolve::getAllDependencies
	 */
	public function getAllDependencies() {
		return $this->modules;
	}


	/**
	 * Get the raw contents from a module file
	 *
	 * @throws Exception If the module file is unreadable
	 * @param string $realpath The resolved path to the module file
	 * @return string Raw module code
	 */
	private function getModuleContents($realpath) {
		$code = @file_get_contents($realpath, false);
		if ($code === false) {
			throw new Exception("Unable to read '$realpath'", Exception::UNABLE_TO_READ);
		}

		return $code;
	}


	/**
	 * @see dependencyResolve::hasModule
	 * @param string $realpath Absolute path to the module file
	 */
	public function hasModule($realpath) {
		return isset($this->modules[$realpath]);
	}


	/**
	 * @see dependencyResolve::addModule
	 * @param string $filepath Path to the module file
	 */
	public function addModule($filepath) {
		$queue = array();

		$realpath = $this->identifiermanager->addIdentifier($filepath);

		// Check if the module has already been added
		if ($this->hasModule($realpath)) {
			return $this->identifiermanager->getFlattenedIdentifier($realpath);
		}

		try {
			$code = $this->resolveDependencies($realpath, $queue);
			$identifier = $this->addModuleToList($realpath, $code);
			while (count($queue)) {
				$filepath = array_pop($queue);
				$realpath = $this->identifiermanager->addIdentifier($filepath);
				$code = $this->resolveDependencies($realpath, $queue);
				$this->addModuleToList($realpath, $code);
			}
		} catch (Exception $e) {
			throw new Exception("Could not resolve dependency in '$filepath'", Exception::UNABLE_TO_RESOLVE, $e);
		}

		return $identifier;
	}


	/**
	 * @param string $realpath Canonicalized path to the module file
	 * @param string $code Code extracted from the module file
	 * @return string Unique (but not canonicalized) identifier for the module
	 */
	private function addModuleToList($realpath, &$code) {
		$identifier = $this->identifiermanager->getFlattenedIdentifier($realpath);

		$module = new Module($code);
		$module->setModificationTime(filemtime($realpath));
		$module->setUniqueIdentifier($identifier);

		$this->modules[$realpath] = $module;
		return $identifier;
	}


	/**
	 * Look for require statements in the code and add referenced modules
	 *
	 * @param string $realpath The resolved path to the module file
	 * @return string The code with resolved dependencies
	 */
	private function resolveDependencies($realpath, &$queue) {
		$that = $this;
		$code = $this->getModuleContents($realpath);
		$relativetodir = dirname($realpath);

		// Allow plugins to process modules before resolving as dependencies could be removed/added
		if ($this->hookmanager) {
			$this->hookmanager->run(processHooks\PROCESS_MODULE, $code);
		}

		return preg_replace_callback(self::REQUIRE_PREG, function($match) use ($that, &$queue, $relativetodir) {
			$filepath = $match[2];

			// If the given path was relative, resolve it from the current module directory
			if ($filepath[0] === '.') {
				$filepath = $relativetodir . '/' . $filepath;
			}
	
			try {

				// Add the module and get the new identifier
				$realpath = $that->getIdentifierManager()->addIdentifier($filepath);
				if (!in_array($realpath, $queue) and !$that->hasModule($realpath)) {
					$queue[] = $realpath;
				}
				$newidentifier = $that->getIdentifierManager()->getFlattenedIdentifier($realpath);
			} catch (Exception $e)  {
				throw new Exception("Could not resolve dependency for '$filepath'", Exception::UNABLE_TO_RESOLVE, $e);
			}

			return "require('$newidentifier')";
		}, $code);
	}
}
