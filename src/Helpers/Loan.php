<?php
	namespace Helpers;

	class Loan {
		// Number of payments per year.
		const MONTHLY_PAYMENT = 12;
		const SEMI_MONTHLY_PAYMENT = 24;
		const WEEKLY_PAYMENT = 52;

		const PAYMENT_FREQUENCIES = [
			[ 'name' => 'Monthly', 'value' => self::MONTHLY_PAYMENT ],
			[ 'name' => 'Semi-Monthly', 'value' => self::SEMI_MONTHLY_PAYMENT ],
			[ 'name' => 'Weekly', 'value' => self::WEEKLY_PAYMENT ],
		];
	
		// This is for specifying the duration of the loan, like a 30 or 15 year mortgage.
		const UNIT_TYPES = [
			[ 'name' => 'Years', 'value' => 'YEAR' ],
			[ 'name' => 'Months', 'value' => 'MONTH' ],
		];

		/**
		 * Calculate the breakdown of a payment based on some key information. The is handy for recursively calculating
		 * all of the remaining payments of a loan.
		 * 
		 * @param float $loan_amount is the total amount of principal remaining on the loan.
		 * @param float $total_pay_amount is the total payment amount (principal, interest, other fees).
		 * @param float $fee_amount is to account for any other fees, like mortgage insurance or escrow.
		 * @param float $interest_rate is the annual interest rate for the loan.
		 * @param integer $pay_frequency is how frequently the payments are made (montly, weekly, ...).
		 * @return array containing payment breakdown, like principal, interesti, fees, and remaining principal.
		 */
		public static function calculatePayment(float $loan_amount, float $total_pay_amount, float $fee_amount, float $interest_rate, int $pay_frequency): array {
			$interest = number_format(($loan_amount * ($interest_rate / 100)) / $pay_frequency, 2);
			$principal = $total_pay_amount - $fee_amount;
			$principal = $principal - floatval(str_replace(',', '', $interest));
			$remaining_principal = number_format($loan_amount - $principal, 2);

			return [ 'principal' => $principal, 'interest' => $interest, 'other' => $fee_amount, 'remaining_principal' => $remaining_principal ];
		}
		
		/**
		 * Calculate the total payment for a single payment period.
		 * M = P [ i(1 + i)^n ] / [ (1 + i)^n – 1]
		 * P = principal loan amount (350000)
		 * i = monthly interest rate (0.04 / 12 = 0.0033)
		 * n = number of months required to repay the loan (30 x 12 = 360)
		 * @param float $loan_amount is the total amount of principal remaining on the loan.
		 * @param float $fee_amount is to account for any other fees, like mortgage insurance or escrow.
		 * @param float $interest_rate is the annual interest rate for the loan.
		 * @param string $term_type is the unit of time used to calculate the lifetime of the loan, like year or month.
		 * @param integer $term_num is the number of units of time used to calculate the lifetime of the loan, like 30 or 15.
		 * @return string is the formatted dollar amount of the total monthly payment of the loan.
		 */
		public static function estimatePaymentAmount(float $loan_amount, float $fee_amount, float $interest_rate, string $term_type, int $term_num) : string {
			if($term_type == 'YEAR') {
				$total_months = $term_num * 12;
				$monthly_rate = ($interest_rate / 100) / 12;
				$dividend = $loan_amount * ($monthly_rate * pow((1 + $monthly_rate), $total_months));
				$divisor = pow((1 + $monthly_rate), $total_months) - 1;
				$monthly_payment = ($dividend / $divisor);
				$monthly_payment = number_format($monthly_payment + $fee_amount, 2);

				return $monthly_payment;
			}
		}

		/**
		 * Calculate how long it will take to pay off a loan based on the size of the payment, frequency, and interest rate.
		 * 
		 * @param float $loan_amount is the total amount of principal remaining on the loan.
		 * @param float $total_pay_amount is the total payment amount (principal, interest, other fees).
		 * @param float $fee_amount is to account for any other fees, like mortgage insurance or escrow.
		 * @param float $interest_rate is the annual interest rate for the loan.
		 * @param integer $pay_frequency is how frequently the payments are made (montly, weekly, ...).
		 * @return array contains some of the key calculated information, like number of remaining payments and months.
		 */
		public static function calculatePayoffTime(float $loan_amount, float $total_pay_amount, float $fee_amount, float $interest_rate, int $pay_frequency): array {
			$remaining_principal = $loan_amount;
			$payment_count = $months = $final_payment_amt = 0;
			$total_interest = 0;
			// Make the standard monthly payment until the remaining principal is less than the usual monthly payment.
			while(($remaining_principal + $fee_amount) >= $total_pay_amount) {
				$payment = self::calculatePayment($remaining_principal, $total_pay_amount, $fee_amount, $interest_rate, $pay_frequency);
				$remaining_principal = floatval(str_replace(',', '', $payment['remaining_principal']));
				$total_interest += $payment['interest'];
				$payment_count++;
			}

			// Calculate the interest for the final payment.
			$interest = ($remaining_principal * ($interest_rate / 100)) / $pay_frequency;
			$total_interest += $interest;
			$total_interest = number_format($total_interest, 2);
			$final_payment_amt = number_format($remaining_principal + $fee_amount + $interest);
			$payment_count++;

			// Calculate the number of months based on the number of payments and the payment frequency;
			$months = $pay_frequency == self::SEMI_MONTHLY_PAYMENT ? $payment_count / 2 : ($pay_frequency == self::WEEKLY_PAYMENT ? $payment_count / 4 : $payment_count);

			return [ 'number_of_payments' => $payment_count, 'number_of_months' => $months, 'final_payment_amount' => $final_payment_amt, 'total_interest' => $total_interest ];
		}
	}
?>