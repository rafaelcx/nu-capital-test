<?php

namespace App\Services\StockService\Events;

readonly class TaxEvent implements \JsonSerializable {

	public function __construct(
		public float $amount,
	) {}

	public function jsonSerialize(): mixed {
		$formatted_amount = number_format($this->amount, decimals: 2, decimal_separator: '.', thousands_separator: '');
		return [
			'tax' => $formatted_amount,
		];
	}

}
