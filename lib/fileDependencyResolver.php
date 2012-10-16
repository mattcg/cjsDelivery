<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once __DIR__.'/processHooks.php';

require_once __DIR__.'/dependencyResolver.php';
require_once __DIR__.'/exception.php';

class fileDependencyResolver implements \hookManager\client, dependencyResolver {

	const REQUIRE_PREG = '/require\((\'|")(.*?)\1\)/';

	private $modules = array();

	private $identifiermanager = null;
	private $hookmanager = null;

	public function __construct(identifierManager $identifiermanager) {
		$this->identifiermanager = $identifiermanager;
	}

	public function setHookManager(\hookManager\manager $hookmanager) {
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
	 * @see dependencyResolve::addModule
	 * @param string $filepath Path to the module file
	 */
	public function addModule($filepath) {
		$realpath = $this->identifiermanager->addIdentifier($filepath);

		// Check if the module has already been added
		if (isset($this->modules[$realpath])) {
			return $this->identifiermanager->getFlattenedIdentifier($realpath);
		}

		$newcode = $this->resolveDependencies($realpath);
		$newidentifier = $this->identifiermanager->getFlattenedIdentifier($realpath);

		$module = new module($newcode);
		$module->setModificationTime(filemtime($realpath));
		$module->setUniqueIdentifier($newidentifier);

		$this->modules[$realpath] = $module;
		return $newidentifier;
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
			throw new cjsDeliveryException("Unable to read '$realpath'", cjsDeliveryException::UNABLE_TO_READ);
		}

		return $code;
	}


	/**
	 * Look for require statements in the code and add referenced modules
	 *
	 * @param string $realpath The resolved path to the module file
	 * @return string The code with resolved dependencies
	 */
	private function resolveDependencies($realpath) {
		$that = $this;
		$code = $this->getModuleContents($realpath);
		$relativetodir = dirname($realpath);

		// Allow plugins to process modules before resolving as dependencies could be removed/added
		if ($this->hookmanager) {
			$this->hookmanager->run(processHooks\PROCESS_MODULE, $code);
		}

		return preg_replace_callback(self::REQUIRE_PREG, function($match) use ($that, $relativetodir) {
			return $that::requireCallback($that, $relativetodir, $match[2]);
		}, $code);
	}


	/**
	 * Callback for handling 'require' calls in parsed modules
	 *
	 * @param cjsDelivery $that The instance to operate on
	 * @param string $relativetodir Path to the directory of the module file currently being parsed
	 * @param string $newfilepath Path to required module file
	 * @return string The new value to replace the require call with
	 */
	public static function requireCallback($that, $relativetodir, $newfilepath) {

		// If the given path was relative, resolve it from the current module directory
		if ($newfilepath[0] !== '/') {
			$newfilepath = $relativetodir . '/' . $newfilepath;
		}

		// Add the module and get the new identifier
		$newidentifier = $that->addModule($newfilepath);
		return "require('$newidentifier')";
	}
}