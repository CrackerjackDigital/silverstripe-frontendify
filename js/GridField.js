(function ($) {
	$.entwine("ss", function ($) {
		/**
		 * GridFieldAddNewInlineButton
		 */
		$(".frontendifygrid.ss-gridfield-editable").entwine('frontendify', {
			reload: function (opts, success) {
				var grid = this;
				// Record position of all items
				var added = [];
				var index = 0; // 0-based index
				grid.find("tbody:first .ss-gridfield-item").each(function () {
					// Record inline items with their original positions
					if ($(this).is(".frontendify-inline-new")) {
						added.push({
							'index': index,
							'row': $(this)
						});
					}
					index++;
				});

				this._super(opts, function () {
					var body = grid.find("tbody:first");
					$.each(added, function (i, item) {
						return;
						var row = item['row'],
							index = item['index'],
							replaces;
						// Insert at index position
						if (index === 0) {
							body.prepend(row);
						} else {
							// Find item that we could potentially insert this row after
							replaces = body.find('.ss-gridfield-item:nth-child(' + index + ')');
							if (replaces.length) {
								replaces.after(row);
							} else {
								body.append(row);
							}
						}
						grid.find("tbody:first").children(".ss-gridfield-no-items").hide();
					});

					if (success) success.apply(grid, arguments);
				});
			},
			onpaste: function (e) {
				// The following was used as a basis for clipboard data access:
				// http://stackoverflow.com/questions/2176861/javascript-get-clipboard-data-on-paste-event-cross-browser
				var clipboardData = typeof e.originalEvent.clipboardData !== "undefined" ? e.originalEvent.clipboardData : null;
				if (clipboardData) {
					// Get current input wrapper div class (ie. 'col-Title')
					var input = $(e.target);
					var inputType = input.attr('type');
					if (inputType === 'text' || inputType === 'email') {
						var lastInput = this.find(".frontendify-inline-new:last").find("input");
						if (input.attr('type') === 'text' && input.is(lastInput) && input.val() === '') {
							var inputWrapperDivClass = input.parent().attr('class');
							// Split clipboard data into lines
							var lines = clipboardData.getData("text/plain").match(/[^\r\n]+/g);
							var linesLength = lines.length;
							// If there are multiple newlines detected, split the data into new rows automatically
							if (linesLength > 1) {
								var elementsChanged = [];
								for (var i = 1; i < linesLength; ++i) {
									this.trigger("addnewinline");
									var row = this.find(".frontendify-inline-new:last");
									var rowInput = row.find("." + inputWrapperDivClass).find("input");
									rowInput.val(lines[i]);
									elementsChanged.push(rowInput);
								}
								// Store the rows added via this method so they can be undo'd.
								input.data('pasteManipulatedElements', elementsChanged);
								// To set the current row to not just be all the clipboard data, must wait a frame
								setTimeout(function () {
									input.val(lines[0]);
								}, 0);
							}
						}
					}
				}
			},
			onkeyup: function (e) {
				if (e.keyCode == 90 && e.ctrlKey) {
					var target = $(e.target);
					var elementsChanged = target.data("pasteManipulatedElements");
					if (typeof elementsChanged !== "undefined" && elementsChanged && elementsChanged.length) {
						for (var i = 0; i < elementsChanged.length; ++i) {
							elementsChanged[i].closest('tr').remove();
						}
						target.data("pasteManipulatedElements", []);
					}
				}
			},
			onfrontendifyaddnewinline: function (e) {
				if (e.target != this[0]) {
					return;
				}

				var tmpl = window.tmpl;
				var row = this.find(".frontendify-add-inline-template:last");
				var num = this.data("add-inline-num") || 1;

				tmpl.cache[this[0].id + "frontendify-add-inline-template"] = tmpl(row.html());

				this.find("tbody:first").append(tmpl(this[0].id + "frontendify-add-inline-template", {num: num}));
				this.find("tbody:first").children(".ss-gridfield-no-items").hide();
				this.data("add-inline-num", num + 1);

				// Rebuild sort order fields
				$(".ss-gridfield-orderable tbody").rebuildSort();
			}
		});

		$(".frontendify-add-new-inline").entwine({
			onclick: function () {
				this.getGridField().trigger("frontendifyaddnewinline");
				return false;
			}
		});

		$(".frontendify-delete-inline").entwine({
			onclick: function () {
				var msg = ss.i18n._t("GridFieldExtensions.CONFIRMDEL", "Are you sure you want to delete this?");

				if (confirm(msg)) {
					this.parents("tr.frontendify-inline-new:first").remove();
				}

				return false;
			}
		});

	})
})(jQuery);
