<?php

namespace Test\Support\Output;

use App\Output\Output;
use App\Output\OutputResolver;

class OutputResolverForTests extends OutputResolver {

	public static function override(Output $output): void {
		self::$output = $output;
	}

}
