<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

class Exception extends \Exception {
	const MODULE_NOT_FOUND  = 1;
	const UNKNOWN_MODULE    = 2;
	const UNABLE_TO_READ    = 3;
	const NOTHING_TO_BUILD  = 4;
	const UNABLE_TO_RESOLVE = 5;
	const BAD_MODULE_MTIME  = 6;

	public function __construct($message, $code = 0, Exception $previous = null) {
		parent::__construct($message, $code, $previous);
	}
}