(function ($) {
	var selector = '.frontendifyselect2colourpicker';

	// returns a single coloured option element
	function option(object, container, query) {
		return $('<div style="background-color:' + object.id + '; margin-top: 4px; width:100%; height:1.25em;"></div>');
	}

	$(selector).entwine('frontendify', {
		onmatch: function(options) {
			var colour = this.val(),
				selector = this.data('colourpicker-container');

			if (selector) {
				this.closest('tr').css('background-color', colour);
			}

			options = {
				templateResult: option,
				templateSelection: option
			};

			this._super(options);
		},
		onchange: function () {
			var colour = this.val(),
				selector = this.data('colourpicker-container');

			if (selector) {
				this.closest(selector).css('background-color', colour);
			}

			this._super();
		}
	});

})(jQuery);