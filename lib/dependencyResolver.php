<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class dependencyResolver implements hookManager\client {

	const EXT_JS = '.js';
	const REQUIRE_PREG = '/require\((\'|")(.*?)\1\)/';

	private $modules = array();

	private $namemanager = null;
	private $hookmanager = null;

	public function __construct(nameManager $namemanager) {
		$this->namemanager = $namemanager;
	}

	public function setHookManager(hookManager\manager $hookmanager) {
		$this->hookmanager = $hookmanager;
	}

	public function getHookManager() {
		return $this->hookmanager;
	}


	/**
	 * @return array
	 */
	public function getAllDependencies() {
		return $this->modules;
	}


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $name Name of the module to add
	 * @param string $filepath Path to the module file
	 * @return string The resolved module name
	 */
	public function addModule($name, $filepath) {
		$realpath = $this->namemanager->addModule($name, $filepath);

		// Check if the module has already been added
		if (isset($this->modules[$realpath])) {
			return $this->namemanager->getResolvedName($realpath);
		}

		$newcode = $this->resolveDependencies($realpath);
		$newname = $this->namemanager->getResolvedName($realpath)

		$this->modules[$realpath] = array('code' => &$newcode, 'name' => $newname);
		return $newname;
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
		$relativetodir = basename($realpath, self::EXT_JS);

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

		// Add the standard JavaScript file extension if it's missing
		if (('.' . pathinfo($newfilepath, PATHINFO_EXTENSION)) !== self::EXT_JS) {
			$newfilepath .= self::EXT_JS;
		}

		// Add the module and get the resolved name (with an incrementor if needed)
		$newname = $that->addModule(basename($newfilepath, self::EXT_JS), $newfilepath);

		// Use the incrementor to avoid name collisions
		return "require('$newname')";
	}
}