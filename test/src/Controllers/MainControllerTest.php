<?php

use App\Controllers\MainController;
use PHPUnit\Framework\TestCase;
use Test\Support\Output\OutputForTests;
use Test\Support\Output\OutputResolverForTests;

class MainControllerTest extends TestCase {

	private OutputForTests $output;

	/** @before */
	public function setUpOutputBufferForTests(): void {
		$this->output = new OutputForTests();
		OutputResolverForTests::override($this->output);
	}

	public function testMainController(): void {
		$controller = new MainController();
		$controller->execute('Input');
		$this->assertEquals('Input', $this->output->getOutputContent());
	}

}
