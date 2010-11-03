
(function ($) {
	Relapse.ClientManager = function () {
	}

	Relapse.ClientManager.prototype = {
		newClient: function () {
			Relapse.createDialog('clientdialog', {title: 'Create Client', url: BASE_URL + 'client/edit'});
		},

		openClient: function (cmd, grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				var title = $($(this).find("td")[1]).find('div').text();
				window.location.href =  BASE_URL + 'client/view/id/'+id;
			});
		},

		editClient: function (cmd, grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				var title = $($(this).find("td")[1]).find('div').text();
				Relapse.createDialog('clientdialog', {title: 'Edit Client', url: BASE_URL + 'client/edit/id/'+id});
			});
		},

		deleteClient: function (cmd, grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				if (confirm("Are you sure you want to delete this client?")) {
					$.post(BASE_URL + 'client/delete/__validation_token/'+VALIDATION_TOKEN, {id: id}, function () {
						$('.pReload',grid).click();
					});
				}
			});
		},

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
