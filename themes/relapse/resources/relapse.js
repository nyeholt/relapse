
// Set up some default objects for attaching behaviour to later on
var Relapse = typeof(Relapse) == "undefined" ? {} : Relapse;

(function ($) {
	$().ready(function () {
		$('tr:odd').addClass('odd');
		$('tr:even').addClass('even');

		$('.ajaxForm').livequery(function () {
			var $form = $(this);
			$(this).ajaxForm({
				success: function (data) {
					// if the form has the 'noreplace' class, we don't replace
					// the content
					var d = $form.parents('.dialogContent');
					if (d.hasClass('appendresult')) {
						d.append(data);
					} else {
						d.html(data);
					}
				}
			});
		});

		Relapse.IssueManager = function () {}
		Relapse.FeatureManager = function () {}
		Relapse.TaskManager = function () {}
		Relapse.Issues = new Relapse.IssueManager();
		Relapse.Tasks = new Relapse.TaskManager();
		Relapse.Features = new Relapse.FeatureManager();

		Relapse.createDialog = function (name, options)  {
			createDialogDiv(name);
			$('#'+name).simpleDialog(options);
		}


		$('#featureList ul').sortable({
			placeholder: 'ui-state-highlight'
		}).disableSelection().sortable('disable').css('opacity', '1');

		$('.enableReorder').click(function () {
			$('#featureList li div:not(div.feature-title)').hide();
			$('div.feature-title').addClass('dragTitle');
			$('#featureList ul').sortable('enable');
			$('.disableReorder').show();
			$('.saveOrder').show();
			$('.enableReorder').hide();
		});

		$('.disableReorder').click(function () {
			$('#featureList li div:not(div.feature-title)').show();
			$('div.feature-title').removeClass('dragTitle');
			$('#featureList ul').sortable('disable');
			$('.enableReorder').show();
			$('.disableReorder').hide();
		});

		$('.saveOrder').click(function () {
			$('#featureList ul').each(function() {
				var ids = $(this).sortable('toArray');
				if (ids) {
					var str = ids.toString();
					var url = $('#featureSort').attr('action');
					$.post(url, {ids: str});
				}
			});
			$('.saveOrder').hide();

		});
	});

	window.createDialogDiv = function (name) {
		var d = $('#'+name);
		if (d.length == 0) {
			$('body').append('<div class="std dialog" id="'+name+'"></div>');
		}
	}
})(jQuery);