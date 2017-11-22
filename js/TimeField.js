(function ($) {
	var selector = '.frontendify-timefield';

	if ($('[type="time"]').prop('type') != 'time') {

		$.fn.timefieldify = function () {
			console.log('timefieldify');
			$(selector).timepicker({
				timeFormat: 'HH:mm',
				interval: 15,
				minTime: '07:00',
				maxTime: '6:30pm',
				defaultTime: 'now',
				startTime: '07:00',
				dynamic: true,
				dropdown: true,
				scrollbar: true
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