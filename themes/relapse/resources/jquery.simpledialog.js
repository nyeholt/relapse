
(function($) {
	var simpleDialogStack = [];

	/**
	 * A very simple overlay style dialog. Trigger it by calling
	 * $('element').simpleDialog();
	 * 
	 * To close it, simply call from anywhere in the code
	 * 
	 * $('element').simpleDialog('close');
	 */
	$.fn.simpleDialog = function (settings) {
		
		function closeDialog()
		{
			if (settings && settings.onClose) {
				settings.onClose.call(this);
			}

			var dialogToClose = simpleDialogStack.pop();
			if (dialogToClose) {
				dialogToClose.hide();
				// if there's still one on the top, then show it
				if (simpleDialogStack.length > 0) {
					simpleDialogStack[0].show();
				} else {
					// otherwise hide the mask etc
					$('#simple-dialog-mask').remove();
				}
			} else {
				$(this).each (function () {
					$(this).hide();
				});
				$('#simple-dialog-mask').remove();
			}
			
			return false;
		}

		if (settings == 'close') {
			$('body').find('select, input').css('visibility', 'hidden').css('visibility', 'visible');
			closeDialog();
			return;
		}

		// first, make sure it's displayed
		var options = $.extend({}, $.fn.simpleDialog.defaults, settings);

		var dialogMask = $('#simple-dialog-mask');
		// create the backdrop
		if (dialogMask.length == 0) {
			dialogMask = $('<div id="simple-dialog-mask"></div>').appendTo('body');
		}

		var maskHeight = $(document).height();  
        var maskWidth = $(window).width();  
      
        //Set height and width to mask to fill up the whole screen  
        dialogMask.css({'width':maskWidth,'height':maskHeight});

		dialogMask.fadeIn(50);	
		dialogMask.fadeTo("fast",0.8);	
		// dialogMask.show();

		if (!options.modal) {
			dialogMask.click (closeDialog);
		} 

		return this.each(function () {
			var $me = $(this);
			var $this = this;
			
			$me.addClass('simpleDialog');

			var winH = $(window).height();
			var winW = $(window).width();

			//Set the popup window to center
			if (options.height) {
				$me.css('height', options.height);
			}

			$me.css('width', options.width);

			$me.css('top',  options.top + $(window).scrollTop());
			$me.css('left', winW/2-$me.width()/2);  

			if ($me.find('div.dialogClose').length == 0) {
				$me.wrapInner('<div class="dialogContent" />');
				$me.prepend('<div class="dialogClose" >X</div>');

				$me.find('div.dialogClose').click(function(){
					$('body').find('select, input').css('visibility', 'visible');
					closeDialog();
				});	
			}

			if ($me.find('.dialogContent').length == 0) {
				// make sure there's a dialog content class
				$me.wrapInner('<div class="dialogContent" />');
			}

			$me.find('div.dialogTitle').remove();
			if (options.title != undefined) {
				$me.find('div.dialogClose').after('<div class="dialogTitle">'+options.title +'</div>');
			}

			// hack for IE6
			$('body').find('select, input').css('visibility', 'hidden');
			$('body').find('.simpleDialog select, .simpleDialog input').css('visibility', 'visible');

			// hide any currently shown dialogs
			if (simpleDialogStack.length > 0) {
				simpleDialogStack[0].hide();
			}

			// add me to the list of dialogs that are currently being shown
			simpleDialogStack.push($me);

			$me.show();

			// if we have a url to load, do so now
			if (options.url) {
				$me.find('.dialogContent').html('<div style="width: 50%; margin: 0px auto; text-align: center;"><p>Loading...</p></div>');
				$me.find('.dialogContent').load(settings.url, {_ajax: 1}, function (loadData) {
					if (options.dialogLoaded) {
						options.dialogLoaded.apply(this, arguments);
					}
				});
			}
		});
	}

	$.fn.simpleDialog.defaults = {
		width: 600,
		top: 80,
		modal: true	// is this dialog locked open (as in, the dialog has to handle closing itself)
	};

})(jQuery);