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
			$result = 'Total Payment: $' . number_format($payment_amount, 2) . '<br>';
			$result .= 'Principal: $' . number_format($payment['principal'], 2) . '<br>';
			$result .= 'Interest: $' . number_format($payment['interest'], 2) . '<br>';
			$result .= 'Other: $' . number_format($fee_amount, 2) . '<br>';
			$result .= 'Remaining Principal: $' . number_format($payment['remaining_principal'], 2);

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
			$result = 'Total Payments: ' . number_format($payoff['number_of_payments']) . '<br>';
			$result .= 'Total Months: ' . number_format($payoff['number_of_months']) . '<br>';
			$result .= 'Final Payment Amount: $' . number_format($payoff['final_payment_amount'], 2) . '<br>';
			$result .= 'Total Interest: $' . number_format($payoff['total_interest'], 2) . '<br>';

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
			$result = 'Payment Amount: $' . number_format($payment, 2);

			echo \json_encode([ 'success' => true, 'result' => $result ]);
		}
	}