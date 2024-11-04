<?php

namespace App\Services\StockService;

use App\Ledger\Ledger;
use App\Services\StockService\Events\OperationEvent;
use App\Services\StockService\Events\TaxEvent;

class StockService {

	private Ledger $ledger;

	public function __construct() {
		$this->ledger = new Ledger();
	}

	public function handleOperations(OperationEvent... $operations): void {
		foreach ($operations as $operation) {
			self::processOperation($operation);
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

	private function processOperation(OperationEvent $op): void {
		match ($op->operation_type) {
			'buy' => $this->handleBuyOperation($op),
			'sell' => $this->handleSellOperation($op),
		};
	}

	private function handleBuyOperation(OperationEvent $op): void {
		$this->createOperationEvent($op);
		$this->createPaidTaxEvent(0.00);
	}

	private function handleSellOperation(OperationEvent $op): void {
		$quantity = 0;
		$weighted_avg_price = 0.00;
		$aggregated_profit = 0.00;

		foreach ($this->ledger->getAllEvents() as $event) {
			if ($event['event'] === 'buy') {
				$weighted_avg_price = $this->calculateWeightedAveragePrice($event, $quantity, $weighted_avg_price);
				$quantity += $event['quantity'];
				continue;
			}

			if ($event['event'] === 'sell') {
				$current_profit = $this->calculateCurrentOperationProfit($event, $weighted_avg_price);
				if ($current_profit < 0 || $aggregated_profit < 0) {
					$aggregated_profit += $current_profit;
				}
				$quantity -= $event['quantity'];
				continue;
			}
		}

		$current_operation_profit = ($op->quantity * $op->unit_cost) - ($op->quantity * $weighted_avg_price);
		$aggregated_profit = $aggregated_profit + $current_operation_profit;

		if (
			true
			&& $aggregated_profit > 0
			&& ($op->quantity * $op->unit_cost > 20000)
		) {
			$this->createOperationEvent($op);
			$this->createPaidTaxEvent($aggregated_profit * 0.20);
			return;
		}

		$this->createOperationEvent($op);
		$this->createPaidTaxEvent(0.00);
	}

	private function calculateWeightedAveragePrice(array $event, int $quantity, float $weighted_avg_price): float {
		return (($quantity * $weighted_avg_price) +
			($event['quantity'] * $event['unit_cost'])) /
			($quantity + $event['quantity']);
	}

	private function calculateCurrentOperationProfit(array $event, float $weighted_avg_price): float {
		return ($event['quantity'] * $event['unit_cost']) - ($event['quantity'] * $weighted_avg_price);
	}

	private function createOperationEvent(OperationEvent $op): void {
		$this->ledger->createEvent($op->operation_type, [
			'unit_cost' => $op->unit_cost,
			'quantity' => $op->quantity,
		]);
	}

	public function createPaidTaxEvent(float $amount): void {
		$this->ledger->createEvent('paid_tax', [
			'amount' => $amount
		]);
	}

}
