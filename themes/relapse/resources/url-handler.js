
;(function($) {

	/**
	 * A URL handler that manages the state based on parameters in the URL. 
	 *
	 * Components that are interested in it can call 
	 *
	 * LateralMinds.Urls.getAction('key') 
	 *
	 * which will return any value associated with that key. This should be used when a component is being initialised to see
	 * if there's any init data that component should use
	 *
	 * Calling
	 * 
	 * LateralMinds.Urls.setAction('key', 'value'[, boolean forceTrigger])
	 * 
	 * will change the current URL to have #key=value appended to it (making that url bookmarkable somewhat). Note that
	 * this does NOT trigger any functionality already attached as listeners for that action (@see listForOpen). 
	 * A third parameter can be passed to this method to explicitly force that functionality to fire if there are
	 * handlers defined. 
	 *
	 * Components can register to the handler to be notified when a certain action comes up if the user changes URL without navigating
	 * away from the page (for example, clicking a back button)
	 *
	 * LateralMinds.Urls.listenForOpen('action', callback, scope);
	 * LateralMinds.Urls.listenForClose('action', callback, scope);
	 * 
	 * The callback will be called with the value corresponding to the 'action' in the URL whenever 'action' newly
	 * appears in the URL (a timer regularly checks the state of the URL and can detect when it's changed). 
	 * 
	 */ 
	LateralMinds.UrlHandler = function () {
		this.inited = false;
		// parse any URL arguments
		this.arguments = null;
		
		// need to store a 'current' hash so that we can periodically check to see if
		// the user went backwards or forwards, so we can then update the listeners to let them know
		this.currentHash = null;
		
		this.openHandlers = {};
		this.closeHandlers = {};

		
		this.checkHash();
		if (this.arguments == null) {
			this.arguments = {};
		}
		this.inited = true;
	}

	LateralMinds.UrlHandler.prototype = {
		/**
		 * Get a list of all the actions in the URL at the moment
		 */
		getActions: function () {
			return this.arguments;
		},
		/**
		 * Get the action in the URL for the given key. Returns null if it's not found
		 */
		getAction: function (key) {
			// check args
			if (this.arguments[key] != null) {
				return this.arguments[key];
			}

			return null;
		},

		/**
		 * Set an action into the URL, removing whatever is there. 
		 * 
		 * This does NOT trigger the action specified by 'key' unless
		 * 'forceTrigger' is set and == true
		 */
		setAction: function (key, value, forceTrigger) {
			this.resetArgs();
			this.arguments[key] = value;

			var newHash = "#" + key + "=" + value;
			
			// change the current hash to match so that the 'newHash' functionality doesn't 
			// trigger immediately after the change if forceTrigger is false or undefined
			if (forceTrigger == undefined || forceTrigger == null || forceTrigger == false) {
				this.setHash(newHash.substring(1));
			}
			location.hash = newHash;
		},
		
		resetArgs: function () {
			this.arguments = {};
		},
		
		/**
		 * Add a listener that gets called when an action is activated
		 * by a back/forward click (ie when the hash changes without a page reload)
		 */
		listenForOpen: function(key, fn, scope) {
			var existing = this.openHandlers[key];
			if (existing == null) { 
				existing = [];
			}
			
			existing.push({
				callback: fn,
				scope: scope
			});
			
			this.openHandlers[key] = existing;
		},
		
		setHash: function (val) {
			this.currentHash = val;
		},
		
		/**
		 * Check to see if the hash has changed
		 */
		checkHash: function () {
			if (location.hash && location.hash.indexOf('#') == 0) {
				var hash = location.hash.substring(1);
				
				// is it the same as the old one? don't bother parsing
				if (hash == this.currentHash) {
					return;
				}
				
				var oldArgs = this.arguments;
				var oldHash = this.currentHash;
				this.setHash(hash);
				this.arguments = this.parseArgs(hash);

				if (this.inited) {
					// trigger the fact that there's a new hash
					this.newHash();
				}
			}
		},

		/**
		 * Parse the URL arguments and take everything from the 'hash'. Separate arguments 
		 * are managed by separate
		 */
		parseArgs: function (hash) {
			var args = {};
			// figure out if we have a key-value thing. If not, we'll leave it to be handled by something else. 
			if (hash.match(/([a-zA-Z0-9:/_-]+)=([a-zA-Z0-9:/_-]+)/)) {
				// split it up 
				var keyValPairs = hash.split('|');
				for (var i in keyValPairs) {
					var bits = keyValPairs[i].split('=');
					if (bits.length == 2) {
						args[bits[0]] = bits[1];
					}
				}
			}

			return args;
		},
		
		/**
		 * For each action defined on the URL, lets trigger their open listeners
		 */
		newHash: function () {
			for (var key in this.arguments) {
				// get its handlers
				var handlers = this.openHandlers[key];
				if (handlers != null) {
					for (var i = 0, c = handlers.length; i < c; i++) {
						var handler = handlers[i].callback;
						var scope = handlers[i].scope;
						var actionVal = this.arguments[key];
						handler.call(scope == null ? this : scope, actionVal);
					}
				}
			}
		}
	}

	LateralMinds.Urls = new LateralMinds.UrlHandler();
	setInterval(function() { LateralMinds.Urls.checkHash(); }, 100);

})(jQuery);