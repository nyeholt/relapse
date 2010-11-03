
(function ($) {
	Relapse.ProjectManager = function () {
		
	}

	Relapse.ProjectManager.prototype = {
		newProject: function (client) {
			Relapse.createDialog('projectdialog', {title: 'Create Project', url: BASE_URL + 'project/edit/clientid/'+client});
		},

		open: function (cmd, grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				var title = $($(this).find("td")[1]).find('div').text();
				window.location.href =  BASE_URL + 'project/view/id/'+id;
				return;
			});
		}
	}
})(jQuery);

Relapse.Projects = new Relapse.ProjectManager();