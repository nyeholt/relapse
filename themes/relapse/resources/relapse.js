
// Set up some default objects for attaching behaviour to later on
var Relapse = typeof(Relapse) == "undefined" ? {} : Relapse;

(function ($) {
	$().ready(function () {
		$('tr:odd').addClass('odd');
		$('tr:even').addClass('even');

		$('.ajaxForm').livequery(function () {
			var $form = $(this);
			$form.validate({
				submitHandler: function () {

					var submits = $form.find('input[type=submit]');
					submits.attr("value", "Please wait...");
					submits.attr('disabled', 'true');

					$form.ajaxSubmit({
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
				}
			});
		});

		Relapse.createDialog = function (name, options)  {
			createDialogDiv(name);
			$('#'+name).simpleDialog(options);
		}
		Relapse.closeDialog = function (name) {
			$('#'+name).simpleDialog('close');
		}
		
	});

	window.createDialogDiv = function (name) {
		var d = $('#'+name);
		if (d.length == 0) {
			$('body').append('<div class="std dialog" id="'+name+'"></div>');
		}
	}

	
})(jQuery);