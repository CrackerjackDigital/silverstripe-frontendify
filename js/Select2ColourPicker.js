(function ($) {
	var selector = '.frontendifyselect2colourpicker';

	$(selector).entwine('frontendify', {
		onchange: function () {
			var colour = this.val();
			this.closest('tr').css('background-color', colour);

			this._super();
		}
	});

})(jQuery);