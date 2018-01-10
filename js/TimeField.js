(function ($) {
	var selector = '.frontendify-timefield';

	$.fn.timefieldify = function () {
		if ($('[type="time"]').prop('type') != 'time') {
			console.log('using pickatime');

			$(this).pickatime({
				clear: 'Clear',
				format: 'HH:i'
			}).addClass('timefieldified');

		} else {
			console.log('using native time field');
		}
	};

	$(selector).entwine("frontendify", {
		onmatch: function () {
			var self = this;
			self.timefieldify();
		}
	});
	$(selector).timefieldify();


})(jQuery);