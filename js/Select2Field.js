(function ($) {
	var selector = '.frontendify-select2field';

	$.fn.select2ify = function (options, data) {
		var self = $(this),
			placeholder = self.attr('placeholder'),
			seperator = self.data('frontendify-tag-seperator') || ',',
			tags = self.data('frontendify-tags'),
			defaults = {
				placeholder: placeholder
			};

		console.dir(options);

		options = $.extend(
			defaults,
			tags ? {
				tags: true,
				tokenSeparators: [seperator],
				allowClear: true,
				data: data || []
			} : {},
			options || {}
		);
		console.dir(options);

		window.requestAnimationFrame(function () {

			self.select2(
				options
			);
			self.addClass('select2ified');
		});
	};
	$.fn.unselect2ify = function () {
		var self = $(this);
		self.removeClass('select2ified');
		self.select2('destroy');
	};

	$(selector).entwine('frontendify', {
		onmatch: function (options) {
			var self = $(this);
			console.log(options);

			self.select2ify(options);
		},
		onchange: function () {
			console.log('changed');
		}
	});
	$(selector).select2ify();

})(jQuery);