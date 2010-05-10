
(function ($) {
	Relapse.FeatureManager = function () {
		
	}

	Relapse.FeatureManager.prototype = {
		/**
		 * Used to update a feature somewhere in the page
		 */
		updateFeatureList: function (feature) {
			var elem = $('#featurelist_'+feature.id);
			if (elem.length) {
				elem = elem[0];
				$('.feature-title h2 a', elem).text(feature.title);
				$('.estimate', elem).text(feature.estimated);
				$('.feature-description', elem).text(feature.description);
				$('.feature-assumptions', elem).text(feature.assumptions);
				$('.feature-questions', elem).text(feature.questions);
			} else {
				// maybe we just added... check whether we're on the feature detail list before refreshing
				if ($('.enableReorder').length > 0) {
					Relapse.closeDialog('featurelist');
					Relapse.createDialog('featurelist', {url: BASE_URL + 'feature/list/projectid/' + feature.projectid, width: 1000});
					return;
				}
			}

			// refresh any tablegrid lists too, just in case
			$('.milestone-entry .pReload').click();
		},

		tableCommand: function (cmd, grid, contextUrl) {
			if (cmd == 'New') {
				Relapse.addToPane('RightPane', contextUrl, 'Create new Feature');
			} else if (cmd == 'Open') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					var title = $($(this).find("td")[1]).find('div').text();
					if (id > 0) {
						Relapse.addToPane('RightPane', BASE_URL + 'feature/edit/id/'+id, 'Edit feature ' + title);
					}
				});
			} else if (cmd == 'Delete') {
				$('.trSelected',grid).each (function () {
					var id = $(this).attr('id').replace('row', '');
					if (id > 0 && confirm("Are you sure you want to delete this?")) {
						$.post(BASE_URL + 'feature/delete/_ajax/1/__validation_token/' + VALIDATION_TOKEN, {id: id}, function () {
							$('.pReload',grid).click();
						});
					}
				});
			}
		},

		/**
		 * Immediately start timing this feature
		 */
		startTiming: function (grid) {
			// first create the task, then launch timer for it
			$('.trSelected',grid).each (function () {
				var id = $(this).attr('id').replace('row', '');
				$.post(BASE_URL + 'task/startnewtask', {id: id, type: 'Feature', prefix: 'Working on Feature '}, function (data) {
					if (parseInt(data)) {
						// lets start timing for this one
						Relapse.Tasks.startTiming(parseInt(data));
						$('.pReload',grid).click();
					}
				});
			});
		}
	}

	$().ready(function () {
		$('#featureList').livequery(function () {
			$('#featureList ul').sortable({
				placeholder: 'ui-state-highlight'
			}).sortable('disable').css('opacity', '1');

			$('.enableReorder').click(function () {
				$('#featureList li div:not(div.feature-title)').hide();
				$('div.feature-title').addClass('dragTitle');
				$('#featureList ul').sortable('enable');
				$('.disableReorder').show();
				$('.saveOrder').show();
				$('.enableReorder').hide();
			});

			$('.disableReorder').click(function () {
				$('#featureList li div:not(div.feature-title)').show();
				$('div.feature-title').removeClass('dragTitle');
				$('#featureList ul').sortable('disable');
				$('.enableReorder').show();
				$('.disableReorder').hide();
			});

			$('.saveOrder').click(function () {
				$('#featureList ul').each(function() {
					var ids = $(this).sortable('toArray');
					if (ids) {
						var str = ids.toString();
						var url = $('#featureSort').attr('action');
						$.post(url, {ids: str});
					}
				});
				$('.saveOrder').hide();
			});
		});
	});

})(jQuery);

Relapse.Features = new Relapse.FeatureManager();