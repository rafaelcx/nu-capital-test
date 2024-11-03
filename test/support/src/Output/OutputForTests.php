<?php

namespace Test\Support\Output;

use App\Output\Output;

class OutputForTests implements Output {

	private string $output_buffer = '';

	public function emmit(string $content): void {
		$this->output_buffer .= $content;
	}

	public function getOutputContent(): string {
		return $this->output_buffer;
	}

}
