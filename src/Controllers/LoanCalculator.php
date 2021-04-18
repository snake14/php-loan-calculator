<?php
    namespace Controllers;

	use \Helpers\Loan;

    class LoanCalculator {
        public function getMainPage($f3, $params) {
			$f3->set('frequencies', Loan::PAYMENT_FREQUENCIES);
			$f3->set('unitTypes', Loan::UNIT_TYPES);
			echo \Template::instance()->render('main.htm');
        }

		public function calculatePaymentBreakdown($f3, $params) {
			$loan_amount = !empty($_POST['loan_amt']) ? floatval($_POST['loan_amt']) : 0;
			$payment_amount = !empty($_POST['total_payment_amt']) ? floatval($_POST['total_payment_amt']) : 0;
			$fee_amount = !empty($_POST['fee_amt']) ? floatval($_POST['fee_amt']) : 0;
			$rate = !empty($_POST['interest_rate']) ? floatval($_POST['interest_rate']) : 0;
			$payment_frequency = !empty($_POST['frequency']) ? $_POST['frequency'] : Loan::MONTHLY_PAYMENT;
			$result = '';

			$payment = Loan::calculatePayment($loan_amount, $payment_amount, $fee_amount, $rate, $payment_frequency);
			$result = "Total Payment: \${$payment_amount}<br>";
			$result .= "Principal: \${$payment['principal']}<br>";
			$result .= "Interest: \${$payment['interest']}<br>";
			$result .= "Other: \${$fee_amount}<br>";
			$result .= "Remaining Principal: \${$payment['remaining_principal']}";

			echo \json_encode([ 'success' => true, 'result' => $result ]);
		}

		public function estimateRemaining($f3, $params) {
			$loan_amount = !empty($_POST['loan_amt']) ? floatval($_POST['loan_amt']) : 0;
			$payment_amount = !empty($_POST['total_payment_amt']) ? floatval($_POST['total_payment_amt']) : 0;
			$fee_amount = !empty($_POST['fee_amt']) ? floatval($_POST['fee_amt']) : 0;
			$rate = !empty($_POST['interest_rate']) ? floatval($_POST['interest_rate']) : 0;
			$frequency = !empty($_POST['frequency']) ? $_POST['frequency'] : Loan::MONTHLY_PAYMENT;
			$result = '';

			$payoff = \Helpers\Loan::calculatePayoffTime($loan_amount, $payment_amount, $fee_amount, $rate, $frequency);
			$result = "Total Payments: {$payoff['number_of_payments']}<br>";
			$result .= "Total Months: {$payoff['number_of_months']}<br>";
			$result .= "Final Payment Amount: \${$payoff['final_payment_amount']}<br>";
			$result .= "Total Interest: \${$payoff['total_interest']}<br>";

			echo \json_encode([ 'success' => true, 'result' => $result ]);
		}

		public function estimatePayment($f3, $params) {
			$loan_amount = !empty($_POST['loan_amt']) ? floatval($_POST['loan_amt']) : 0;
			$fee_amount = !empty($_POST['fee_amt']) ? floatval($_POST['fee_amt']) : 0;
			$rate = !empty($_POST['interest_rate']) ? floatval($_POST['interest_rate']) : 0;
			$unit_type = !empty($_POST['unit_type']) ? $_POST['unit_type'] : 'YEAR';
			$unit_amt = !empty($_POST['unit_amt']) ? intval($_POST['unit_amt']) : 0;
			$result = '';

			$payment = Loan::estimatePaymentAmount($loan_amount, $fee_amount, $rate, $unit_type, $unit_amt);
			$result = "Payment Amount: \${$payment}";

			echo \json_encode([ 'success' => true, 'result' => $result ]);
		}
	}