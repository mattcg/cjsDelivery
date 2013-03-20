<?php
/**
 * Pragma manager plugin for cjsDelivery
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class PragmaManager {

	private $pragmas = array();
	private $pragmaformat;

	protected $signal;


	/**
	 * Default pragma format
	 *
	 * <code>
	 * // ifdef DEBUG_CLIENT
	 * ...
	 * // endif DEBUG_CLIENT
	 * </code>
	 */
	const DEFAULT_PFMT = '/\/\/ ifdef (?<pragma>[A-Z_]+)\n(.*?)\n\/\/ endif \1/';

	const MATCH_NAME = 'pragma';


	public function __construct(\Aura\Signal\Manager $signal, DependencyResolverInterface $dependencyresolver) {
		$this->signal = $signal;

		$that = $this;
		$signal->handler($dependencyresolver, SignalSender::PROCESS_MODULE, function($code) use ($that) {
			return $that->processPragmas($code);
		});
	}


	/**
	 * Process pragmas in the code and exclude or include blocks depending on the setup
	 *
	 * @param string $code The code to process
	 */
	public function processPragmas($code) {
		$pattern = $this->pragmaformat;
		if (!$pattern) {
			$pattern = self::DEFAULT_PFMT;
		}

		$that = $this;
		return preg_replace_callback($pattern, function($match) use ($that) {
			if (isset($match[self::MATCH_NAME]) and $that->checkPragma($match[self::MATCH_NAME])) {
				return $match[0];
			}

			// Replace the pragma with an empty string if not set
			return '';
		}, $code);
	}


	/**
	 * Set a pragma to be included in the output
	 *
	 * By default, all blocks within pragmas matching the pragma pattern will be excluded from the output.
	 *
	 * @param string $name The name of the pragma to set
	 */
	public function setPragma($name) {
		$this->pragmas[$name] = true;
	}


	/**
	 * Unset a pragma, excluding it from the output
	 *
	 * All pragmas are unset by default, therefore this method would have to be called only to undo a change using the setPragma method.
	 *
	 * @param string $name The name of the pragma to unset
	 */
	public function unsetPragma($name) {
		if ($this->checkPragma($name)) {
			unset($this->pragmas[$name]);
		}
	}


	/**
	 * Get all the currently set pragmas
	 *
	 * @return array
	 */
	public function getPragmas() {
		return array_keys($this->pragmas);
	}


	/**
	 * Set pragmas in bulk
	 *
	 * @param array $pragmas
	 */
	public function setPragmas(array $pragmas) {
		foreach ($pragmas as $pragma) {
			$this->setPragma($pragma);
		}
	}


	/**
	 * Check whether a pragma is enabled
	 *
	 * @param string $name The name of the pragma to check
	 * @return boolean Whether the pragma is set or not
	 */
	public function checkPragma($name) {
		return isset($this->pragmas[$name]);
	}


	/**
	 * Set the regular expression string used to find pragmas
	 *
	 * The pragma name should be matched by a named subpattern with the name 'pragma'.
	 *
	 * @param string $format A Perl-compatible regular expression
	 */
	public function setPragmaFormat($format) {
		$this->pragmaformat = $format;
	}


	/**
	 * Get the regular expression string used to find pragmas
	 *
	 * @return string A Perl-compatible regular expression or null if unset
	 */
	public function getPragmaFormat() {
		return $this->pragmaformat;
	}
}
