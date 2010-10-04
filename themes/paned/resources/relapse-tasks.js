
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
			var $this = this;
			if (cmd == 'New') {
				Relapse.addToPane('RightPane', BASE_URL + 'task/edit', 'New task');
			} else if (cmd == 'Edit') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					var title = $($(this).find("td")[1]).find('div').text();
					if (id > 0) {
						Relapse.addToPane('RightPane', BASE_URL + 'task/edit/id/'+id, 'Edit task ' + title);
					}
				});
			} else if (cmd == 'Start') {
				var id = null;
				$('.trSelected',grid).each (function () {
					id = $(this).attr('id').replace('row', '');
					
				});

				if (id > 0) {
					$this.startTiming(id);
				}
				
			} else if (cmd == 'Timesheet') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					var title = $($(this).find("td")[1]).find('div').text();
					if (id > 0) {
						$this.openTimesheet(id, title);
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

		completeTasks: function (cmd, grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				if (id > 0) {
					$.post(BASE_URL + 'task/complete/id/'+id+'/__validation_token/' + VALIDATION_TOKEN+'/_ajax/1', {id: id}, function () {
					});
				}
			});
			$('.pReload',grid).click();
		},

		/**
		 * Call to start timing a task
		 */
		startTiming: function (taskId) {
			if (taskId) {
				popup(BASE_URL + 'timesheet/record/id/' + taskId, 'timer', '500', '300');
			}
		},

		openTimesheet: function (id, title) {
			Relapse.addToPane('RightPane', BASE_URL + 'timesheet/detailedTimesheet/taskid/'+id, 'Timesheet for ' + title);
		},

		addTimeToTask: function (taskid, date, amount) {
			
		}
	}

})(jQuery);

Relapse.Tasks = new Relapse.TaskManager();