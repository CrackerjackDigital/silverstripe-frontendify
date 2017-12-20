(function ($) {
	var selector = '.frontendify-select2field';

	$.fn.select2ify = function (options, data) {
		var self = $(this),
			placeholder = self.attr('placeholder'),
			seperator = self.data('frontendify-tag-seperator') || ',',
			tags = seperator ? (self.data('frontendify-tags') || true) : false
			defaults = {
				placeholder: placeholder
			};

		options = $.extend(
			defaults,
			tags ? {
				tags: tags,
				tokenSeparators: [seperator],
				placeholder: placeholder,
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
			console.log(self);

			self.select2ify(options);
		},
		onchange: function () {
			console.log('changed');
		}
	});
	$(selector).each(function() {
		$(this).select2ify();
	});

})(jQuery);