
// Set up some default objects for attaching behaviour to later on
var Relapse;

(function ($) {
	function RelapseManager() {
		this.layout = null;
		this.effect = 'blind'; // highlight or blind

		this.expandedWidth = 600;
		this.collapsedWidth = 250;
	}

	RelapseManager.prototype = {
		/**
		 * Called on page initialisation
		 */
		init: function () {
			var _this = this;

			if ($('#LayoutContainer').length) {
				this.layout = $("#LayoutContainer").layout({
					east__size: this.collapsedWidth,
					enableCursorHotkey: false
				});
			}

			$('a.targeted').livequery(function () {
				$(this).click(function () {
					var url = $(this).attr('href');
					var target = $(this).attr('target');

					var title = $(this).attr('title');
					if (!title) {
						title = $(this).text();
					}

					_this.addToPane(target, url, title);
					
					return false;
				});
			});

			if (typeof(PANE_FAVOURITES) != "undefined" && PANE_FAVOURITES.length) {
				for (var i = 0; i < PANE_FAVOURITES.length; i++) {
					var pf = PANE_FAVOURITES[i];
					this.addQuickLink(pf.title, pf.url, pf.pane, pf.id)
				}
			}
		},

		/**
		 * Adds a new panel into a given pane, using a URL to represent the panel
		 *
		 * @var String url
		 * 
		 */
		addToPane: function (pane, url, title, params) {
			var _this = this;

			if (!params) {
				params = {};
			}

			params['_ajax'] = 1;

			// we create the panel first so the user know it exists and we can show a temporary 'loading...'
			var panel = _this.createPanel(null, title, url, pane);
			$('#' + pane).prepend(panel);
			panel.show(_this.effect);
			
			$.get(url, params, function (data) {
				panel.find('.panePanelBody').html(data);
				
				$('.ui-layout-pane').scrollTo(panel);
				if (pane == "RightPane") {
					_this.layout.sizePane("east", _this.expandedWidth);
				} else {
					_this.layout.sizePane("east", _this.collapsedWidth);
				}
			});
		},

		createPanel: function (content, title, url, pane) {
			var _this = this;
			
			var panel = $('<div class="panePanel" style="display: none"></div>');
			var panelClose = null;
			if (title) {
				var panelTitle = $('<div class="panePanelTitle"><span>'+title+'</span></div>').appendTo(panel);
				panelClose = $('<div class="panePanelClose">x</div>').prependTo(panelTitle);
			} else {
				panelClose = $('<div class="panePanelClose">x</div>').appendTo(panel);
			}

			panelClose.click(function () {
				// _this.closePanel(this);
				var p = $(this).parents('.panePanel');
				_this.closePanel(p);
			});

			panel.click(function () {
				// we want to make sure we're at an appropriate size
				if (pane == 'RightPane') {
					_this.layout.sizePane("east", _this.expandedWidth);
				} else if (pane == 'CenterPane') {
					_this.layout.sizePane("east", _this.collapsedWidth);
				}
			})

			var panelBody = $('<div class="panePanelBody"></div>').appendTo(panel);

			if (!content) {
				// create the default content block which shows before any content is loaded
				content = '<div class="panePanelDefault"></div>';
			}
			panelBody.append(content);
			
			if (pane) {
				var saveLinkContainer = $('<div class="panePanelSave">').insertAfter(panelClose);
				var saveInput = $('<input type="text" name="panelSaveTitle" />').appendTo(saveLinkContainer);
				saveInput.val(title);
				var saveButton = $('<input type="button" value="Save" />').appendTo(saveLinkContainer);

				saveButton.click(function () {
					var title = $(this).parent().find('input[name=panelSaveTitle]').val();
					_this.saveToQuickLinks(title, url, pane);
				})
			}

			return panel;
		},

		closePanel: function (panel) {
			var _this = this;
			panel = $(panel);
			panel.hide(_this.effect, null, null, function () {
				var col = $(this).parents('.ui-layout-pane');
				if (col.length) {
					var more = col.find('.panePanel');
					if (more.length <= 1) {
						if (col.attr('id') == 'RightPane') {
							_this.layout.sizePane("east", _this.collapsedWidth);
							// scroll to the top of the centre pane
							if ($('#CenterPane .panePanel').length) {
								$('#CenterPane').scrollTo($('#CenterPane .panePanel')[0]);
								$(window).scrollTo($('body'));
							}
						}
					}
				}
				$(this).remove();
			})
		},

		/**
		 * Add a url as a favourite in a particular pane
		 */
		saveToQuickLinks: function (title, url, pane) {
			var _this = this;
			$.post(BASE_URL + 'index/favouritePane', {title: title, pane: pane, url: url}, function (data) {
				var response = $.parseJSON(data);
				if (response.data && response.success) {
					_this.addQuickLink(title, url, pane, response.data.id);
				}
			});
		},

		addQuickLink: function (title, url, pane, id) {
			var content = '<a href="'+url+'" target="'+pane+'" class="targeted">'+title+'</a>';
			var panel = this.createPanel(content, null, url);

			$('#LeftPane').prepend(panel);
			panel.show(this.effect);
			
			var deleteButton = $('<img src="'+BASE_URL+'resources/images/delete.png" style="float: left;" />').prependTo(panel);
			deleteButton.click(function () {
				$.post(BASE_URL + 'index/deletefavourite', {id: id}, function (data) {
					var response = $.parseJSON(data);
					if (response.data && response.success) {
						panel.remove();
					}
				});
			});
		},

		createDialog: function (name, options)  {
			if (options && options.closeExisting) {
				this.closeDialog(name);
			}
			createDialogDiv(name);
			$('#'+name).simpleDialog(options);
		},

		closeDialog: function (name, context) {
			if (name) {
				$('#'+name).simpleDialog('close');
			}

			if (context) {
				// see if we're a side panel object that needs closing
				var panel = $(context).parents('.panePanel');
				this.closePanel(panel);
				$('.pReload',grid).click();
			}
		}
	};

	Relapse = new RelapseManager();

	$().ready(function () {
		Relapse.init();
		
		$('tr:odd').addClass('odd');
		$('tr:even').addClass('even');

		$('#QuickSearch').submit(function () {
			var params = $(this).serialize();
			Relapse.addToPane('CenterPane', BASE_URL + 'search?'+params, 'Search');
			return false;
		});

		$('.ajaxForm').livequery(function () {
			var $form = $(this);
			$form.validate({
				submitHandler: function () {
					var submits = $form.find('input[type=submit]');
					submits.each(function () {
						$(this).attr('title', $(this).css('color'));
					});

					submits.css('color', '#ddd');
					submits.attr('disabled', true);
					$form.ajaxSubmit({
						success: function (data) {
							var d = $form.parents('.dialogContent');
							if (d.length) {
								if (d.hasClass('appendresult')) {
									d.append(data);
								} else {
									d.html(data);
								}
							} else if ($form.hasClass('replacecontent')) {
								$form.html(data);
							}
							submits.each(function () {
								$(this).css('color', $(this).attr('title'));
							});
							submits.attr('disabled', false);
						}
					});
				}
			});
		});
	});

	window.createDialogDiv = function (name) {
		var d = $('#'+name);
		if (d.length == 0) {
			$('body').append('<div class="std dialog" id="'+name+'"></div>');
		}
	}

})(jQuery);