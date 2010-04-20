
(function ($) {
	Relapse.TaskManager = function () {
		
	}

	Relapse.TaskManager.prototype = {
		/**
		 * Make sure that the timespent is formatted nicely
		 */
		preProcessTableData: function (data) {
			if (data && data.rows && data.rows.length) {
				for (var i = 0; i < data.rows.length; i++) {
					var row = data.rows[i];
					var timespent = parseFloat(row.cell[2]);
					if (timespent > 0) {
						timespent = timespent / 3600;
						row.cell[2] = timespent.toFixed(2);
					}
				}
			}
			return data;
		},

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
						this.startTiming(id);
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
		},

		/**
		 * Call to start timing a task
		 */
		startTiming: function (taskId) {
			if (taskId) {
				popup(BASE_URL + 'timesheet/record/id/' + taskId, 'timer', '500', '300');
			}
		}
	}

})(jQuery);

Relapse.Tasks = new Relapse.TaskManager();