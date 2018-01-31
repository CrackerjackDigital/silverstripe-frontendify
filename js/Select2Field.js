(function ($) {
	var selector = '.frontendify-select2field',
		channel = postal.channel('select2field');

	function templateSelection(item) {
		var option = $(item.element),
			classes = option.hasClass('conflict') ? 'conflict' : '';

		// copy data-id and data-model-class to the list item
		return $('<span class="' + classes + '" data-model-class="' + option.data('model-class')  + '" data-id="' + item.id + '">' + item.text + '</span>');
	}

	$.fn.select2ify = function (options, data) {
		var self = $(this),
			placeholder = self.attr('placeholder'),
			seperator = self.data('frontendify-tag-seperator') || ',',
			clear = !!self.data('frontendify-show-clear'),
			tagField = self.hasClass('frontendify-tagfield'),
			defaults = {
				placeholder: placeholder,
				allowClear: clear,
				templateSelection: templateSelection
			};

		options = $.extend(
			defaults,
			tagField ? {
				// tag field options
				tags: true,
				tokenSeparators: [seperator],
				data: data || []
			} : {
				// non tag-field options
			},
			options || {}
		);

		self.select2(
			options
		);
		self.on('select2:selecting', function (e) {
			channel.publish('item.selecting', e);
		});
		self.on('select2:select', function (e) {
			channel.publish('item.select', e);
		});
		self.on('select2:unselecting', function (e) {
			channel.publish('item.unselecting', e);
		});
		self.on('select2:unselect', function (e) {
			channel.publish('item.unselect', e);
		});
		self.addClass('select2ified');

	};
	$.fn.unselect2ify = function () {
		var self = $(this);
		self.removeClass('select2ified');
		self.select2('destroy');
	};

	$(selector).entwine('frontendify', {
		onmatch: function (options) {
			$(this[0]).select2ify(options);
		}
	});


})(jQuery);