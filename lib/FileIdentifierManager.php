<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class FileIdentifierManager implements IdentifierManager {
	const EXT_JS = 'js';

	private $identifiergenerator;

	private $modules = array();
	private $includes = null;

	private $tlicache = array();

	public function __construct(identifierGenerator $identifiergenerator) {
		$this->setIdentifierGenerator($identifiergenerator);
	}

	public function setIdentifierGenerator(identifierGenerator $identifiergenerator) {
		$this->identifiergenerator = $identifiergenerator;
	}

	public function getIdentifierGenerator() {
		return $this->identifiergenerator;
	}


	/**
	 * Set the list of file include directories to use when searching for module files.
	 *
	 * @param array $includes
	 */
	public function setIncludes(array $includes = null) {

		// Normalize empty array to null
		if (is_array($includes) and !count($includes)) {
			$includes = null;
		}

		$this->includes = $includes;
	}


	/**
	 * @see IdentifierManager::getFlattenedIdentifier()
	 * @param string $realpath The canonicalized absolute pathname of the module
	 */
	public function getFlattenedIdentifier($realpath) {
		if (!isset($this->modules[$realpath])) {
			throw new Exception("Unknown module '$realpath'", Exception::UNKNOWN_MODULE);
		}

		return $this->identifiergenerator->generateFlattenedIdentifier($realpath);
	}


	/**
	 * Searches for a potential module file in a given directory using the following prioritised rules:
	 * 1) a file named index.js in the given directory
	 * 2) the value of 'main' in a package.json file in the given directory
	 * 3) a file with the same basename as the given directory
	 * 4) an only-child file in the given directory
	 *
	 * @param string $dirpath Path to directory
	 * @return string|boolean Returns false if the file is not found
	 */
	private function findFileInDirectory($dirpath) {

		// 1) check for index file
		$realpath = realpath($dirpath . '/index.' . self::EXT_JS);

		// 2) check for package.json
		$packagejsonpath = $dirpath . '/package.json';
		if (is_file($packagejsonpath)) {

			// TODO: catch and report errors
			$packagejson = json_decode(file_get_contents($packagejsonpath));
			if ($packagejson and !empty($packagejson->main)) {
				$mainpath = $dirpath . '/' . $this->addExtension($packagejson->main);
				if (is_file($mainpath)) {
					return $mainpath;
				}
			}
		}

		// 3) check for file with same name as folder
		if ($realpath === false) {
			$realpath = realpath($dirpath . '/' . basename($dirpath) . '.' . self::EXT_JS);
		}

		// 4) check for one file
		if ($realpath === false) {
			$filesindir = glob($dirpath . '/*.' . self::EXT_JS);
			if (count($filesindir) == 1) {
				$realpath = realpath($filesindir[0]);
			}
		}

		if ($realpath !== false and is_file($realpath)) {
			return $realpath;
		}

		return false;
	}


	/**
	 * Loop through the specified list of include directories (if available) searching for a match against the given relative path.
	 *
	 * @param string $filepath Relative path to module file
	 * @return string|boolean Returns false if the file is not found
	 */
	private function findFileInIncludes($filepath) {
		if (!$this->inludes) {
			return false;
		}

		foreach ($this->includes as $include) {

			// First try with appended extension
			$realpath = realpath($include . '/' . $this->addExtension($filepath));
			if (is_file($realpath)) {
				return $realpath;
			}

			$realpath = realpath($include . '/' . $filepath);
			if (is_dir($realpath)) {
				$realpath = $this->findFileInDirectory($realpath);
				if ($realpath !== false) {
					return $realpath;
				}
			}
		}

		return false;
	}


	/**
	 * @see IdentifierManager::getTopLevelIdentifier()
	 * @param string $filepath Path to the module file, absolute (but not necessarily canonicalized) or relative to includes path
	 * @return string The canonicalized absolute pathname of the module
	 */
	public function getTopLevelIdentifier($filepath) {
		if (isset($this->tlicache[$filepath])) {
			return $this->tlicache[$filepath];
		}

		// If the path is not absolute, check the includes directory
		if ($filepath[0] !== '/') {
			$realpath = $this->findFileInIncludes($filepath);
			if ($realpath !== false) {
				$this->tlicache[$filepath] = $realpath;
				return $realpath;
			}
		} else {
			$filepathwithext = $this->addExtension($filepath);
			$realpath = realpath($filepathwithext);
			if (is_file($realpath)) {
				$this->tlicache[$filepath] = $realpath;
				return $realpath;
			}
	
			// Try again without the .js suffix on the given path
			$realpath = realpath($filepath);
			if (is_dir($realpath)) {
				$realpath = $this->findFileInDirectory($realpath);
				if ($realpath !== false) {
					$this->tlicache[$filepath] = $realpath;
					return $realpath;
				}
			}
		}

		throw new Exception("Module not found at '$filepath'", Exception::MODULE_NOT_FOUND);
	}


	/**
	 * @see IdentifierManager::addIdentifier()
	 * @param string $filepath Path to the module file
	 * @return string The canonicalized absolute pathname of the module
	 */
	public function addIdentifier($filepath) {
		$realpath = $this->getTopLevelIdentifier($filepath);
		if (!isset($this->modules[$realpath])) {
			$this->modules[$realpath] = true;
	 	}

		return $realpath;
	}


	/**
	 * Add the standard JavaScript file extension if it's missing
	 *
	 * @param string $filepath
	 * @returns string The path with a file extension added if needed
	 */
	private function addExtension($filepath) {
		if ((pathinfo($filepath, PATHINFO_EXTENSION)) !== self::EXT_JS) {
			$filepath .= '.' . self::EXT_JS;
		}

		return $filepath;
	}
}