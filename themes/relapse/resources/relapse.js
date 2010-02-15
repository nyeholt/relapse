
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

//		$.fn.simpleDialog.defaults.dialogLoaded = function (args) {
//			var $this = $(this);
//			$this.find('.ajaxForm').ajaxForm({
//				success: function (data) {
//					$this.html(data);
//				}
//			});
//		}

		Relapse.IssueManager = function () {}
		Relapse.TaskManager = function () {}
		Relapse.Issues = new Relapse.IssueManager();
		Relapse.Tasks = new Relapse.TaskManager();
	});
	window.createDialogDiv = function (name) {
		var d = $('#'+name);
		if (d.length == 0) {
			$('body').append('<div class="std dialog" id="'+name+'"></div>');
		}
	}
})(jQuery);