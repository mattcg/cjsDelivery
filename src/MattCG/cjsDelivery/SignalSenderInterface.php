<?php
/**
 * @author Matthew Caruana Galizia <m@m.cg>
 * @copyright Copyright (c) 2012, Matthew Caruana Galizia
 * @package cjsDelivery
 */

namespace MattCG\cjsDelivery;

interface SignalSenderInterface {

	public function setSignalManager(\Aura\Signal\Manager $signal);

	public function getSignalManager();
}
