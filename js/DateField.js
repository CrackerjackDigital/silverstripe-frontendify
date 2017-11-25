(function ($) {
	var selector = '.frontendify-datefield';

	// if ($('[type="date"]').prop('type') != 'date') {

	$.fn.datefieldify = function () {
		console.log('datefieldify');
		$(selector).pickadate({
			formatSubmit: 'yyyy-mm-dd',
			format: 'dd/mm/yyyy',
			selectYears: 2,
			firstDay: 1,
			selectMonths: true,
			container: 'body'

	        }).addClass('datefieldified');

	};

	$(selector).entwine("frontendify", {
		onmatch: function () {
			var self = this;
			self.datefieldify();
		}
	});
	$(selector).datefieldify();


})(jQuery);
