var attachSubmit = function(submitBtnSelector) {
	// Attach the listener to the submit buttons.
	$(submitBtnSelector).on('click', function() {
		// Validate the form before posting to the server.
		$form = $('#' + $(this).data('form'));
		var isValid = $form[0].checkValidity();
		$form.addClass('was-validated');
		// If validation failed, don't submit the form.
		if(isValid === false) {
			return false;
		}

		// If we made it past the validation, lets post the form fields to the server.
		$.post($(this).data('url'), $form.serialize(), function(response) {
			// If there was no response, display an error message.
			if(typeof response === 'undefined' || typeof response.result === 'undefined' || response.result === '') {
				$('#info_modal .modal-body').html('There was an issue calculating the result.');
			} else {
				$('#info_modal .modal-body').html(response.result);
			}

			// Display the result.
			$('#info_modal').modal('show');
		}, 'json');
	});
};