/**
 * cbpFWTabs.js v1.0.0
 * http://www.codrops.com
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2014, Codrops
 * http://www.codrops.com
 */
;( function( window ) {
	
	'use strict';

	function extend( a, b ) {
		for( var key in b ) { 
			if( b.hasOwnProperty( key ) ) {
				a[key] = b[key];
			}
		}
		return a;
	}

	function CBPFWTabs( el, options ) {
		this.el = el;
		this.options = extend( {}, this.options );
  		extend( this.options, options );
  		this._init();
	}

	CBPFWTabs.prototype.options = {
		start : 0
	};

	CBPFWTabs.prototype._init = function() {
		// tabs elems
		this.tabs = [].slice.call( this.el.querySelectorAll( 'nav > ul > li' ) );
		// content items
		this.items = [].slice.call( this.el.querySelectorAll( '.content-wrap > section' ) );
		// current index
		this.current = -1;

		var startIdx = this.options.start;
		var hash = window.location.hash;
		var storedTab = null;
		try {
			storedTab = localStorage.getItem( 'woowbot_active_tab' );
		} catch( e ) {}

		var matched = false;
		if ( hash ) {
			for ( var i = 0; i < this.tabs.length; i++ ) {
				var link = this.tabs[i].querySelector( 'a' );
				if ( link && link.getAttribute( 'href' ) === hash ) {
					startIdx = i;
					matched = true;
					break;
				}
			}
		}
		if ( !matched && storedTab !== null ) {
			var tabIndex = parseInt( storedTab, 10 );
			if ( !isNaN( tabIndex ) && tabIndex >= 0 && tabIndex < this.items.length ) {
				startIdx = tabIndex;
			}
		}

		// show current content item
		this._show( startIdx );
		// init events
		this._initEvents();
	};

	CBPFWTabs.prototype._initEvents = function() {
		var self = this;
		this.tabs.forEach( function( tab, idx ) {
			tab.addEventListener( 'click', function( ev ) {
				ev.preventDefault();
				self._show( idx );
				var link = tab.querySelector( 'a' );
				if ( link && link.getAttribute( 'href' ) ) {
					var href = link.getAttribute( 'href' );
					if ( window.history && window.history.replaceState ) {
						window.history.replaceState( null, null, href );
					} else {
						window.location.hash = href;
					}
				}
				try {
					localStorage.setItem( 'woowbot_active_tab', idx );
				} catch( e ) {}
			} );
		} );

		window.addEventListener( 'hashchange', function() {
			var hash = window.location.hash;
			if ( hash ) {
				for ( var i = 0; i < self.tabs.length; i++ ) {
					var link = self.tabs[i].querySelector( 'a' );
					if ( link && link.getAttribute( 'href' ) === hash ) {
						self._show( i );
						break;
					}
				}
			}
		} );
	};

	CBPFWTabs.prototype._show = function( idx ) {
		if( this.current >= 0 && this.tabs[ this.current ] && this.items[ this.current ] ) {
			this.tabs[ this.current ].className = this.items[ this.current ].className = '';
		}
		// change current
		this.current = ( idx !== undefined && idx >= 0 && idx < this.items.length ) ? idx : ( this.options.start >= 0 && this.options.start < this.items.length ? this.options.start : 0 );
		if ( this.tabs[ this.current ] ) {
			this.tabs[ this.current ].className = 'tab-current';
		}
		if ( this.items[ this.current ] ) {
			this.items[ this.current ].className = 'content-current';
		}
	};

	// add to global namespace
	window.CBPFWTabs = CBPFWTabs;

})( window );