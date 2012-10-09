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

class cjsDelivery extends processHooks {

	private $mainmodule;
	private $modules;
	private $lastmodtime;
	private $templates;
	private $pragmaformat;

	const DEFAULT_MAIN = 'app';

	const DIR_TEMPLATES = '/templates';
	const DIR_LIB = __DIR__;

	const TEMPLATE_FULL = 'full';
	const TEMPLATE_MODULE = 'module';

	const EXT_JS = '.js';
	const EXT_MS = '.ms';

	public function __construct() {
		$this->modules = array();
		$this->lastmodtime = 0;
	}


	/**
	 * Simple template renderer used internally
	 *
	 * @param string $templatename
	 * @param array $keys
	 * @param array $values
	 * @return string
	 */
	private function renderTemplate($name, $keys = null, $values = null) {
		$filepath = self::DIR_LIB . self::DIR_TEMPLATES . '/' . $name . self::EXT_MS;

		if (!isset($this->templates[$name])) {
			$this->templates[$name] = file_get_contents($filepath, false);
		}

		if (!$keys) {
			return $this->templates[$name];
		}

		return str_replace($keys, $values, $this->templates[$name]);
	}


	/**
	 * Render output-ready code for a given module, calling the callback if specified
	 *
	 * @param string $realpath
	 * @param array $module
	 * @return string
	 */
	private function renderModule($realpath, &$module) {
		$name = $module['name'];

		// Add an incrementor to the name if needed
		$name = $this->getResolvedName($realpath);

		return $this->renderTemplate(self::TEMPLATE_MODULE,
			array('{{name}}', '{{code}}'),
			array($name, $module['code'])
		);
	}


	/**
	 * Render all of the output-ready code together
	 *
	 * @param string $output Concatenated module code
	 * @param string $main Name of the main module
	 * @return string
	 */
	private function renderOutput($output, $main) {
		return $this->renderTemplate(self::TEMPLATE_FULL,
			array('{{main}}', '{{output}}'),
			array($main, $output)
		);
	}


	/**
	 * Add a module. The module code will be parsed for 'require' statements to resolve dependencies
	 *
	 * @param string $name Name of the module to add
	 * @param string $filepath Path to the module file
	 * @return string The resolved module path
	 */
	public function addModule($name, $filepath) {
		$realpath = @realpath($filepath);

		// Check if the path was resolved
		if ($realpath === false) {
			throw new RuntimeException("Module '$name' not found");
		}

		// Check if the module has already been added
		if (!isset($this->modules[$realpath])) {
			$this->processModule($name, $realpath);
		}

		return $realpath;
	}


	/**
	 * @see cjsDelivery::addModule()
	 */
	private function processModule($name, $realpath) {

		// Check if a module with a similar name but different file exists, and if so increment $i
		$i = $this->checkSameName($name);

		// Cache the last modified time of the module file
		$mtime = filemtime($realpath);
		$this->lastmodtime = max($this->lastmodtime, $mtime);

		// Read the code from the module file
		$code = $this->getModuleContents($realpath);

		// Module hashmap with code and meta information for later processing
		$this->modules[$realpath] = array(
			'name' => $name,
			'path' => $realpath,
			'code' => &$code,
			'mtime' => $mtime,
			'i' => $i
		);

		// Allow plugins to process modules before resolving dependencies
		$this->runHook('process_module', $code);
		$this->resolveDependencies($code, $realpath);
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
			throw new RuntimeException("Unable to read $realpath");
		}

