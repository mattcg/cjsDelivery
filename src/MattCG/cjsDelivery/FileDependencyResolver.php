<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class FileDependencyResolver extends SignalSender implements DependencyResolverInterface {

	const REQUIRE_PREG = '/require\((\'|")(.*?)\1\)/';

	private $modules = array();

	private $identifiermanager;

	public function __construct(IdentifierManagerInterface $identifiermanager) {
		$this->identifiermanager = $identifiermanager;
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
	 * @see DependencyResolverInterface::getModule
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 */
	public function getModule($toplevelidentifier) {
		if (!$this->hasModule($toplevelidentifier)) {
			throw new Exception("Unknown module '$toplevelidentifier'", Exception::UNKNOWN_MODULE);
		}

		return $this->modules[$toplevelidentifier];
	}


	/**
	 * @see DependencyResolverInterface::addModule
	 * @param string $identifier Path to the module file
	 */
	public function addModule($identifier, &$code = null) {
		$identifiermanager = $this->identifiermanager;
		$toplevelidentifier = $identifiermanager->addIdentifier($identifier);

		// Check if the module has already been added.
		if ($this->hasModule($toplevelidentifier)) {
			return $identifiermanager->getFlattenedIdentifier($toplevelidentifier);
		}

		// Check if the module is a JSON file. JSON files should have no dependences, so their code is already 'resolved'.
		if ($identifiermanager->isJson($toplevelidentifier)) {
			if ($code === null) {
				$code = $this->getFileContents($toplevelidentifier);
			}

			$resolvedcode = 'module.exports = ' . trim($code) . ';' . PHP_EOL;
		} else {
			$resolvedcode = $this->resolveDependencies($toplevelidentifier, $code);
		}

		$identifier = $this->addModuleToList($toplevelidentifier, $resolvedcode);
		return $identifier;
	}


	/**
	 * @param string $toplevelidentifier The canonicalized absolute pathname of the module, excluding any extension
	 * @param string $code Code extracted from the module file
	 * @return string Unique (but not canonicalized) identifier for the module
	 */
	private function addModuleToList($toplevelidentifier, &$code) {
		$identifiermanager = $this->identifiermanager;
		$identifier = $identifiermanager->getFlattenedIdentifier($toplevelidentifier);

		$module = new Module($code);
		$module->setModificationTime(filemtime($identifiermanager->getRealpath($toplevelidentifier)));
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
		$realpath = $this->identifiermanager->getRealpath($toplevelidentifier);
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
			$result = $this->signal->send($this, SignalSender::PROCESS_MODULE, $code)->getLast();
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
}
