/*!
 * JavaScript to update page URL when a redirect is viewed, ensuring that the
 * page is scrolled to the id when it's a redirect with fragment.
 *
 * This is loaded in the top queue, so avoid unnecessary dependencies
 * like mediawiki.Title or mediawiki.Uri.
 */
( function ( mw, $ ) {
	var profile = $.client.profile(),
		canonical = mw.config.get( 'wgInternalRedirectTargetUrl' ),
		fragment = null,
		shouldChangeFragment, index;

	index = canonical.indexOf( '#' );
	if ( index !== -1 ) {
		fragment = canonical.slice( index );
	}

	// Never override the fragment if the user intended to look at a different section
	shouldChangeFragment = fragment && !location.hash;

	// Replace the whole URL if possible, otherwise just change the fragment
	if ( canonical && history.replaceState ) {
		if ( !shouldChangeFragment ) {
			// If the current page view has a fragment already, don't override it
			canonical = canonical.replace( /#.*$/, '' );
			canonical += location.hash;
		}

		// RM: This section of code imported from MW 1.33 to fix redirect-to-section functionality failing most of the time. See https://phabricator.wikimedia.org/T110501 et al.
		// Note that this will update the hash in a modern browser, retaining back behaviour
		history.replaceState( /* data= */ history.state, /* title= */ document.title, /* url= */ canonical );
		if ( shouldChangeFragment ) {
			// Specification for history.replaceState() doesn't require browser to scroll,
			// so scroll to be sure (see also T110501). Support for IE10.
			node = document.getElementById( fragment.slice( 1 ) );
			if ( node ) {
				node.scrollIntoView();
			}
		}

		// …except for IE 10 and 11. Prod it with a location.hash change.
		if ( shouldChangeFragment && profile.name === 'msie' && profile.versionNumber >= 10 ) {
			location.hash = fragment;
		}

	} else if ( shouldChangeFragment ) {
		if ( profile.layout === 'webkit' && profile.layoutVersion < 420 ) {
			// Released Safari w/ WebKit 418.9.1 messes up horribly
			// Nightlies of 420+ are ok
			return;
		}

		location.hash = fragment;
	}

	if ( shouldChangeFragment && profile.layout === 'gecko' ) {
		// Mozilla needs to wait until after load, otherwise the window doesn't
		// scroll.  See <https://bugzilla.mozilla.org/show_bug.cgi?id=516293>.
		// There's no obvious way to detect this programmatically, so we use
		// version-testing.  If Firefox fixes the bug, they'll jump twice, but
		// better twice than not at all, so make the fix hit future versions as
		// well.
		$( function () {
			if ( location.hash === fragment ) {
				location.hash = fragment;
			}
		} );
	}

}( mediaWiki, jQuery ) );
