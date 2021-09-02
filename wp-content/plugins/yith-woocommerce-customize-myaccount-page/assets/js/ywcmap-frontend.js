jQuery( document ).ready( function( $ ) {
	"use strict";

	function YITH_WCMAP_Avatar() {
		// const
		const self = this;

		let modal		= $( '#yith-wcmap-avatar' ),
			overlay		= modal.find( '.avatar-modal-overlay' ),
			content		= modal.find( '.avatar-modal' ),
			template	= wp.template( 'ywcmap-avatar-modal-content' ),
			// flags
			_opened 	= false,
			_cleanup 	= false;

		// Modal actions.
		self.centerModal = function() {

			let window_w = $( window ).width(),
				window_h = $( window ).height(),
				margin = ((window_w - 40) > 450) ? window_h / 8 + 'px' : '0',
				width = ((window_w - 40) > 450) ? 450 + 'px' : 'auto';

			content.css( {
				'margin-top': margin,
				'margin-bottom': margin,
				'width': width,
			} );
		};
		self.openModal = function() {
			if ( _opened ) {
				return false;
			}
			_opened = true;
			// show modal
			content.append( template({}) );
			modal.show();

			overlay.fadeIn( 500 );
			content.fadeIn( 500, function() {
				self.initActions();
			});
		};
		self.closeModal = function() {
			if ( ! _opened ) {
				return;
			}

			overlay.fadeOut( 500 );
			content.fadeOut( 500, function() {
				content.find( '.avatar-modal-content' ).remove();
				modal.hide();
				// Cleanup if needed
				self.clearTempAvatar();

				_cleanup = false;
				_opened = false;
			} );
		};
		self.resetModalContent = function( event ) {
			event.preventDefault();

			content.find( '.avatar-modal-content' ).replaceWith( template({}) ).fadeIn();
			// Cleanup if needed
			self.clearTempAvatar();
		};

		// Ajax Request.
		self.ajaxRequest = function( action, data, preload ) {

			if( null == data ) {
				data = new FormData();
			}

			data.append( 'action', action );
			data.append( 'security', yith_wcmap.actionNonce );
			data.append( 'context', 'frontend' );

			return $.ajax( {
				url: yith_wcmap.ajaxUrl,
				data: data,
				contentType: false,
				processData: false,
				dataType: 'json',
				type: 'POST',
				beforeSend: function() {
					if( preload ) {
						content.block( {
							message: null,
							overlayCSS: {
								background: '#fff no-repeat center',
								opacity: 0.5,
								cursor: 'none'
							}
						} );
					}
				},
				complete: function() {
					if( preload ) {
						content.unblock();
					}
				}
			} )
				.fail( ( response ) => {
					console.log( response );
				} );
		}
		self.uploadAvatar = function( event ) {
			event.preventDefault();

			let form = $( this ).closest( 'form' ),
				dataForm = new FormData();

			// Process Form file
			$.each( $( this ), function( i, tag ) {
				$.each( $( tag )[0].files, function( i, file ) {
					dataForm.append( tag.name, file );
				} );
			} );

			self.ajaxRequest( 'upload_avatar', dataForm, true )
				.done( ( response ) => {
					if ( response?.success ) {
						_cleanup = true;
						form.replaceWith( response.data.html );

						content.find( '.avatar-modal-content' ).addClass( 'avatar-uploaded' );
					}
				} );
		};
		self.setAvatar = function() {
			self.ajaxRequest( 'set_avatar', null, true )
				.done( ( response ) => {
					window.location.reload();
				} );
		};
		self.clearTempAvatar = function() {

			if( ! _cleanup ) {
				return;
			}

			self.ajaxRequest( 'clear_temp_avatar', null, false )
				.done( ( response ) => {
					_cleanup = false;
				} );
		};
		self.resetAvatar = function( event ) {
			event.preventDefault();

			self.ajaxRequest( 'reset_avatar', null, true )
				.done( ( response ) => {
					if ( response?.success ) {
						window.location.reload();
					}
				} );
		};

		// Init actions.
		self.initActions = function() {
			modal.on( 'change', '#ywcmap_custom_avatar', self.uploadAvatar );
			modal.on( 'click', '.avatar-modal-close', self.closeModal );

			modal.on( 'click', '.reset-avatar a.cancel', self.resetModalContent );
			modal.on( 'click', '.reset-avatar a.reset', self.resetAvatar );
			modal.on( 'click', '.set-avatar button', self.setAvatar );
		}

		// Init trigger.
		self.init = function() {

			self.centerModal();
			$( document ).on( 'click', '.yith-wcmap .user-avatar', self.openModal );
			$( window ).on( 'resize', self.centerModal );
		}

		self.init();
	}

	// If modal template in page, start handler.
	if( $('#tmpl-ywcmap-avatar-modal-content').length && $( '#yith-wcmap-avatar' ).length && typeof wp?.template != 'undefined' ) {
		new YITH_WCMAP_Avatar();
	}

	$( document ).on( "click", ".group-opener", function( ev ) {
		ev.preventDefault();

		var container = $( this ).closest( 'li' );

		if ( container.hasClass( 'is-tab' ) && $( window ).width() >= 480 ) {
			container.toggleClass( 'is-hover' );
			return;
		}

		$( this ).find( 'span.item-opener i' ).toggleClass( 'fa-chevron-down' ).toggleClass( 'fa-chevron-up' );
		$( this ).next( '.myaccount-submenu' ).slideToggle();
	} )
} );