(function ($) {
	var selector = '.frontendify-timefield';

	if ($('[type="time"]').prop('type') != 'time') {

		$.fn.timefieldify = function () {
			console.log('timefieldify');
			$(selector).pickatime({
				clear: 'Clear',
				format: 'HH:i'
			}).addClass('timefieldified');

		};

		$(selector).entwine("frontendify", {
			onmatch: function () {
				var self = this;
				self.timefieldify();
			}
		});
		$(selector).timefieldify();
	}


})(jQuery);