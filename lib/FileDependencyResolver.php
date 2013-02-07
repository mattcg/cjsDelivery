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
	 * @see DependencyResolver::getAllDependencies
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
	public function getModuleContents($realpath) {
		$code = @file_get_contents($realpath, false);
		if ($code === false) {
			throw new Exception("Unable to read '$realpath'", Exception::UNABLE_TO_READ);
		}

		return $code;
	}


	/**
	 * @see DependencyResolver::hasModule
	 * @param string $realpath Absolute path to the module file
	 */
	public function hasModule($realpath) {
		return isset($this->modules[$realpath]);
	}


	/**
	 * @see DependencyResolver::addModule
	 * @param string $filepath Path to the module file
	 */
	public function addModule($filepath) {
		$realpath = $this->identifiermanager->addIdentifier($filepath);

		// Check if the module has already been added
		if ($this->hasModule($realpath)) {
			return $this->identifiermanager->getFlattenedIdentifier($realpath);
		}

		$code = $this->resolveDependencies($realpath);
		$identifier = $this->addModuleToList($realpath, $code);

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
	 * @see DependencyResolver::resolveDependencies
	 * @param string $realpath Canonicalized path to the module file
	 */
	public function resolveDependencies($realpath) {
		$queue = array();

		try {
			$code = $this->queueDependencies($realpath, $queue);
		} catch (Exception $e) {
			throw new Exception("Could not resolve dependencies in '$realpath'", Exception::UNABLE_TO_RESOLVE, $e);
		}

		try {
			while (count($queue)) {
				$dependencyrealpath = array_pop($queue);
				$dependencycode = $this->queueDependencies($dependencyrealpath, $queue);
				$this->addModuleToList($dependencyrealpath, $dependencycode);
			}
		} catch (Exception $e) {
			throw new Exception("Could not resolve dependency in '$dependencyrealpath'", Exception::UNABLE_TO_RESOLVE, $e);
		}

		return $code;
	}


	/**
	 * Look for required module identifiers and add them to the given queue.
	 *
	 * @param string $realpath Canonicalized path to the module file
	 * @param array $queue Queue of identifiers to resolve
	 * @return string The code with resolved dependencies
	 */
	private function queueDependencies($realpath, &$queue) {
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
	
			$identifiermanager = $that->getIdentifierManager();
			try {

				// Add the module and get the new identifier
				$realpath = $identifiermanager->addIdentifier($filepath);
				if (!in_array($realpath, $queue) and !$that->hasModule($realpath)) {
					$queue[] = $realpath;
				}
				$newidentifier = $identifiermanager->getFlattenedIdentifier($realpath);
			} catch (Exception $e)  {
				throw new Exception("Could not resolve dependency '$filepath'", Exception::UNABLE_TO_RESOLVE, $e);
			}

			return "require('$newidentifier')";
		}, $code);
	}
}