		return $code;
	}


	/**
	 * Look for require statements in the code and add referenced modules
	 *
	 * @param string $code The unresolved module code
	 * @param string $realpath The resolved path to the module file
	 */
	private function resolveDependencies(&$code, $realpath) {
		$that = $this;
		$code = preg_replace_callback('/require\((\'|")(.*?)\1\)/', function($match) use ($that, $realpath) {
			return $that::requireCallback($that, $realpath, $match[2]);
		}, $code);
	}


	/**
	 * Callback for handling 'require' calls in parsed modules
	 *
	 * @internal
	 * @param cjsDelivery $instance The instance to operate on
	 * @param string $filepath Path to the module file currently being parsed
	 * @param string $newfilepath Path to required module file
	 * @return string The new value to replace the require call with
	 */
	public static function requireCallback($instance, $filepath, $newfilepath) {
		$filedir = dirname($filepath);

		// Get the basename with the standard JavaScript extension stripped
		$newname = basename($newfilepath, self::EXT_JS);

		// If the given path was relative, resolve it from the current module directory
		if ($newfilepath[0] !== '/') {
			$newfilepath = $filedir . '/' . $newfilepath;
		}

		// Add the standard JavaScript file extension if it's missing
		if (('.' . pathinfo($newfilepath, PATHINFO_EXTENSION)) !== self::EXT_JS) {
			$newfilepath .= self::EXT_JS;
		}

		$newrealpath = $instance->addModule($newname, $newfilepath);

		// Return the known name with an incrementor if needed
		$newname = $instance->getResolvedName($newrealpath);

		// Use the incrementor to avoid name collisions
		return "require('$newname')";
	}


	/**
	 * Get the 'resolved' name of a module that will actually be used in the JavaScript output
	 *
	 * @param string $realpath The resolved path to the module file
	 * @return string The resolved name, including an incrementor in case of a collision
	 */
	private function getResolvedName($realpath) {
		if (!isset($this->modules[$realpath])) {
			throw new RuntimeException("Module '$realpath' not found");
		}

		$m = $this->modules[$realpath];
		$name = $m['name'];

		if ($m['i'] > 0) {
			$name = $name . $m['i'];
		}

		return $name;
	}


	/**
	 * Check for modules with the same name
	 *
	 * @param string $name The name to for duplicates of
	 * @return integer Number of modules with the same name
	 */
	private function checkSameName($name) {
		$i = 0;

		foreach ($this->modules as &$m) {
			if ($name === $m['name']) {
				$i++;
			}
		}

		return $i;
	}


	/**
	 * Build complete module output, including all added modules and dependencies
	 *
	 * This method is useful for generating a single file that can be loaded in one HTTP request.
	 *
	 * If specified, the callback function will receive the module code, name and path for every module.
	 * If a string is returned, it will be used in place of the original code.
	 *
	 * @throws Exception If the module is not found
	 *
	 * @return string Complete output
	 */
	public function buildOutput() {
		$output = '';

		$this->runHook('build_output', $output);

		// If output was created by the hook callbacks, return it
		if ($output) {
			return $output;
		}

		// Get the name of the main module
		if ($this->mainmodule) {
			$main = $this->mainmodule;
		} else {
			$main = self::DEFAULT_MAIN;
		}

		$concat = '';
		$hasmain = false;

		// Loop through the modules, render and look for the main module
		foreach ($this->modules as $filepath => &$module) {
			$concat .= $this->renderModule($filepath, $module);

			if (!$hasmain and $main === $module['name']) {
				$hasmain = true;
			}
		}

		// Output can't be built without a main module
		if (!$hasmain) {
			throw new LogicException("Main module '$main' not found in module list");
		}

		$output = $this->renderOutput($concat, $main);

		// Run hooks with the fully built output
		$this->runHook('output_ready', $output);

		return $output;
	}


	/**
	 * Get the maximum modified time of each of the module files
	 *
	 * @return int The maximum modified time of each of the module files
	 */
	public function getLastModTime() {
		return $this->lastmodtime;
	}


	/**
	 * Set the name of the main module
	 *
	 * Each module is wrapped in a function which isn't executed until the module is required, so
	 * a 'main' module needs to be 'required' automatically to kick off execution on the client.
	 *
	 * @param string $name The name of the main module
	 */
	public function setMainModule($name) {
		$this->mainmodule = $name;
	}


	/**
	 * Get the name of the main module
	 *
	 * @return string The name of the main module
	 */
	public function getMainModule() {
		return $this->mainmodule;
	}
}