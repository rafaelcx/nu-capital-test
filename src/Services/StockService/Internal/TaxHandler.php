<?php

namespace App\Services\StockService\Internal;

use App\Ledger\Ledger;
use App\Services\StockService\Events\OperationEvent;

class TaxHandler {

	private Ledger $ledger;

	public function __construct(Ledger $ledger) {
		$this->ledger = $ledger;
	}

	public function handleOnBuy(OperationEvent $op): void {
		if ($op->operation_type !== 'buy') {
			return;
		}
		$this->createPaidTaxEvent(0.00);
	}

	public function handleOnSell(OperationEvent $op): void {
		if ($op->operation_type !== 'sell') {
			return;
		}

		$unit_quantity = 0;
		$unit_avg_price = 0.00;
		$aggregated_profit = 0.00;

		foreach ($this->ledger->getAllEvents() as $event) {
			if ($event['event'] === 'buy') {
				$unit_avg_price = $this->calculateWeightedAveragePrice($event, $unit_quantity, $unit_avg_price);
				$unit_quantity += $event['quantity'];
				continue;
			}

			if ($event['event'] === 'sell') {
				$current_profit = $this->calculateCurrentOperationProfit($event, $unit_avg_price);
				if ($current_profit < 0 || $aggregated_profit < 0) {
					$aggregated_profit += $current_profit;
				}
				$unit_quantity -= $event['quantity'];
				continue;
			}
		}

		$current_operation_profit = ($op->quantity * $op->unit_cost) - ($op->quantity * $unit_avg_price);
		$aggregated_profit = $aggregated_profit + $current_operation_profit;

		if (
			true
			&& $aggregated_profit > 0
			&& ($op->quantity * $op->unit_cost > 20000)
		) {
			$this->createPaidTaxEvent($aggregated_profit * 0.20);
			return;
		}

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

	private function createPaidTaxEvent(float $amount): void {
		$this->ledger->createEvent('paid_tax', [
			'amount' => $amount
		]);
	}

}
