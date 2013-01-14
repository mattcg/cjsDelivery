<?php
/**
 * Pragma manager plugin for cjsDelivery
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace cjsDelivery;

class PragmaManager implements \hookManager\Plugin {

	private $pragmas = array();
	private $pragmaformat;


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


	/**
	 * Register the plugin on a cjsDelivery class instance
	 *
	 * @param cjsDelivery $delivery
	 */
	public function register(\hookManager\Client $delivery) {
		$hookmanager = $delivery->getHookManager();

		$that = $this;
		$hookmanager->hook(processHooks\PROCESS_MODULE, function(&$code) use ($that) {
			$that->processPragmas($code);
		});
	}


	/**
	 * Process pragmas in the code and exclude or include blocks depending on the setup
	 *
	 * @param string $code The code to process
	 */
	public function processPragmas(&$code) {
		$pattern = $this->pragmaformat;
		if (!$pattern) {
			$pattern = self::DEFAULT_PFMT;
		}
		$that = $this;
		$code = preg_replace_callback($pattern, function($match) use ($that) {
			if ($that->checkPragma($match['pragma'])) {
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
		if (!in_array($name, $this->pragmas, true)) {
			$this->pragmas[] = $name;
		}
	}


	/**
	 * Unset a pragma, excluding it from the output
	 *
	 * All pragmas are unset by default, therefore this method would have to be called only to undo a change using the setPragma method.
	 *
	 * @param string $name The name of the pragma to unset
	 */
	public function unsetPragma($name) {
		$offset = array_search($name, $this->pragmas, true);
		if ($offset !== false) {
			array_splice($this->pragmas, $offset, 1);
		}
	}


	/**
	 * Get all the currently set pragmas
	 *
	 * @return array
	 */
	public function getPragmas() {
		return $this->pragmas;
	}


	/**
	 * Set pragmas in bulk
	 *
	 * @param array $pragmas
	 */
	public function setPragmas(array $pragmas) {
		foreach ($pragma as $pragmas) {
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
		return in_array($name, $this->pragmas, true);
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