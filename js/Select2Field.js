(function ($) {
	var selector = '.frontendify-select2field';

	$.fn.select2ify = function () {
		var self = $(this),
			placeholder = self.attr('placeholder'),
			seperator = self.data('frontendify-tag-seperator') || ',',
			tags = self.data('frontendify-tags') || '',
			options = tags ? {
				tags: tags,
				tokenSeparators: [seperator],
				placeholder: placeholder,
				allowClear: true
			} : {
				placeholder: placeholder
			};

		self.select2(
			options
		);
		self.addClass('select2ified');
	};

	$('.frontendifygrid.ss-gridfield-editable').entwine('frontendify', {
		onfrontendifyaddnewinline: function (e) {
			$(selector, $(this)).not('.select2ified').select2ify();
		}
	});

	$(selector).entwine('frontendify', {
		onmatch: function () {
			var self = $(this);
			self.select2ify();
		}
	});
	$(selector).select2ify();

})(jQuery);