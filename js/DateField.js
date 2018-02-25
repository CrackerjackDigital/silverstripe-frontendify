(function ($) {
	var selector = '.frontendify-datefield';

	$.fn.datefieldify = function () {
		// only do it if we don't support native (html5) date field
//		if ($('[type="date"]').prop('type') != 'date') {

			console.log('using pickadate');
			var format = $(this).attr('frontendify-display-format') || 'dd/mm/yyyy';

			console.log('format: ' + format);

			$(this).pickadate({
				format: format,
				formatSubmit: 'yyyy-mm-dd',
				hiddenName: true,
				selectYears: 2,
				firstDay: 1,
				selectMonths: true,
				container: 'body'
			}).addClass('datefieldified');

//		} else {
//			console.log('using native');
//		}
	};

	$(selector).entwine("frontendify", {
		onmatch: function () {
			var self = this;
			self.datefieldify();
		}
	});
	$(selector).datefieldify();


})(jQuery);
