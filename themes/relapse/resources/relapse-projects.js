
(function ($) {
	Relapse.ProjectManager = function () {
		
	}

	Relapse.ProjectManager.prototype = {
		newClient: function () {
			Relapse.addToPane('RightPane', BASE_URL + 'client/edit', 'Add new client');
		},
		
		openClient: function (cmd, grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				var title = $($(this).find("td")[1]).find('div').text();
				Relapse.addToPane('CenterPane', BASE_URL + 'client/view/id/'+id, title);
			});
		},

		editClient: function (cmd, grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				var title = $($(this).find("td")[1]).find('div').text();
				Relapse.addToPane('RightPane', BASE_URL + 'client/edit/id/'+id, 'Edit ' + title);
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
		}
	}
})(jQuery);

Relapse.Projects = new Relapse.ProjectManager();