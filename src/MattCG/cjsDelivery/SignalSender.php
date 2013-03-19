<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

abstract class SignalSender {

	protected $signal = null;

	public function setSignalManager(\Aura\Signal\Manager $signal) {
		$this->signal = $signal;
	}

	public function getSignalManager() {
		return $this->signal;
	}
}
