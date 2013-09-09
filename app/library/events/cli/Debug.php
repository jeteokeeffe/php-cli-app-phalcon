<?php

/**
 * Event that displays debug output at the end of the application
 * 
 * @package Cli
 * @subpackage Events
 * @author Jete O'Keeffe
 * @version 1.0
 */

namespace Cli\Events;


class Debug extends \Phalcon\Events\Manager {

	public function __construct() {
		$this->createEvent();
	}


	public function createEvent() {
		$this->attach('', function ($event, $app) {

		});
	}
}
