<?php
	namespace Classes;

	/**
	 * Let's extend Exception so that we can use a specific type of error to indicate that the error message is safe to
	 * display to the user.
	 */
	class DisplayableError extends \Exception {}