(function ($) {
	var selector = '.frontendify-select2field';

	$.fn.select2ify = function (data) {
		var self = $(this),
			placeholder = self.attr('placeholder'),
			seperator = self.data('frontendify-tag-seperator') || ',',
			tags = self.data('frontendify-tags') || '',
			options = tags ? {
				tags: tags,
				tokenSeparators: [seperator],
				placeholder: placeholder,
				allowClear: true,
				data: data || []
			} : {
				placeholder: placeholder
			};

		self.select2(
			options
		);
		self.addClass('select2ified');
	};
	$.fn.unselect2ify = function() {
		var self = $(this);
		self.removeClass('select2ified');
		self.select2('destroy');
	};

	$(selector).entwine('frontendify', {
		onmatch: function () {
			var self = $(this);
			self.select2ify();
		}
	});
	$(selector).select2ify();

})(jQuery);