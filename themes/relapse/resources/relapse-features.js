
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
			}

			// refresh any tablegrid lists too, just in case :p
			$('.milestone-entry .pReload').click();
		}
	}

	$().ready(function () {
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

})(jQuery);

Relapse.Features = new Relapse.FeatureManager();