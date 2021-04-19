<?php
	namespace Helpers;

	use \Classes\DisplayableError;

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
	
		const UNIT_TYPE_YEAR = 12;
		const UNIT_TYPE_MONTH = 1;
		// This is for specifying the duration of the loan, like a 30 or 15 year mortgage.
		const UNIT_TYPES = [
			[ 'name' => 'Years', 'value' => self::UNIT_TYPE_YEAR ],
			[ 'name' => 'Months', 'value' => self::UNIT_TYPE_MONTH ],
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
			if(!in_array($pay_frequency, array_column(self::PAYMENT_FREQUENCIES, 'value'))) {
				throw new DisplayableError("The provided payment frequency is not a valid option.");
			}

			$interest = ($loan_amount * ($interest_rate / 100)) / $pay_frequency;
			$principal = $total_pay_amount - $fee_amount;
			$principal = $principal - $interest;
			$remaining_principal = $loan_amount - $principal;

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
		 * @param integer $term_type is the unit of time used to calculate the lifetime of the loan, like year or month.
		 * @param integer $term_num is the number of units of time used to calculate the lifetime of the loan, like 30 or 15.
		 * @return float is the float dollar amount of the total monthly payment of the loan.
		 */
		public static function estimatePaymentAmount(float $loan_amount, float $fee_amount, float $interest_rate, int $term_type, int $term_num) : float {
			if(!in_array($term_type, array_column(self::UNIT_TYPES, 'value'))) {
				throw new DisplayableError("The provided term unit type is not a valid option.");
			}
			
			$total_months = $term_num * $term_type;
			$monthly_rate = ($interest_rate / 100) / 12;
			$dividend = $loan_amount * ($monthly_rate * pow((1 + $monthly_rate), $total_months));
			$divisor = pow((1 + $monthly_rate), $total_months) - 1;
			$monthly_payment = ($dividend / $divisor);
			$monthly_payment = $monthly_payment + $fee_amount;

			return $monthly_payment;
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
			$total_interest = $total_interest;
			$final_payment_amt = $remaining_principal + $fee_amount + $interest;
			$payment_count++;

			// Calculate the number of months based on the number of payments and the payment frequency;
			$months = $pay_frequency == self::SEMI_MONTHLY_PAYMENT ? $payment_count / 2 : ($pay_frequency == self::WEEKLY_PAYMENT ? $payment_count / 4 : $payment_count);

			return [ 'number_of_payments' => $payment_count, 'number_of_months' => $months, 'final_payment_amount' => $final_payment_amt, 'total_interest' => $total_interest ];
		}
	}
?>