<?php

/**
 * Event to trigger profiling SQL statements
 *
 * @author Jete O'Keeffe
 * @version 1.0
 */

namespace Events\Database;

use \Interfaces\IEvent as IEvent;

class Profile extends \Phalcon\Events\Manager implements IEvent {

	/**
	 * Constructor
 	 */
	public function __construct() {
		$this->handleEvent();
	}

	/**
	 * Create the Event
	 */
	public function handleEvent() {

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
