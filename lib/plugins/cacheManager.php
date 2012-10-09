<?php
/**
 * Cache manager plugin for cjsDelivery
 *
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

class cacheManager {

	private $cachefile;


	/**
	 * Register the plugin on a cjsDelivery class instance
	 *
	 * @param cjsDelivery $instance
	 */
	function __construct(cjsDelivery $instance) {
		$that = $this;

		$instance->hook('output_ready', function(&$output) use ($that) {
			$that->setCacheContents($output);
		});

		$instance->hook('build_output', function(&$output) use ($that, $instance) {
			$output = $that->getCacheContents($instance->getLastModTime());
		});
	}


	/**
	 * Read the contents of the configured cache file, if it is fresh
	 *
	 * @throws cjsDeliveryException
	 *
	 * @return boolean|string Returns false if the cache doesn't exist or is stale
	 */
	public function getCacheContents($treshold) {
		$cachefile =& $this->cachefile;

		if (!$cachefile or !file_exists($cachefile)) {
			return false;
		}

		// Check treshold against the cache mod time
		$mtime = filemtime($cachefile);
		if ($treshold > $mtime) {
			return false;
		}

		// Return cache contents if fresh
		$contents = @file_get_contents($cachefile);
		if ($contents === false) {
			throw new cjsDeliveryException("Unable to read cache file at path '$cachefile'");
		}

		return $contents;
	}


	/**
	 * Write the contents of the configured cache file
	 *
	 * @throws cjsDeliveryException
	 *
	 * @param string $contents
	 */
	public function setCacheContents(&$contents) {
		$cachefile =& $this->cachefile;

		if ($cachefile) {
			$wrote = @file_put_contents($cachefile, $contents);

			if ($wrote === false) {
				throw new cjsDeliveryException("Unable to write to cache file at path '$cachefile'");
			}
		}
	}


	/**
	 * Set the cache file path
	 *
	 * @param string $cachefile Path to cachefile, including filename
	 */
	public function setCachePath($cachefile) {
		$this->cachefile = $cachefile;
	}


	/**
	 * Get the cache file path
	 *
	 * @return string Path to cachefile, including filename
	 */
	public function getCachePath() {
		return $this->cachefile;
	}
}