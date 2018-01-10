(function ($) {
	var selector = '.frontendify-datefield';

	$.fn.datefieldify = function () {
		// only do it if we don't support native (html5) date field
		if ($('[type="date"]').prop('type') != 'date') {

			console.log('using pickadate');

			window.requestAnimationFrame(function () {
				$(selector).pickadate({
					format: 'dd/mm/yyyy',
					selectYears: 2,
					firstDay: 1,
					selectMonths: true,
					container: 'body'
				}).addClass('datefieldified');
			});

		} else {
			console.log('using native');
		}
	};

	$(selector).entwine("frontendify", {
		onmatch: function () {
			var self = this;
			self.datefieldify();
		}
	});
	$(selector).datefieldify();


})(jQuery);
