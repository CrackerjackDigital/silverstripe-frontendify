(function ($) {
	$.entwine("frontendify", function ($) {
		/**
		 * Common functions across frontendify-gridfield classed elements
		 */
		$('.frontendify-gridfield *').entwine({
			getFrontendifyGridField: function () {
				return this.closest('.frontendify-gridfield');
			}
		});

		/**
		 * Set item backgrounds on load from row-background-colour attribute
		 */
		$(".frontendify-gridfield .ss-gridfield-item").entwine({
			onmatch: function () {
				// set background row colour to attribute value if set
				var bgColour = this.data('row-background-colour');
				if (bgColour) {
					this.css('background-color', bgColour);
				}
			}
		});

		/**
		 * GridField actions: deleteRow, refresh, saveall, addnewinline
		 */
		$(".frontendify-gridfield").entwine({
			deleteRow: function (ajaxOpts, successCallback) {
				var grid = this,
					container = grid.closest('.frontendify'),
					url = grid.data('url'),
					row = ajaxOpts.row;

				if (!ajaxOpts) {
					ajaxOpts = {};
				}
				if (!ajaxOpts.data) {
					ajaxOpts.data = {};
				}

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

				if (ajaxOpts.ID && !ajaxOpts.isNew) {
					container.addClass('loading');

					$.ajax(
						$.extend(
							{},
							{
								headers: {"X-Pjax": 'CurrentField'},
								type: "POST",
								url: url,
								dataType: 'html',
								success: function (result, textStatus, jqXHR) {
									container.removeClass('loading');
									if (successCallback) {
										successCallback.apply(this, arguments);
									}
									row.animate(
										{
											height: 0
										},
										function () {
											row.detach();
										}
									);

								},
								error: function (e) {
									alert(ss ? ss.i18n._t('GRIDFIELD.ERRORINTRANSACTION') : 'Sorry, there was an error, please submit again');
									form.removeClass('loading');
								}
							},
							ajaxOpts
						)
					);
				}
			},
			refresh: function (ajaxOpts, successCallback) {
				var grid = this,
					container = this.closest('.frontendify'),
					form = this.closest('form'),
					table = grid.find('table.ss-gridfield-table'),
					rows = table.find('tbody:first'),
					data = form.find(':input').serializeArray(),
					url = this.data('url'),
					modeName = this.data('mode-name');

				rows.find('.ss-gridfield-item')
					.removeClass('error')
					.removeClass('warning')
					.removeClass('success');

				if (!ajaxOpts) {
					ajaxOpts = {};
				}
				if (!ajaxOpts.data) {
					ajaxOpts.data = [];
				}
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

				url += ((url.indexOf('?') === -1 ) ? '?' : '&') + '_mode=' + modeName;

				container.addClass('loading');

				$.ajax($.extend({}, {
					headers: {"X-Pjax": 'CurrentField'},
					type: "POST",
					url: url,
					dataType: 'html',
					success: function (pjaxHTML, textStatus, jqXHR) {
						var result = $(pjaxHTML).find('table.ss-gridfield-table');

						table.empty();
						table.append(result.children());

						container.removeClass('loading');
						if (successCallback) {
							successCallback.apply(this, arguments);
						}
					},
					error: function (e) {
						alert(ss ? ss.i18n._t('GRIDFIELD.ERRORINTRANSACTION') : 'Sorry, there was an error, please submit again');
						form.removeClass('loading');
					}
				}, ajaxOpts));
			},
			saveall: function (ajaxOpts, successCallback) {
				var grid = this,
					form = this.closest('form'),
					tbody$ = 'table.ss-gridfield-table tbody:first',
					tbody = grid.find(tbody$),
					focusedElName = this.find(':input:focus').attr('name'),
					data = form.find(':input').serializeArray(),
					index = 1,
					stashed = [];

				tbody.find('.ss-gridfield-item')
					.removeClass('error')
					.removeClass('warning')
					.removeClass('success');

				// save all rows, for errors we need to be able to restore the pre-saved value
				// as the response from the server only contains valid rows without changes if
				// an error has occured.
				tbody.find("tr.ss-gridfield-item").each(function () {
					var row = $(this),
						id = row.find('.col-ID input').val(),
						action = row.data('id') ? 'update' : 'new';


					stashed[index] = {
						'id': id,
						'index': index,
						'action': action,
						'row': row
					};
					index++;
				});

				if (!ajaxOpts) {
					ajaxOpts = {};
				}
				if (!ajaxOpts.data) {
					ajaxOpts.data = [];
				}
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
					success: function (pjax, textStatus, jqXHR) {
						var json = jqXHR.getResponseHeader('X-Messages'),
							results = JSON.parse(json);     // json result for each row submitted (stashed)

						// doit on the repaint
						window.requestAnimationFrame(function () {
							var index, row, icon, message, result, stash;

							for (index in results) {
								result = results[index];

								if (result.tempid) {
									stash = _.filter(stashed, {id: result.tempid});

									if (stash.length) {
										// find by tempID (new row)
										row = stash[0].row;
									} else {
										// something wrong, skip it
										continue;
									}

								} else {
									// find by previous ID
									row = tbody.find('td.col-ID input[value="' + result.id + '"]').closest('tr');

								}

								if (result.id) {
									if (result.tempid) {
										// saved new row OK so replace it with result from server
										// otherwise we leave it in place as couldn't save
										row.detach();
										row = $(pjax).find('tr[data-id="' + result.id + '"]');
										tbody.append(row);
									}
									// set id and data attribute to whatever we got back
									row.data('id', result.id);
								}

								// set class on the row
								row.addClass(result.type);

								// set message on trow
								message = result.message || '';
								row.find('td.col-Messages').text(result.message);

								icon = result.icon || '';
								row.find('td.col-Icon').html('<i class="glyphicon glyphicon-' + icon + '"></i>');
							}

							// remove added rows

							if (focusedElName) {
								grid.find(':input[name="' + focusedElName + '"]').focus();
							}

							// Update filter
							if (grid.find('.filter-header').length) {
								var content;
								if (ajaxOpts.data[0].filter == "show") {
									content = '<span class="non-sortable"></span>';
									grid.addClass('show-filter').find('.filter-header').show();
								} else {
									content = '<button type="button" name="showFilter" class="ss-gridfield-button-filter trigger"></button>';
									grid.removeClass('show-filter').find('.filter-header').hide();
								}

								grid.find('.sortable-header th:last').html(content);
							}
							form.removeClass('loading');
							if (successCallback) {
								successCallback.apply(this, arguments);
							}

						});
					},
					error: function (e) {
						alert(ss ? ss.i18n._t('GRIDFIELD.ERRORINTRANSACTION') : 'Sorry, there was an error, please submit again');
						form.removeClass('loading');
					}
				}, ajaxOpts));
			},
			onfrontendifyaddnewinline: function (e) {
				if (e.target != this[0]) {
					return;
				}
				var tmpl = window.tmpl;
				var row = this.find(".frontendify-add-inline-template:last");
				var num = this.data("add-inline-num") || 1,
					tbody = this.find("table.ss-gridfield-table tbody:first"),
					newrow;

				tmpl.cache[this[0].id + "frontendify-add-inline-template"] = tmpl(row.html());

				newrow = $(tmpl(this[0].id + "frontendify-add-inline-template", {num: num}));
				newrow.find('td.col-ID input').val('NewRow' + (new Date().getTime()).toString(16));

				tbody.append(newrow);

				tbody.children(".ss-gridfield-no-items").hide();

				this.data("add-inline-num", num + 1);

				// Rebuild sort order fields
				$(".ss-gridfield-orderable tbody").rebuildSort();

				$('.frontendify-select2field', $(this)).not('.select2ified').select2ify();
				$(".frontendify-datefield", $(this)).not('.datefieldified').datefieldify();
				$(".frontendify-timefield", $(this)).not('.timefieldified').datefieldify();

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
			}

		});

		/**
		 * Save all button
		 */
		$('.frontendify-gridfield .action.frontendify-saveallbutton').entwine({
			onclick: function (e) {
				var filterState = 'show'; //filterstate should equal current state.

				e.preventDefault();
				e.stopPropagation();


				if (this.hasClass('ss-gridfield-button-close') || !(this.closest('.ss-gridfield').hasClass('show-filter'))) {
					filterState = 'hidden';
				}
				this.getFrontendifyGridField().saveall({
					data: [{
						name: this.attr('name'),
						value: this.val(),
						filter: filterState
					}]
				});

				return false;
			},
			/**
			 * Get the url this action should submit to
			 */
			actionurl: function () {
				var btn = this.closest(':button'),
					grid = this.getFrontendifyGridField(),
					form = this.closest('form'),
					data = form.find(':input.gridstate').serialize(),
					csrf = form.find('input[name="SecurityID"]').val();

				// Add current button
				data += "&" + encodeURIComponent(btn.attr('name')) + '=' + encodeURIComponent(btn.val());

				// Add csrf
				if (csrf) {
					data += "&SecurityID=" + encodeURIComponent(csrf);
				}

				// Include any GET parameters from the current URL, as the view
				// state might depend on it. For example, a list pre-filtered
				// through external search criteria might be passed to GridField.
				if (window.location.search) {
					data = window.location.search.replace(/^\?/, '') + '&' + data;
				}

				// decide whether we should use ? or & to connect the URL
				var connector = grid.data('url').indexOf('?') == -1 ? '?' : '&';

				return $.path.makeUrlAbsolute(
					grid.data('url') + connector + data,
					$('base').attr('href')
				);
			}

		});

		/**
		 * Publish button
		 */
		$('.frontendify-gridfield .action.frontendify-publishbutton').entwine({
			onclick: function (e) {
				var filterState = 'show'; //filterstate should equal current state.

				e.preventDefault();
				e.stopPropagation();

				if (this.hasClass('ss-gridfield-button-close') || !(this.closest('.ss-gridfield').hasClass('show-filter'))) {
					filterState = 'hidden';
				}
				this.getFrontendifyGridField().saveall({
					data: [{
						name: this.attr('name'),
						value: this.val(),
						filter: filterState
					}]
				});

				return false;
			},
			/**
			 * Get the url this action should submit to
			 */
			actionurl: function () {
				var btn = this.closest(':button'),
					grid = this.getFrontendifyGridField(),
					form = this.closest('form'),
					data = form.find(':input.gridstate').serialize(),
					csrf = form.find('input[name="SecurityID"]').val();

				// Add current button
				data += "&" + encodeURIComponent(btn.attr('name')) + '=' + encodeURIComponent(btn.val());

				// Add csrf
				if (csrf) {
					data += "&SecurityID=" + encodeURIComponent(csrf);
				}

				// Include any GET parameters from the current URL, as the view
				// state might depend on it. For example, a list pre-filtered
				// through external search criteria might be passed to GridField.
				if (window.location.search) {
					data = window.location.search.replace(/^\?/, '') + '&' + data;
				}

				// decide whether we should use ? or & to connect the URL
				var connector = grid.data('url').indexOf('?') == -1 ? '?' : '&';

				return $.path.makeUrlAbsolute(
					grid.data('url') + connector + data,
					$('base').attr('href')
				);
			}

		});

		/**
		 * Filters
		 */
		$('.frontendify-gridfield .action.frontendify-filter-apply').entwine({
			onclick: function (e) {
				console.log('show');

				e.preventDefault();
				e.stopPropagation();

				this.getFrontendifyGridField().refresh({
					data: [{
						name: this.attr('name'),
						value: this.val(),
						filter: 'apply'
					}]
				});

				return false;
			}
		});

		$('.frontendify-gridfield .action.frontendify-filter-clear').entwine({
			onclick: function (e) {
				console.log('clear');
				e.preventDefault();
				e.stopPropagation();

				this.closest('.frontendify-filter-row').find(':input').not('button').each(function() {
					// if clear is present and it has a value then use it, otherwise use ''
					// if default is present then use it otherwise use the current value
					// (so if neither clear or default defined then don't clear at all)
					var clear = $(this).attr('frontendify-clear-value'),
						def = $(this).attr('frontendify-default-value'),
						name = $(this).attr('name'),
						value = (typeof clear === typeof undefined)
							? ((typeof def !== typeof undefined) ? def : $(this).val())
							: (clear || '');

					console.log('clearing ' + name + ' to ' + value);
					$(this).val(value);
				});

				this.getFrontendifyGridField().refresh({
					data: [{
						name: this.attr('name'),
						value: this.val(),
						filter: 'clear'
					}]
				});

				return false;
			}
		});


		$('.frontendify-gridfield .action.frontendify-delete-row').entwine({
			onclick: function (e) {
				e.preventDefault();
				e.stopPropagation();

				var row = this.closest('tr'),
					ID, isNew;

				if (row && row.length) {
					ID = row.data('id');
					isNew = row.is('.frontendify-inline-new');

					this.getFrontendifyGridField().deleteRow({
						data: [
							{
								name: this.attr('name'),
								value: this.val()
							},
							{
								name: 'RecordID',
								value: ID
							}
						],
						ID: ID,
						isNew: isNew,
						row: row
					});
				}

				return false;
			}
		});

		$(".frontendify-add-new-inline").entwine({
			onclick: function (e) {
				this.getFrontendifyGridField().trigger("frontendifyaddnewinline");
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

		/**
		 * GridFieldEditableColumns disable row clicks
		 */

		$('.frontendify-gridfield.ss-gridfield-editable .ss-gridfield-item td').entwine({
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
				var grid = this.getFrontendifyGridField();

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
					var grid = self.getFrontendifyGridField();
					if (grid.data("immediate-update") && postback) {
						grid.saveall({
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

	});

	//scroll events
	$(window).scroll(function(){
	    var scrollPos = $(document).scrollTop();
	    var filterBar = $('.ss-gridfield-buttonrow');
	    if(scrollPos >= 100){
	    	filterBar.css('top','55px');
	    	filterBar.css('background-color','#FFD5B7');
	    }else{
	    	filterBar.css('top','160px');
	    	filterBar.css('background-color','#fff');
	    }
	});
})(jQuery);
