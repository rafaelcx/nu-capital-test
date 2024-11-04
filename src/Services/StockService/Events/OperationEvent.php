<?php

namespace App\Services\StockService\Events;

readonly class OperationEvent {

	public function __construct(
		public string $operation_type,
		public string $unit_cost,
		public string $quantity,
	) {}

}
