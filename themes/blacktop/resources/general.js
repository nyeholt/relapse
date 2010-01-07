/** Because we've created our own custom tabs, we need to create a 
dummy $().tabs() method so that our existing templates can still be used **/

$.fn.tabs = function() {

}

var selectedTabPage = null;
function moveTabs()
{
	// move the tab options so that they're in the right hand menu
	var tabOptions = $('ul.tab-options');
	if (tabOptions.length > 0) {
		var tabNum = 0;
		tabOptions.each(function() {
			if ($('#right').length > 0) {
				$('#right').prepend('<div class="box"><h2>Options ...</h2><ul class="tab-options" id="tab'+tabNum+'"></ul></div>');
				$('#tab'+tabNum).append($(this).html());
				$(this).remove();
				tabNum++;
			}
		});

		var tabUrls = $('ul.tab-options a');
		tabUrls.each(function() {
			var targetTab = $(this).attr('href');
			if (targetTab.indexOf("#") >= 0) {
				var targetTab = targetTab.substring(targetTab.indexOf("#"), targetTab.length);
				if (targetTab != location.hash && selectedTabPage != null) {
					$(targetTab).hide();						
				} else {
					if (selectedTabPage != null) {
						selectedTabPage.hide();
					}
					selectedTabPage = $(targetTab);
				}

				$(this).click(function() {
					if (selectedTabPage != null) {
						selectedTabPage.hide();
					}
					$(targetTab).show();
					selectedTabPage = $(targetTab);
					unFocus();
		
					return false;
				});
			}
		});
		
		unFocus();
	}
}



/**
 * Borrowed from JQuery Tabs!!
 */
function unFocus() {
    scrollTo(0, 0);
}

$().ready (function() {
	moveTabs();
	
	// move the 'parent-links' div if it exists
	var parentLinks = $('#parent-links');
	var rightLinks = $('#right');
	if (parentLinks.length > 0 && rightLinks.length > 0) {
		parentLinks.remove();
		$('#right').prepend('<div class="box" id="parentLinksBox"></div>');
		$('#parentLinksBox').append(parentLinks);
	}
	

	
	
	
});