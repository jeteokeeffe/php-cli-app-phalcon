<?php

/**
 * Example of a Task/Cli application
 * 
 * @author Jete O'Keeffe
 * @version 1.0
 */

namespace Tasks;

use \Cli\Output as Output;


class ExampleTask extends \Phalcon\Cli\Task {

	public function test1Action() {
		Output::stdout("Hello World!");
	}

	public function mainAction() {
		Output::stdout("Main Action");
	}
}
