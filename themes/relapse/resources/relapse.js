(function ($) {
	$().ready(function () {
		$('tr:odd').addClass('odd');
		$('tr:even').addClass('even');

		$.fn.simpleDialog.defaults.dialogLoaded = function (args) {
			var $this = $(this);
			$this.find('.ajaxForm').ajaxForm({
				success: function (data) {
					$this.html(data);
				}
			});
		}
	});
})(jQuery);