(function ($) {
	var selector = '.frontendify-datefield';

	// if ($('[type="date"]').prop('type') != 'date') {

		$.fn.datefieldify = function () {
			console.log('datefieldify');
			// $(selector).datepicker({
			// 	format: 'yyyy-mm-dd',
			// 	todayBtn: true,
			// 	todayHighlight: true,
			// 	autoclose: true,
			// 	autoSize: true,
			// 	weekStart: 1
			// }).addClass('datefieldified');

			 $(selector).pickadate({
			    formatSubmit: 'yyyy-mm-dd',
			    format: 'dd mmmm, yyyy', 
			    selectYears: 2,
			    firstDay: 1,
			    selectMonths: true,
			    container: 'body'

			}).addClass('datefieldified');

		};

		// $(selector).entwine("frontendify", {
		// 	onmatch: function () {
		// 		var self = this;
		// 		self.datefieldify();
		// 	}
		// });
		$(selector).datefieldify();
	// }


})(jQuery);
