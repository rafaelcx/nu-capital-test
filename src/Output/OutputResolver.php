<?php

namespace App\Output;

class OutputResolver {

	protected static ?Output $output = null;

	public static function resolve(): Output {
		self::$output ??= new StandardOutput();
		return self::$output;
	}

}
