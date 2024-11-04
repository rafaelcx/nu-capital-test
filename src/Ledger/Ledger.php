<?php

namespace App\Ledger;

class Ledger {

	private array $ledger;

	public function __construct() {
		$this->ledger = [];
	}

	public function createEvent(string $event_name, array $event_values): void {
		$ledger_entry = array_merge(['event' => $event_name], $event_values);
		$this->ledger[] = $ledger_entry;
	}

	public function getAllEvents(): array {
		return $this->ledger;
	}

	public function getByEvent(string $event_name): array {
		$filter = fn ($ledger_entry) => $ledger_entry['event'] === $event_name;
		return array_filter($this->ledger, $filter);
	}

}
