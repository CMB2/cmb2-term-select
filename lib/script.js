( function( window, document, $, undefined ) {
	'use strict';

	var app = {};

	app.setupAutocomplete = function( field_id, taxonomy ) {
		var $field = $( document.getElementById( field_id + '_name' ) );

		if ( ! $field.length ) {
			return console.warn( 'Missing field input for ' + field_id );
		}

		var position = { offset: '0, -1' };
		if ( typeof isRtl !== 'undefined' && isRtl ) {
			position.my = 'right top';
			position.at = 'right bottom';
		}

		var args = {
			source    : app.ajax_url + '/' + taxonomy + '?cmb2-term-select',
			delay     : 500,
			minLength : 3,
			position  : position,
			open : function() {
				$( this ).addClass( 'open' );
			},
			close : function() {
				$( this ).removeClass( 'open' );
			},
			select : function( event, ui ) {
				$field.val( ui.item.label );
				$( document.getElementById( field_id + '_id' ) ).val( ui.item.value );

				return false;
			}
		};

		$field.autocomplete( $.extend( args, $field.data() ) );
	};

	app.init = function() {
		app = window.cmb2_term_select_field = $.extend( app, window.cmb2_term_select_field );

		if ( ! app.field_ids ) {
			return console.warn( 'Missing CMB2 term_select field data!', app, window.cmb2_term_select_field );
		}

		for ( var i = app.field_ids.length - 1; i >= 0; i-- ) {
			app.setupAutocomplete( app.field_ids[i].id, app.field_ids[i].taxonomy );
		}
	};

	$( app.init );

} )( window, document, jQuery );
