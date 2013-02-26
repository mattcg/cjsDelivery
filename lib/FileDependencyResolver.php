<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once 'external/hookManager/lib/Client.php';
require_once 'external/hookManager/lib/Manager.php';

require_once 'Exception.php';
require_once 'DependencyResolver.php';
require_once 'IdentifierManager.php';
require_once 'Module.php';
require_once 'processHooks.php';

class FileDependencyResolver implements \hookManager\Client, DependencyResolver {

	const EXT_JS = 'js';
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
	 * @param string $tlipath The canonicalized absolute pathname of the module, excluding any extension
	 * @return string Raw module code
	 */
	public function getModuleContents($tlipath) {
		$realpath = $this->getSystemPathForTopLevelIdentifier($this->identifiermanager->getTopLevelIdentifier($tlipath));
		$code = @file_get_contents($realpath, false);
		if ($code === false) {
			throw new Exception("Unable to read '$realpath'", Exception::UNABLE_TO_READ);
		}

		return $code;
	}


	/**
	 * @see DependencyResolver::hasModule
	 * @param string $tlipath The canonicalized absolute pathname of the module, excluding any extension
	 */
	public function hasModule($tlipath) {
		return isset($this->modules[$tlipath]);
	}


	/**
	 * @see DependencyResolver::addModule
	 * @param string $filepath Path to the module file
	 */
	public function addModule($filepath) {
		$tlipath = $this->identifiermanager->addIdentifier($filepath);

		// Check if the module has already been added
		if ($this->hasModule($tlipath)) {
			return $this->identifiermanager->getFlattenedIdentifier($tlipath);
		}

		$code = $this->resolveDependencies($tlipath);
		$identifier = $this->addModuleToList($tlipath, $code);

		return $identifier;
	}


	/**
	 * @param string $tlipath The canonicalized absolute pathname of the module, excluding any extension
	 * @param string $code Code extracted from the module file
	 * @return string Unique (but not canonicalized) identifier for the module
	 */
	private function addModuleToList($tlipath, &$code) {
		$identifier = $this->identifiermanager->getFlattenedIdentifier($tlipath);

		$module = new Module($code);
		$module->setModificationTime(filemtime($this->getSystemPathForTopLevelIdentifier($tlipath)));
		$module->setUniqueIdentifier($identifier);

		$this->modules[$tlipath] = $module;
		return $identifier;
	}


	/**
	 * @see DependencyResolver::resolveDependencies
	 * @param string $tlipath The canonicalized absolute pathname of the module, excluding any extension
	 */
	public function resolveDependencies($tlipath) {
		$queue = array();

		try {
			$code = $this->queueDependencies($tlipath, $queue);
		} catch (Exception $e) {
			throw new Exception("Could not resolve dependencies in '$tlipath'", Exception::UNABLE_TO_RESOLVE, $e);
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
	 * @param string $tlipath The canonicalized absolute pathname of the module, excluding any extension
	 * @param array $queue Queue of identifiers to resolve
	 * @return string The code with resolved dependencies
	 */
	private function queueDependencies($tlipath, &$queue) {
		$that = $this;
		$code = $this->getModuleContents($tlipath);
		$relativetodir = dirname($tlipath);

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
				$tlipath = $identifiermanager->addIdentifier($filepath);
				if (!in_array($tlipath, $queue) and !$that->hasModule($tlipath)) {
					$queue[] = $tlipath;
				}
				$newidentifier = $identifiermanager->getFlattenedIdentifier($tlipath);
			} catch (Exception $e)  {
				throw new Exception("Could not resolve dependency '$filepath'", Exception::UNABLE_TO_RESOLVE, $e);
			}

			return "require('$newidentifier')";
		}, $code);
	}


	/**
	 * @param string $tlipath The canonicalized absolute pathname of the module, excluding any extension
	 * @returns string The system path to the module file
	 */
	private function getSystemPathForTopLevelIdentifier($tlipath) {
		return $tlipath . '.' . self::EXT_JS;
	}
}
