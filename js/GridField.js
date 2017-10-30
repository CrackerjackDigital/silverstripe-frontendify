(function ($) {
	$.entwine("ss", function ($) {
		/**
		 * GridFieldAddNewInlineButton
		 */
		$(".frontendifygrid.ss-gridfield-editable").entwine({
			reload: function (opts, successCallback) {
				var grid = this;
				// Record position of all items
				var added = [];
				var index = 0; // 0-based index
				var self = this, form = this.closest('form'),
					focusedElName = this.find(':input:focus').attr('name'), // Save focused element for restoring after refresh
					data = form.find(':input').serializeArray();


				grid.find("tbody:first .ss-gridfield-item").each(function () {
					// Record inline items with their original positions
					if ($(this).is(".ss-gridfield-inline-new")) {
						added.push({
							'index': index,
							'row': $(this)
						});
					}
					index++;
				});
				if (!ajaxOpts) ajaxOpts = {};
				if (!ajaxOpts.data) ajaxOpts.data = [];
				ajaxOpts.data = ajaxOpts.data.concat(data);


				// Include any GET parameters from the current URL, as the view state might depend on it.
				// For example, a list prefiltered through external search criteria might be passed to GridField.
				if (window.location.search) {
					ajaxOpts.data = window.location.search.replace(/^\?/, '') + '&' + $.param(ajaxOpts.data);
				}

				// For browsers which do not support history.pushState like IE9, ss framework uses hash to track
				// the current location for PJAX, so for them we pass the query string stored in the hash instead
				if (!window.history || !window.history.pushState) {
					if (window.location.hash && window.location.hash.indexOf('?') != -1) {
						ajaxOpts.data = window.location.hash.substring(window.location.hash.indexOf('?') + 1) + '&' + $.param(ajaxOpts.data);
					}
				}

				form.addClass('loading');

				$.ajax($.extend({}, {
					headers: {"X-Pjax": 'CurrentField'},
					type: "POST",
					url: this.data('url'),
					dataType: 'html',
					success: function (data, textStatus, jqXHR) {
						// Replace the grid field with response, not the form.
						// TODO Only replaces all its children, to avoid replacing the current scope
						// of the executing method. Means that it doesn't retrigger the onmatch() on the main container.
						self.empty().append($(data).children());

						// Refocus previously focused element. Useful e.g. for finding+adding
						// multiple relationships via keyboard.
						if (focusedElName) self.find(':input[name="' + focusedElName + '"]').focus();

						// Update filter
						if (self.find('.filter-header').length) {
							var content;
							if (ajaxOpts.data[0].filter == "show") {
								content = '<span class="non-sortable"></span>';
								self.addClass('show-filter').find('.filter-header').show();
							} else {
								content = '<button type="button" name="showFilter" class="ss-gridfield-button-filter trigger"></button>';
								self.removeClass('show-filter').find('.filter-header').hide();
							}

							self.find('.sortable-header th:last').html(content);
						}

						form.removeClass('loading');
						if (successCallback) {
							successCallback.apply(this, arguments);
						}
						self.trigger('reload', self);
					},
					error: function (e) {
						alert(ss.i18n._t('GRIDFIELD.ERRORINTRANSACTION'));
						form.removeClass('loading');
					}
				}, ajaxOpts));
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


		/**
		 * GridFieldEditableColumns
		 */

		$('.ss-gridfield.ss-gridfield-editable .ss-gridfield-item td').entwine({
			onclick: function (e) {
				// Prevent the default row click action when clicking a cell that contains a field
				if (this.find('.editable-column-field').length) {
					e.stopPropagation();
				}
			}
		});
		/**
		 * GridFieldOrderableRows
		 */

		$(".ss-gridfield-orderable tbody").entwine({
			rebuildSort: function () {
				var grid = this.getGridField();

				// Get lowest sort value in this list (respects pagination)
				var minSort = null;
				grid.getItems().each(function () {
					// get sort field
					var sortField = $(this).find('.ss-orderable-hidden-sort');
					if (sortField.length) {
						var thisSort = sortField.val();
						if (minSort === null && thisSort > 0) {
							minSort = thisSort;
						} else if (thisSort > 0) {
							minSort = Math.min(minSort, thisSort);
						}
					}
				});
				minSort = Math.max(1, minSort);

				// With the min sort found, loop through all records and re-arrange
				var sort = minSort;
				grid.getItems().each(function () {
					// get sort field
					var sortField = $(this).find('.ss-orderable-hidden-sort');
					if (sortField.length) {
						sortField.val(sort);
						sort++;
					}
				});
			},
			onadd: function () {
				var self = this;

				var helper = function (e, row) {
					return row.clone()
						.addClass("ss-gridfield-orderhelper")
						.width("auto")
						.find(".col-buttons")
						.remove()
						.end();
				};

				var update = function (event, ui) {
					// If the item being dragged is unsaved, don't do anything
					var postback = true;
					if ((ui != undefined) && ui.item.hasClass('ss-gridfield-inline-new')) {
						postback = false;
					}

					// Rebuild all sort hidden fields
					self.rebuildSort();

					// Check if we are allowed to postback
					var grid = self.getGridField();
					if (grid.data("immediate-update") && postback) {
						grid.reload({
							url: grid.data("url-reorder")
						});
					}
					else {
						// Tells the user they have unsaved changes when they
						// try and leave the page after sorting, also updates the
						// save buttons to show the user they've made a change.
						var form = $('.cms-edit-form');
						form.addClass('changed');
					}
				};

				this.sortable({
					handle: ".handle",
					helper: helper,
					opacity: .7,
					update: update
				});
			},
			onremove: function () {
				if (this.data('sortable')) {
					this.sortable("destroy");
				}
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
