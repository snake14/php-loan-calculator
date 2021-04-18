<?php
	require __DIR__ . '/../vendor/autoload.php';
	$f3 = \Base::instance();
	$f3->set('AUTOLOAD', __DIR__ . '/../src/');
	$f3->set('UI', __DIR__ . '/../views/');
	// Define the routes used by the web service.
	$f3->route('GET /', 'Controllers\LoanCalculator->getMainPage');
	$f3->route('POST /calculate-payment-breakdown', 'Controllers\LoanCalculator->calculatePaymentBreakdown');
	$f3->route('POST /estimate-remaining', 'Controllers\LoanCalculator->estimateRemaining');
	$f3->route('POST /estimate-payment', 'Controllers\LoanCalculator->estimatePayment');
	$f3->run();
?>