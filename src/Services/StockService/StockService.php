<?php

namespace App\Services\StockService;

use App\Ledger\Ledger;
use App\Services\StockService\Events\OperationEvent;
use App\Services\StockService\Events\TaxEvent;
use App\Services\StockService\Internal\OperationHandler;
use App\Services\StockService\Internal\TaxHandler;

class StockService {

	private Ledger $ledger;

	public function __construct() {
		$this->ledger = new Ledger();
	}

	public function handleOperations(OperationEvent... $operations): void {
		foreach ($operations as $operation) {
			(new TaxHandler($this->ledger))->handleOnBuy($operation);
			(new TaxHandler($this->ledger))->handleOnSell($operation);
			(new OperationHandler($this->ledger))->handle($operation);
		}
	}

	/**
	 * @return TaxEvent[]
	 */
	public function getTaxEvents(): array {
		$tax_events_from_ledger = $this->ledger->getByEvent('paid_tax');

		$tax_list = [];
		foreach ($tax_events_from_ledger as $tax_event) {
			$tax_list[] = new TaxEvent($tax_event['amount']);
		}
		return $tax_list;
	}

}
