<?php

namespace Cli\Test;

/**
 * Test class for Cli\Execute class
 */
class ExecuteTest extends \PHPUnit_Framework_TestCase
{
	protected $_cmd;
	protected $_input;

	public function setUp(){
        $this->_cmd = \Cli\Execute::singleton();
		$line[] = '<?php';
		$line[] = 'echo "test" ."123";';
		$this->_input = implode(PHP_EOL, $line);
	}

	public function testExecuteSimple(){
        $success = $this->_cmd->execute('echo "123"', __FILE__, __LINE__, $output);
        $this->assertTrue($success);
        $this->assertEquals(trim($output),'123');

        $success = $this->_cmd->execute('cd wrongDirsTest123', __FILE__, __LINE__, $output,$err);
        $this->assertInternalType('int',$success);
	}

	private function execPhp($input){
        $success = $this->_cmd->execute('php', __FILE__, __LINE__, $output, $err, $input);
        $this->assertTrue($success);
        $this->assertEquals(trim($output),"test123");
	}

	public function testExecuteWithStdinAsString(){
		$this->execPhp($this->_input);
	}

	public function testExecuteWithStdinAsStream(){
		$temp = tmpfile();
		fwrite($temp, $this->_input);
		fseek($temp, 0);
		$this->execPhp($temp);
		fclose($temp);
	}

}