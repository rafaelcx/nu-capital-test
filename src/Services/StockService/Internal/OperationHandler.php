<?php

namespace App\Services\StockService\Internal;

use App\Ledger\Ledger;
use App\Services\StockService\Events\OperationEvent;

class OperationHandler {

	private Ledger $ledger;

	public function __construct(Ledger $ledger) {
		$this->ledger = $ledger;
	}

	public function handle(OperationEvent $op): void {
		$this->createOperationEvent($op);
	}

	private function createOperationEvent(OperationEvent $op): void {
		$this->ledger->createEvent($op->operation_type, [
			'unit_cost' => $op->unit_cost,
			'quantity' => $op->quantity,
		]);
	}

}
