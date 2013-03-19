<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class FileDependencyResolver implements DependencyResolverInterface {

	const EXT_JS = 'js';
	const REQUIRE_PREG = '/require\((\'|")(.*?)\1\)/';

	private $modules = array();

	private $identifiermanager;

	protected $signal = null;

	public function __construct(IdentifierManagerInterface $identifiermanager) {
		$this->identifiermanager = $identifiermanager;
	}

	public function setSignalManager(\Aura\Signal\Manager $signal) {
		$this->signal = $signal;
	}

	public function getSignalManager() {
		return $this->signal;
	}

	public function getIdentifierManager() {
		return $this->identifiermanager;
	}


	/**
	 * @see DependencyResolverInterface::getAllDependencies
	 */
	public function getAllDependencies() {
		return $this->modules;
	}


	/**
	 * @see DependencyResolverInterface::hasModule
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 */
	public function hasModule($toplevelidentifier) {
		return isset($this->modules[$toplevelidentifier]);
	}


	/**
	 * @see DependencyResolverInterface::addModule
	 * @param string $identifier Path to the module file
	 */
	public function addModule($identifier, &$code = null) {
		$toplevelidentifier = $this->identifiermanager->addIdentifier($identifier);

		// Check if the module has already been added
		if ($this->hasModule($toplevelidentifier)) {
			return $this->identifiermanager->getFlattenedIdentifier($toplevelidentifier);
		}

		$resolvedcode = $this->resolveDependencies($toplevelidentifier, $code);
		$identifier = $this->addModuleToList($toplevelidentifier, $resolvedcode);

		return $identifier;
	}


	/**
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 * @param string $code Code extracted from the module file
	 * @return string Unique (but not canonicalized) identifier for the module
	 */
	private function addModuleToList($toplevelidentifier, &$code) {
		$identifier = $this->identifiermanager->getFlattenedIdentifier($toplevelidentifier);

		$module = new Module($code);
		$module->setModificationTime(filemtime($this->getFilePathForTopLevelIdentifier($toplevelidentifier)));
		$module->setUniqueIdentifier($identifier);

		$this->modules[$toplevelidentifier] = $module;
		return $identifier;
	}


	/**
	 * @see DependencyResolverInterface::resolveDependencies
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 */
	public function resolveDependencies($toplevelidentifier, &$code = null) {
		$queue = array();

		if ($code === null) {
			$code = $this->getFileContents($toplevelidentifier);
		}

		try {
			$resolvedcode = $this->queueDependencies($toplevelidentifier, $code, $queue);
		} catch (Exception $e) {
			throw new Exception("Could not resolve dependencies in '$toplevelidentifier'", Exception::UNABLE_TO_RESOLVE, $e);
		}

		$this->resolveDependenciesInQueue($queue);
		return $resolvedcode;
	}


	/**
	 * Resolve dependencies in the given queue.
	 *
	 * @param array $queue Queue of identifiers to add unresolved dependencies to
	 */
	private function resolveDependenciesInQueue(&$queue) {
		try {
			while (count($queue)) {
				$toplevelidentifier = array_pop($queue);
				$code = $this->getFileContents($toplevelidentifier);
				$resolvedcode = $this->queueDependencies($toplevelidentifier, $code, $queue);
				$this->addModuleToList($toplevelidentifier, $resolvedcode);
			}
		} catch (Exception $e) {
			throw new Exception("Could not resolve dependency in '$toplevelidentifier'", Exception::UNABLE_TO_RESOLVE, $e);
		}
	}


	/**
	 * Get the raw contents from a module file
	 *
	 * @throws Exception If the module file is unreadable
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 * @return string Raw module code
	 */
	private function getFileContents($toplevelidentifier) {
		$realpath = $this->getFilePathForTopLevelIdentifier($toplevelidentifier);
		$code = @file_get_contents($realpath, false);
		if ($code === false) {
			throw new Exception("Unable to read '$realpath'", Exception::UNABLE_TO_READ);
		}

		return $code;
	}


	/**
	 * Look for required module identifiers and add them to the given queue.
	 *
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 * @param string $code Unresolved module code
	 * @param array $queue Queue of identifiers to add unresolved dependencies to
	 * @return string The code with resolved dependencies
	 */
	private function queueDependencies($toplevelidentifier, &$code, &$queue) {
		$that = $this;
		$relativetodir = dirname($toplevelidentifier);

		// Allow plugins to process modules before resolving as dependencies could be removed/added
		if ($this->signal) {
			$result = $this->signal->send($this, processHooks\PROCESS_MODULE, $code)->getLast();
			if ($result) {
				$code = $result->value;
			}
		}

		return preg_replace_callback(self::REQUIRE_PREG, function($match) use ($that, &$queue, $relativetodir) {
			$identifier = $match[2];

			// If the given path was relative, resolve it from the current module directory
			if ($identifier[0] === '.') {
				$identifier = $relativetodir . '/' . $identifier;
			}
	
			$identifiermanager = $that->getIdentifierManager();
			try {

				// Add the module and get the new identifier
				$toplevelidentifier = $identifiermanager->addIdentifier($identifier);
				if (!in_array($toplevelidentifier, $queue) and !$that->hasModule($toplevelidentifier)) {
					$queue[] = $toplevelidentifier;
				}
				$newidentifier = $identifiermanager->getFlattenedIdentifier($toplevelidentifier);
			} catch (Exception $e)  {
				throw new Exception("Could not resolve dependency '$identifier'", Exception::UNABLE_TO_RESOLVE, $e);
			}

			return "require('$newidentifier')";
		}, $code);
	}


	/**
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 * @returns string The system path to the module file
	 */
	private function getFilePathForTopLevelIdentifier($toplevelidentifier) {
		return $toplevelidentifier . '.' . self::EXT_JS;
	}
}
