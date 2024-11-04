<?php

use App\Controllers\MainController;
use PHPUnit\Framework\TestCase;
use Test\Support\Output\OutputForTests;
use Test\Support\Output\OutputResolverForTests;

class MainControllerTest extends TestCase {

	private OutputForTests $output;

	/** @before */
	public function setUpOutputBufferForTests(): void {
		$this->output = new OutputForTests();
		OutputResolverForTests::override($this->output);
	}

	public function provideUseCasesForTests(): iterable {
		yield 'case_1' => [
			'input' => [
				'{"operation":"buy", "unit-cost":10.00, "quantity": 100}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":15.00, "quantity": 50}', // No tax when total value less than R$ 20,000
				'{"operation":"sell", "unit-cost":15.00, "quantity": 50}', // No tax when total value less than R$ 20,000
			],
			'expected_output' => '[{"tax":"0.00"},{"tax":"0.00"},{"tax":"0.00"}]' . "\n",
		];

		yield 'case_2' => [
			'input' => [
				'{"operation":"buy", "unit-cost":10.00, "quantity": 10000}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":20.00, "quantity": 5000}', // Profit of R$ 50,000: 20% of the profit amounts to R$ 10,000, with no previous loss
				'{"operation":"sell", "unit-cost":5.00, "quantity": 5000}', // Loss of R$ 25,000: no tax is due
			],
			'expected_output' => '[{"tax":"0.00"},{"tax":"10000.00"},{"tax":"0.00"}]' . "\n",
		];

		yield 'case_3' => [
			'input' => [
				'{"operation":"buy", "unit-cost":10.00, "quantity": 10000}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":5.00, "quantity": 5000}', // Loss of R$ 25,000: no tax is due
				'{"operation":"sell", "unit-cost":20.00, "quantity": 3000}', // Profit of R$ 30,000: Must deduct a loss of R$ 25,000 and pays 20% tax on R$ 5,000 (R$ 1,000)
			],
			'expected_output' => '[{"tax":"0.00"},{"tax":"0.00"},{"tax":"1000.00"}]' . "\n",
		];

		yield 'case_4' => [
			'input' => [
				'{"operation":"buy", "unit-cost":10.00, "quantity": 10000}', // No tax when buying stocks
				'{"operation":"buy", "unit-cost":25.00, "quantity": 5000}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":15.00, "quantity": 10000}', // Considering a weighted average price of R$ 15 ((10×10000 + 25×5000) ÷ 15000), there was no profit or loss
			],
			'expected_output' => '[{"tax":"0.00"},{"tax":"0.00"},{"tax":"0.00"}]' . "\n",
		];

		yield 'case_5' => [
			'input' => [
				'{"operation":"buy", "unit-cost":10.00, "quantity": 10000}', // No tax when buying stocks
				'{"operation":"buy", "unit-cost":25.00, "quantity": 5000}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":15.00, "quantity": 10000}', // Considering a weighted average price of R$ 15, there was no profit or loss
				'{"operation":"sell", "unit-cost":25.00, "quantity": 5000}', // Considering a weighted average price of R$ 15 with a profit of R$ 50,000: pays 20% tax on R$ 50,000 (R$ 10,000)
			],
			'expected_output' => '[{"tax":"0.00"},{"tax":"0.00"},{"tax":"0.00"},{"tax":"10000.00"}]' . "\n",
		];

		yield 'case_6' => [
			'input' => [
				'{"operation":"buy", "unit-cost":10.00, "quantity": 10000}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":2.00, "quantity": 5000}', // Loss of R$ 40,000: total value is less than R$ 20,000, but we must deduct the loss regardless
				'{"operation":"sell", "unit-cost":20.00, "quantity": 2000}', // Profit of R$ 20,000: if you deduct the loss, your profit is zero. You still have R$ 20,000 in losses to deduct from future profits
				'{"operation":"sell", "unit-cost":20.00, "quantity": 2000}', // Profit of R$ 20,000: if you deduct the loss, your profit is zero. Now there is no remaining loss to deduct from future profits
				'{"operation":"sell", "unit-cost":25.00, "quantity": 1000}', // Profit of R$ 15,000 with no losses: pays 20% tax on R$ 15,000 (R$ 3,000)
			],
			'expected_output' => '[{"tax":"0.00"},{"tax":"0.00"},{"tax":"0.00"},{"tax":"0.00"},{"tax":"3000.00"}]' . "\n",
		];

		yield 'case_7' => [
			'input' => [
				'{"operation":"buy", "unit-cost":10.00, "quantity": 10000}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":2.00, "quantity": 5000}', // Loss of R$ 40,000: total value is less than R$ 20,000, but we deduct the losses regardless.
				'{"operation":"sell", "unit-cost":20.00, "quantity": 2000}', // Profit of R$ 20,000: if you deduct the loss, your profit is zero. You still have R$ 20,000 in losses to deduct from future profits
				'{"operation":"sell", "unit-cost":20.00, "quantity": 2000}', // Profit of R$ 20,000: if you deduct the loss, your profit is zero. Now there is no remaining loss to deduct from future profits
				'{"operation":"sell", "unit-cost":25.00, "quantity": 1000}', // Profit of R$ 15,000 with no losses: pays 20% tax on R$ 15,000 (R$ 3,000)
				'{"operation":"buy", "unit-cost":20.00, "quantity": 10000}', // All previous shares were sold. Buying new shares changes the weighted average to the price paid for them (R$ 20)
				'{"operation":"sell", "unit-cost":15.00, "quantity": 5000}', // Loss of R$ 25,000
				'{"operation":"sell", "unit-cost":30.00, "quantity": 4350}', // Profit of R$ 43,500: if you deduct the loss of R$ 25,000, R$ 18,500 of profit remains. Pays 20% tax on R$ 18,500 (R$ 3,700)
				'{"operation":"sell", "unit-cost":30.00, "quantity": 650}', // Profit of R$ 6,500, with no loss to deduct, but the total value is less than R$ 20,000, so no tax is due
			],
			'expected_output' => '[{"tax":"0.00"},{"tax":"0.00"},{"tax":"0.00"},{"tax":"0.00"},{"tax":"3000.00"},{"tax":"0.00"},{"tax":"0.00"},{"tax":"3700.00"},{"tax":"0.00"}]' . "\n",
		];

		yield 'case_8' => [
			'input' => [
				'{"operation":"buy", "unit-cost":10.00, "quantity": 10000}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":50.00, "quantity": 10000}', // Profit of R$ 400,000: pays 20% tax on R$ 400,000 (R$ 80,000)
				'{"operation":"buy", "unit-cost":20.00, "quantity": 10000}', // No tax when buying stocks
				'{"operation":"sell", "unit-cost":50.00, "quantity": 10000}', // Profit of R$ 300,000: pays 20% tax on R$ 300,000 (R$ 60,000)
			],
			'expected_output' => '[{"tax":"0.00"},{"tax":"80000.00"},{"tax":"0.00"},{"tax":"60000.00"}]' . "\n",
		];
	}

