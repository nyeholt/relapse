
(function ($) {
	Relapse.IssueManager = function () {
		
	}

	Relapse.IssueManager.prototype = {
		tableCommand: function (cmd, grid, contextUrl) {
			if (cmd == 'New') {
				Relapse.addToPane('RightPane', contextUrl, 'Add new issue ');
			} else if (cmd == 'Open') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					var title = $($(this).find("td")[1]).find('div').text();
					Relapse.addToPane('RightPane', BASE_URL + 'issue/edit/id/'+id, 'Edit issue ' + title);
				});
			} else if (cmd == 'Delete') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (confirm("Are you sure you want to delete this?")) {
						$.post(BASE_URL + 'issue/delete/__validation_token/'+VALIDATION_TOKEN, {id: id}, function () {
							$('.pReload',grid).click();
						});
					}
				});
			} else if (cmd == 'Export All') {
				location.href = BASE_URL + 'issue/csvExport/unlimited/1';
			}
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
						$('.pReload',grid).click();
					}
				});
			});
		}
	}


})(jQuery);

Relapse.Issues = new Relapse.IssueManager();
