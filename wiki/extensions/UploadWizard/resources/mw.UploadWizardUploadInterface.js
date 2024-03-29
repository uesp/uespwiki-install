( function ( mw, uw, $, OO ) {
	/**
	 * Create an interface fragment corresponding to a file input, suitable for Upload Wizard.
	 *
	 * @class mw.UploadWizardUploadInterface
	 * @mixins OO.EventEmitter
	 * @constructor
	 * @param {mw.UploadWizardUpload} upload
	 */
	mw.UploadWizardUploadInterface = function MWUploadWizardUploadInterface( upload ) {
		var
			ui = this;

		OO.EventEmitter.call( this );

		this.upload = upload;

		// may need to collaborate with the particular upload type sometimes
		// for the interface, as well as the uploadwizard. OY.
		this.$div = $( '<div class="mwe-upwiz-file"></div>' );
		this.div = this.$div.get( 0 );

		this.isFilled = false;

		this.$indicator = $( '<div class="mwe-upwiz-file-indicator"></div>' );

		this.visibleFilenameDiv = $( '<div class="mwe-upwiz-visible-file"></div>' )
			.append( this.$indicator )
			.append(
				'<div class="mwe-upwiz-visible-file-filename">' +
					'<div class="mwe-upwiz-file-preview"/>' +
						'<div class="mwe-upwiz-file-texts">' +
							'<div class="mwe-upwiz-visible-file-filename-text"/>' +
							'<div class="mwe-upwiz-file-status-line">' +
								'<div class="mwe-upwiz-file-status"></div>' +
							'</div>' +
						'</div>' +
					'</div>'
			);

		this.removeCtrl = new OO.ui.ButtonWidget( {
			label: mw.message( 'mwe-upwiz-remove' ).text(),
			title: mw.message( 'mwe-upwiz-remove-upload' ).text(),
			flags: 'destructive',
			icon: 'remove',
			framed: false
		} ).on( 'click', function () {
			ui.emit( 'upload-removed' );
		} );

		if ( mw.UploadWizard.config.defaults && mw.UploadWizard.config.defaults.objref !== '' ) {
			this.$imagePicker = this.createImagePickerField(
				this.upload.index,
				mw.UploadWizard.config.defaults.updateList === ''
			);
			this.visibleFilenameDiv.find( '.mwe-upwiz-file-status-line' )
				.append( this.$imagePicker );
		}

		this.visibleFilenameDiv.find( '.mwe-upwiz-file-status-line' )
			.append( this.removeCtrl.$element );

		this.$form = $( '<form>' )
				.addClass( 'mwe-upwiz-form' )
				.append( this.visibleFilenameDiv );

		$( this.div ).append( this.$form );

		// this.progressBar = ( no progress bar for individual uploads yet )
		// we bind to the ui div since unbind doesn't work for non-DOM objects
		$( this.div ).bind( 'transportProgressEvent', function () { ui.showTransportProgress(); } );
	};

	OO.mixinClass( mw.UploadWizardUploadInterface, OO.EventEmitter );

	/**
	 * Change the graphic indicator at the far end of the row for this file
	 *
	 * @param {string} statusClass Corresponds to a class mwe-upwiz-status which changes style of indicator.
	 */
	mw.UploadWizardUploadInterface.prototype.showIndicator = function ( statusClass ) {
		this.clearIndicator();
		// add the desired class and make it visible, if it wasn't already.
		this.$indicator.addClass( 'mwe-upwiz-status-' + statusClass ).css( 'visibility', 'visible' );
	};

	/**
	 * Reset the graphic indicator
	 */
	mw.UploadWizardUploadInterface.prototype.clearIndicator = function () {
		var ui = this;
		$.each( this.$indicator.attr( 'class' ).split( /\s+/ ), function ( i, className ) {
			if ( className.match( /^mwe-upwiz-status/ ) ) {
				ui.$indicator.removeClass( className );
			}
		} );
	};

	/**
	 * Set the status line for this upload with an internationalized message string.
	 *
	 * @param {string} msgKey Key for the message
	 * @param {Array} args Array of values, in case any need to be fed to the image.
	 */
	mw.UploadWizardUploadInterface.prototype.setStatus = function ( msgKey, args ) {
		var $s;
		if ( args === undefined ) {
			args = [];
		}
		// get the status line for our upload
		$s = $( this.div ).find( '.mwe-upwiz-file-status' );
		$s.msg( msgKey, args ).show();
	};

	/**
	 * Set status line directly with a string
	 *
	 * @param {string} html
	 */
	mw.UploadWizardUploadInterface.prototype.setStatusString = function ( html ) {
		$( this.div ).find( '.mwe-upwiz-file-status' ).html( html ).show();
	};

	/**
	 * Set additional status information
	 *
	 * @param {jQuery} [$status] If not given or null, additional status is cleared
	 */
	mw.UploadWizardUploadInterface.prototype.setAdditionalStatus = function ( $status ) {
		if ( this.$additionalStatus ) {
			this.$additionalStatus.remove();
		}
		this.$additionalStatus = $status;
		if ( this.$additionalStatus ) {
			$( this.div ).find( '.mwe-upwiz-file-status' ).after( this.$additionalStatus );
		}
	};

	/**
	 * Clear the status line for this upload (hide it, in case there are paddings and such which offset other things.)
	 */
	mw.UploadWizardUploadInterface.prototype.clearStatus = function () {
		$( this.div ).find( '.mwe-upwiz-file-status' ).hide();
		this.setAdditionalStatus( null );
	};

	/**
	 * Put the visual state of an individual upload into "progress"
	 *
	 * @param {number} fraction The fraction of progress. Float between 0 and 1
	 */
	mw.UploadWizardUploadInterface.prototype.showTransportProgress = function () {
		// if fraction available, update individual progress bar / estimates, etc.
		this.showIndicator( 'progress' );
		this.setStatus( 'mwe-upwiz-uploading' );
		this.setAdditionalStatus( null );
	};

	/**
	 * Show that upload is transported
	 */
	mw.UploadWizardUploadInterface.prototype.showStashed = function () {
		this.showIndicator( 'stashed' );
		this.setStatus( 'mwe-upwiz-stashed-upload' );
		this.setAdditionalStatus( null );
	};

	/**
	 * Show that transport has failed
	 *
	 * @param {string} code Error code from API
	 * @param {string} html Error message
	 * @param {jQuery} [$additionalStatus]
	 */
	mw.UploadWizardUploadInterface.prototype.showError = function ( code, html, $additionalStatus ) {
		this.showIndicator( 'error' );
		this.setStatusString( html );
		this.setAdditionalStatus( $additionalStatus );
	};

	/**
	 * Run this when the value of the file input has changed and we know it's acceptable -- this
	 * will update interface to show as much info as possible, including preview.
	 * n.b. in older browsers we only will know the filename
	 *
	 * @param {Object} imageinfo
	 * @param {File} file
	 */
	mw.UploadWizardUploadInterface.prototype.fileChangedOk = function ( imageinfo, file ) {
		var statusItems = [];

		this.updateFilename();

		// set the status string - e.g. "256 Kb, 100 x 200"
		if ( imageinfo && imageinfo.width && imageinfo.height ) {
			statusItems.push( imageinfo.width + '\u00d7' + imageinfo.height );
		}

		if ( file && file.size ) {
			statusItems.push( uw.units.bytes( file.size ) );
		}

		this.clearStatus();
		this.setStatusString( statusItems.join( ' \u00b7 ' ) );
	};

	/**
	 * Display thumbnail preview.
	 *
	 * @return {jQuery.Promise} Promise resolved when the thumbnail is displayed or when displaying it
	 *     fails
	 */
	mw.UploadWizardUploadInterface.prototype.showThumbnail = function () {
		var
			$preview = $( this.div ).find( '.mwe-upwiz-file-preview' ),
			deferred = $.Deferred();
		this.upload.getThumbnail().done( function ( thumb ) {
			mw.UploadWizard.placeThumbnail( $preview, thumb );
			deferred.resolve();
		} );
		return deferred.promise();
	};

	/**
	 * this does two things:
	 *   1 ) since the file input has been hidden with some clever CSS ( to avoid x-browser styling issues ),
	 *	  update the visible filename
	 *
	 *   2 ) update the underlying "title" which we are targeting to add to mediawiki.
	 *	  TODO silently fix to have unique filename? unnecessary at this point...
	 */
	mw.UploadWizardUploadInterface.prototype.updateFilename = function () {
		var $div,
			path = this.upload.getFilename();

		// visible filename
		this.$form.find( '.mwe-upwiz-visible-file-filename-text' )
			.text( path );

		if ( !this.isFilled ) {
			$div = $( this.div );
			this.isFilled = true;
			$div.addClass( 'filled' );
		}
	};

	/**
	* Create a checkbox to process the object reference parameter
	*
	* @param {number} index Number of the file for which the field is being created
	* @param {boolean} setDisabled Disable in case there already is an image in the referring list
	* @return {jQuery} A `div` containing a checkbox, label, and optional notice
	*/
	mw.UploadWizardUploadInterface.prototype.createImagePickerField = function ( index, setDisabled ) {
		var $fieldContainer = $( '<div>' ).attr( {
				'class': 'mwe-upwiz-objref-pick-image'
			} ),
			attributes = {
				type: 'checkbox',
				'class': 'imgPicker',
				id: 'imgPicker' + index,
				disabled: false,
				checked: false
			};

		if ( setDisabled ) {
			attributes.disabled = 'disabled';
		} else if ( index === 0 ) {
			attributes.checked = 'checked';
		}

		$fieldContainer.append(
			$( '<input>' ).attr( attributes ).on( 'click', function () {
				$( this )
					.prop( 'checked', true )
					.closest( '.mwe-upwiz-file' )
					.siblings()
					.find( '.imgPicker' )
					.prop( 'checked', false );
			} ),

			$( '<label>' ).attr( {
				'for': 'imgPicker' + index
			} ).text( this.getPickImageLabel() )
		);

		if ( setDisabled ) {
			$fieldContainer.append(
				$( '<div>' ).attr( {
					'class': 'mwe-upwiz-objref-notice-existing-image'
				} ).text( this.getExistingImageNotice() )
			);
		}

		return $fieldContainer;
	};

	mw.UploadWizardUploadInterface.prototype.getExistingImageNotice = function () {
		if ( mw.UploadWizard.config && mw.UploadWizard.config.display && mw.UploadWizard.config.display.noticeExistingImage ) {
			return mw.UploadWizard.config.display.noticeExistingImage;
		} else {
			return mw.message( 'mwe-upwiz-objref-notice-existing-image' ).text();
		}
	};

	mw.UploadWizardUploadInterface.prototype.getPickImageLabel = function () {
		if ( mw.UploadWizard.config && mw.UploadWizard.config.display && mw.UploadWizard.config.display.labelPickImage ) {
			return mw.UploadWizard.config.display.labelPickImage;
		} else {
			return mw.message( 'mwe-upwiz-objref-pick-image' ).text();
		}
	};

}( mediaWiki, mediaWiki.uploadWizard, jQuery, OO ) );
