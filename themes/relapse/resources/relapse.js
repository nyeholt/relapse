(function ($) {
	$().ready(function () {
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