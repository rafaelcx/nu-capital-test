<?php

namespace App\Controllers;

use App\Output\OutputResolver;

class MainController {

	public function execute(string $input): void {
		OutputResolver::resolve()->emmit($input);
	}

}