	/**
	 * @dataProvider provideUseCasesForTests
	 */
	public function testController(array $input, string $expected_output): void {
		$input = $this->formatInput($input);
		(new MainController)->execute($input);
		$this->assertEquals($expected_output, $this->output->getOutputContent());
	}

	public function testController_WhenMoreThanOneOperationList(): void {
		$input_one = '[
			{"operation":"buy", "unit-cost":10.00, "quantity": 100},
			{"operation":"sell", "unit-cost":15.00, "quantity": 50},
			{"operation":"sell", "unit-cost":15.00, "quantity": 50}
		]' . "\n";

		$input_two = '[
			{"operation":"buy", "unit-cost":10.00, "quantity": 10000},
			{"operation":"sell", "unit-cost":20.00, "quantity": 5000},
			{"operation":"sell", "unit-cost":5.00, "quantity": 5000}
		]' . "\n";

		$expected_output =
			'[{"tax":"0.00"},{"tax":"0.00"},{"tax":"0.00"}]' . "\n" .
			'[{"tax":"0.00"},{"tax":"10000.00"},{"tax":"0.00"}]' . "\n";

		(new MainController)->execute($input_one);
		(new MainController)->execute($input_two);
		$this->assertEquals($expected_output, $this->output->getOutputContent());
	}

	private function formatInput(array $input): string {
		$input = implode(',', $input);
		return "[{$input}]" . "\n";
	}

}
