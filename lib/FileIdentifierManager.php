<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

require_once 'Exception.php';
require_once 'IdentifierManager.php';
require_once 'IdentifierGenerator.php';

class FileIdentifierManager implements IdentifierManager {
	const EXT_JS = 'js';

	private $identifiergenerator;

	private $includes = null;

	private $resolvedidentifiers = array();
	private $modules = array();

	public function __construct(IdentifierGenerator $identifiergenerator) {
		$this->setIdentifierGenerator($identifiergenerator);
	}

	public function setIdentifierGenerator(IdentifierGenerator $identifiergenerator) {
		$this->identifiergenerator = $identifiergenerator;
	}

	public function getIdentifierGenerator() {
		return $this->identifiergenerator;
	}


	/**
	 * Set the list of file include directories to use when searching for module files.
	 *
	 * @see IdentifierManager::setIncludes()
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
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 */
	public function getFlattenedIdentifier($toplevelidentifier) {
		if (!isset($this->modules[$toplevelidentifier])) {
			throw new Exception("Unknown module '$toplevelidentifier'", Exception::UNKNOWN_MODULE);
		}

		return $this->identifiergenerator->generateFlattenedIdentifier($toplevelidentifier);
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
		if ($realpath !== false and is_file($realpath)) {
			return $realpath;
		}

		// 2) check for package.json
		$packagejsonpath = $dirpath . '/package.json';
		if (is_file($packagejsonpath)) {

			// TODO: catch and report errors
			$packagejson = json_decode(file_get_contents($packagejsonpath));
			if ($packagejson and !empty($packagejson->main)) {
				$mainpath = realpath($dirpath . '/' . $this->addExtensionIfMissing($packagejson->main));
				if ($mainpath !== false and is_file($mainpath)) {
					return $mainpath;
				}
			}
		}

		// 3) check for file with same name as folder
		$realpath = realpath($dirpath . '/' . basename($dirpath) . '.' . self::EXT_JS);
		if ($realpath !== false and is_file($realpath)) {
			return $realpath;
		}

		// 4) check for one file
		$filesindir = glob($dirpath . '/*.' . self::EXT_JS);
		if (count($filesindir) == 1) {
			$realpath = realpath($filesindir[0]);
			if ($realpath !== false and is_file($realpath)) {
				return $realpath;
			}
		}

		return false;
	}


	/**
	 * Loop through the specified list of include directories (if available) searching for a match against the given relative path.
	 *
	 * @param string $identifier Relative path to module file
	 * @return string|boolean Returns false if the file is not found
	 */
	private function findFileInIncludes($identifier) {
		if (!$this->includes) {
			return false;
		}

		foreach ($this->includes as $include) {
			$realpath = $this->findFile($include . '/' . $identifier);
			if ($realpath !== false) {
				return $realpath;
			}
		}

		return false;
	}


	/**
	 * Searching for a file match against the given relative path.
	 *
	 * @param string $identifier Relative path to module file
	 * @return string|boolean Returns false if the file is not found
	 */
	private function findFile($identifier) {

		// Is the path to a file?
		$identifierwithext = $this->addExtensionIfMissing($identifier);
		$realpath = realpath($identifierwithext);
		if ($realpath !== false and is_file($realpath)) {
			if ($identifierwithext === $identifier) {
				trigger_error('Module identifiers may not have file-name extensions like ".' . self::EXT_JS . '" (found "' . basename($identifier) . '").', E_USER_NOTICE);
			}

			return $realpath;
		}

		// Is the path to a directory?
		$realpath = realpath($identifier);
		if ($realpath !== false and is_dir($realpath)) {
			$realpath = $this->findFileInDirectory($realpath);
			if ($realpath !== false) {
				return $realpath;
			}
		}

		return false;
	}


	/**
	 * @see IdentifierManager::getTopLevelIdentifier()
	 * @param string $identifier Path to the module file, absolute (but not necessarily canonicalized) or relative to includes path
	 * @return string The canonicalized absolute pathname of the module, excluding any extension
	 */
	public function getTopLevelIdentifier($identifier) {
		if (isset($this->resolvedidentifiers[$identifier])) {
			return $this->resolvedidentifiers[$identifier];
		}

		// If the path is not absolute or relative, check the includes directory
		if ($identifier[0] !== '/' and $identifier[0] !== '.') {
			$realpath = $this->findFileInIncludes($identifier);
		} else {
			$realpath = $this->findFile($identifier);
		}

		if ($realpath === false) {
			throw new Exception("Module not found at '$identifier'", Exception::MODULE_NOT_FOUND);
		}

		$toplevelidentifier = $this->stripExtension($realpath);
		$this->resolvedidentifiers[$identifier] = $toplevelidentifier;
		return $toplevelidentifier;
	}


	/**
	 * @see IdentifierManager::addIdentifier()
	 * @param string $identifier Path to the module file
	 * @return string The canonicalized absolute pathname of the module, excluding any extension
	 */
	public function addIdentifier($identifier) {
		$toplevelidentifier = $this->getTopLevelIdentifier($identifier);
		if (!isset($this->modules[$toplevelidentifier])) {
			$this->modules[$toplevelidentifier] = true;
	 	}

		return $toplevelidentifier;
	}


	/**
	 * Strip the standard JavaScript file extension if present
	 *
	 * @param string $identifier
	 * @returns string The path with any file extension removed
	 */
	private function stripExtension($identifier) {
		return preg_replace('/\.' . self::EXT_JS . '$/', '', $identifier);
	}


	/**
	 * Add the standard JavaScript file extension if it's missing
	 *
	 * @param string $identifier
	 * @returns string The path with a file extension added if needed
	 */
	private function addExtensionIfMissing($identifier) {
		if ((pathinfo($identifier, PATHINFO_EXTENSION)) !== self::EXT_JS) {
			$identifier .= '.' . self::EXT_JS;
		}

		return $identifier;
	}
}
