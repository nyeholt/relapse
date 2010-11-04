
(function ($) {
	Relapse.IssueManager = function () {
		
	}

	Relapse.IssueManager.prototype = {
		tableCommand: function (cmd, grid, contextUrl) {
			if (cmd == 'New') {
				Relapse.createDialog('issuedialog', {title: 'Add new Issue', url: contextUrl});
			} else if (cmd == 'Open') {
				
			} else if (cmd == 'Delete') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (confirm("Are you sure you want to delete this?")) {
						$.post(BASE_URL + 'issue/delete/__validation_token/'+VALIDATION_TOKEN, {id: id}, function () {
							$('.pReload').click();
						});
					}
				});
			} else if (cmd == 'Export All') {
				location.href = BASE_URL + 'issue/csvExport/unlimited/1';
			}
		},

		exportIssues: function (clientid, projectid) {
			var url = BASE_URL + 'issue/csvExport/unlimited/1';
			if (clientid) {
				url += '/clientid/'+clientid;
			}
			if (projectid) {
				url += '/projectid/'+projectid;
			}
			location.href = url;
		},

		editIssue: function (grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				Relapse.createDialog('issuedialog', {title: 'Edit Issue', url: BASE_URL + 'issue/edit/id/'+id});
			});
		},

		createTask: function (grid) {
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				var url = BASE_URL + 'task/linkedtaskform/id/'+id+'/type/Issue';
				Relapse.createDialog('newLinkedTask', {title: 'Add Task', url: url});
			});
		},

		/**
		 * Immediately start timing this task
		 */
		startTiming: function (grid) {
			// first create the task, then launch timer for it
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				$.post(BASE_URL + 'task/startnewtask', {id: id, type: 'Issue', prefix: 'Working on '}, function (data) {
					if (parseInt(data)) {
						// lets start timing for this one
						Relapse.Tasks.startTiming(parseInt(data));
						$('.pReload').click();
					}
				});
			});
		}
	}


})(jQuery);

Relapse.Issues = new Relapse.IssueManager();