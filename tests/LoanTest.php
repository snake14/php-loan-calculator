<?php 
	declare(strict_types=1);

	use PHPUnit\Framework\TestCase;
	use \Helpers\Loan;
	use \Classes\DisplayableError;
	
	final class LoanTest extends TestCase {
		public function testCalculatePaymentError() {
			try {
				Loan::calculatePayment(300000, 1800, 500, 3.25, 12345);
				$this->fail('This should have failed with an invalid payment frequency.');
			} catch (DisplayableError $de) {
				$this->assertStringContainsString('The provided payment frequency is not a valid option.', $de->getMessage());
			}
		}

		public function testCalculatePayment(): void {
			$result = Loan::calculatePayment(300000, 1800, 500, 3.25, Loan::MONTHLY_PAYMENT);
			$this->assertNotEmpty($result);
			$this->assertSame(487.5, $result['principal']);
			$this->assertSame(812.5, $result['interest']);
			$this->assertSame(500.0, $result['other']);
			$this->assertSame(299512.5, $result['remaining_principal']);
		}

		public function testCalculatePaymentLowerAmount(): void {
			$result = Loan::calculatePayment(250000, 1200, 200, 4.5, Loan::MONTHLY_PAYMENT);
			$this->assertNotEmpty($result);
			$this->assertSame(62.5, $result['principal']);
			$this->assertSame(937.5, $result['interest']);
			$this->assertSame(200.0, $result['other']);
			$this->assertSame(249937.5, $result['remaining_principal']);
		}

		public function testEstimatePaymentAmountError() {
			try {
				Loan::estimatePaymentAmount(300000, 500, 3.25, 12345, 30);
				$this->fail('This should have failed with an invalid unit type.');
			} catch (DisplayableError $de) {
				$this->assertStringContainsString('The provided term unit type is not a valid option.', $de->getMessage());
			}
		}

		public function testEstimatePaymentAmount(): void {
			$result = Loan::estimatePaymentAmount(300000, 500, 3.25, Loan::UNIT_TYPE_YEAR, 30);
			$this->assertNotEmpty($result);
			$this->assertSame('1,805.62', number_format($result, 2));
		}

		public function testEstimatePaymentAmountLowerAmount(): void {
			$result = Loan::estimatePaymentAmount(250000, 200, 4.5, Loan::UNIT_TYPE_YEAR, 30);
			$this->assertNotEmpty($result);
			$this->assertSame('1,466.71', number_format($result, 2));
		}

		public function testEstimatePaymentAmountWithMonths(): void {
			$result = Loan::estimatePaymentAmount(300000, 500, 3.25, Loan::UNIT_TYPE_MONTH, 360);
			$this->assertNotEmpty($result);
			$this->assertSame('1,805.62', number_format($result, 2));
		}

		public function testCalculatePayoffTimeError() {
			try {
				Loan::calculatePayoffTime(300000, 1800, 500, 3.25, 12345);
				$this->fail('This should have failed with an invalid payment frequency.');
			} catch (DisplayableError $de) {
				$this->assertStringContainsString('The provided payment frequency is not a valid option.', $de->getMessage());
			}
		}

		public function testCalculatePayoffTime() {
			$result = Loan::calculatePayoffTime(300000, 1805.62, 500, 3.25, Loan::MONTHLY_PAYMENT);
			$this->assertNotEmpty($result);
			$this->assertSame(360, $result['number_of_payments']);
			$this->assertSame(360, $result['number_of_months']);
			$this->assertSame('1,805', number_format($result['final_payment_amount']));
			$this->assertSame('170,022.57', number_format($result['total_interest'], 2));
		}

		public function testCalculatePayoffTimeLowerAmount() {
			$result = Loan::calculatePayoffTime(250000, 1466.71, 200, 4.5, Loan::MONTHLY_PAYMENT);
			$this->assertNotEmpty($result);
			$this->assertSame(360, $result['number_of_payments']);
			$this->assertSame(360, $result['number_of_months']);
			$this->assertSame('1,469', number_format($result['final_payment_amount']));
			$this->assertSame('206,018.09', number_format($result['total_interest'], 2));
		}
	}