(function ($) {
	var selector = '.frontendify-select2field';

	$.fn.select2ify = function (options, data) {
		var self = $(this),
			placeholder = self.attr('placeholder'),
			seperator = self.data('frontendify-tag-seperator') || ',',
			clear = !!self.data('frontendify-show-clear'),
			tags = seperator ? (self.data('frontendify-tags') || true) : false,
			defaults = {
				placeholder: placeholder,
				allowClear: clear
			};
		options = $.extend(
			defaults,
			tags ? {
				tags: true,
				tokenSeparators: [seperator],
				data: data || []
			} : {},
			options || {}
		);

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

			self.select2ify(options);
		}
	});
	$(selector).each(function () {
		$(this).select2ify();
	});

})(jQuery);