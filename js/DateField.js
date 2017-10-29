(function ($) {
	var selector = '.frontendify-datefield';

	if ($('[type="date"]').prop('type') != 'date') {

		$.fn.datefieldify = function () {
			console.log('datefieldify');

			$(selector).datepicker({
				dateFormat: 'yy-mm-dd',
				altFormat: 'yy-mm-dd',
				autoclose: true,
				todayHighlight: true,
				autoSize: true,
				defaultDate: 0
			}).addClass('datefieldified');

		};

		$(selector).entwine("frontendify", {
			onmatch: function () {
				var self = this;
				self.datefieldify();
			}
		});
		$(selector).datefieldify();
	}


})(jQuery);