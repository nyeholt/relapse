
(function ($) {
	Relapse.TaskManager = function () {
		
	}

	Relapse.TaskManager.prototype = {
		tableCommand: function (cmd, grid) {
			if (cmd == 'New') {
				Relapse.createDialog('taskdialog', {title: 'Create Task', url: BASE_URL + 'task/edit'});
			} else if (cmd == 'Edit') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0) {
						Relapse.createDialog('taskdialog', {title: 'Edit Task', url: BASE_URL + 'task/edit/id/'+id});
					}
				});
			} else if (cmd == 'Start') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0) {
						popup(BASE_URL + 'timesheet/record/id/' + id, 'timer', '500', '300');
					}
				});
				
			} else if (cmd == 'Timesheet') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0) {
						Relapse.createDialog('timesheetdialog', {title: 'Timesheet', url: BASE_URL + 'timesheet/detailedTimesheet/taskid/'+id});
					}
				});
			} else if (cmd == 'Delete') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0 && confirm("Are you sure you want to delete this?")) {
						$.post(BASE_URL + 'task/delete/id/'+id+'/__validation_token/' + VALIDATION_TOKEN+'/_ajax/1', {id: id}, function () {
							$('.pReload',grid).click();
						});
					}
				});
			}
		}
	}

})(jQuery);

Relapse.Tasks = new Relapse.TaskManager();