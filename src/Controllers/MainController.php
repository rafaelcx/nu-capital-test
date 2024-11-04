<?php

namespace App\Controllers;

use App\Output\OutputResolver;
use App\Services\StockService\Events\OperationEvent;
use App\Services\StockService\StockService;

class MainController {

	public function execute(string $input): void {
		$operation_list = $this->decodeInput($input);

		$stock_service = new StockService();
		$stock_service->handleOperations(...$operation_list);

		$this->emitResult($stock_service);
	}

	/**
	 * @return OperationEvent[]
	 */
	private function decodeInput(string $input): array {
		$input_as_obj = json_decode($input, associative: true);

		$operation_list = [];
		foreach ($input_as_obj as $operation) {
			$operation_list[] = new OperationEvent(
				$operation['operation'],
				$operation['unit-cost'],
				$operation['quantity']
			);
		}
		return $operation_list;
	}

	private function emitResult(StockService $stock_service): void {
		$result_as_str = json_encode($stock_service->getTaxEvents());
		OutputResolver::resolve()->emmit($result_as_str . "\n");
	}

}
