<?php

namespace App\Output;

class StandardOutput implements Output {

	public function emmit(string $content): void {
		echo $content;
	}

}
