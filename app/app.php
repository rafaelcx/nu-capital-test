<?php

use App\Controllers\MainController;

require __DIR__ . '/../vendor/autoload.php';

while ($input = fgets(STDIN)) {
	(new MainController())->execute($input);
}
