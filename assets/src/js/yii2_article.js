$(document).ready(function() {

	/**
	 * Shows or hides form parts depending on the value of a type. The form parts
	 * which should be dynamically shown or hidden must have an attribute `data-types`
	 * which contains the type values to show them for in json-format.
	 *
	 * @param form the form element
	 * @param selectedType the selected type
	 */
	function showRelevantFormParts(form, selectedType) {
		//assert int
		if (typeof selectedType === 'string' || selectedType instanceof String) {
			selectedType = parseInt(selectedType);
		}

		//fetch relevant form parts and show or hide them
		var relevantItems = form.find('[data-types]');
		for (var i=0; i<relevantItems.length; i++) {
			var curItem = $(relevantItems[i]);
			var shownWithTypes = JSON.parse(curItem.attr('data-types'));

			if ($.inArray(selectedType, shownWithTypes) !== -1) {
				curItem.show();
			} else {
				curItem.hide();
			}
		}
	}

	//initial call and attachment of event listener
	$('form').each(function () {
		var typeField = $(this).find('[name*=\'[type]\']');
		if (typeField.length > 0) {
			showRelevantFormParts($(this), typeField.val());
			typeField.change(function (event) {
				showRelevantFormParts($(this).closest('form'), $(this).val());
			});
		}
	});

});
