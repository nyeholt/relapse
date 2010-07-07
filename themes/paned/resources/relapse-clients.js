
(function ($) {
	Relapse.ClientManager = function () {
	}

	Relapse.ClientManager.prototype = {
		showContactFromGrid: function (grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				Relapse.addToPane('RightPane', BASE_URL + 'contact/edit/id/'+id, 'Edit contact ');
			});
		},

		createContact: function (clientid) {
			Relapse.addToPane('RightPane', BASE_URL + 'contact/edit/clientid/'+clientid, 'Add new contact ');
		},

		deleteContactFromGrid: function (grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				if (confirm("Are you sure you want to delete this?")) {
					$.post(BASE_URL + 'contact/delete/__validation_token/'+VALIDATION_TOKEN, {id: id}, function () {
						$('.pReload',grid).click();
					});
				}
			});
		}
	}
})(jQuery);

Relapse.Clients = new Relapse.ClientManager();
