(function ($) {
	var selector = '.frontendifyselect2colourpicker';

	$(selector).entwine('frontendify', {
		onmatch: function() {
			var colour = this.val();
			this.closest('tr').css('background-color', colour);

			console.log('matched ' + colour);

			this._super();
		},
		onchange: function () {
			var colour = this.val();
			this.closest('tr').css('background-color', colour);

			this._super();
		}
	});

})(jQuery);