
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
					var d = $form.parents('.dialogContent');
					d.html(data);
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
})(jQuery);