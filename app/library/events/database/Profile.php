<?php

/**
 * Event to trigger profiling SQL statements
 *
 * @author Jete O'Keeffe
 * @version 1.0
 */

namespace Events\Database;

class Profile extends \Phalcon\Events\Manager {

	/**
	 * Constructor
 	 */
	public function __construct() {
		$this->createEvent();
	}

	/**
	 * Create the Event
	 */
	public function createEvent() {
		$di = \Phalcon\DI::getDefault();
		$di->set('profiler', function() {
			return new \Phalcon\Db\Profiler();
		}, TRUE);
		$profiler = $di->get('profiler');


		$this->attach('db', function($event, $connection) use ($profiler) {
			if ($event->getType() == 'beforeQuery') {
				$profiler->startProfile($connection->getSQLStatement());
			}


			if ($event->getType() == 'afterQuery') {
				$profiler->stopProfile();
			}
		});
	}
}
