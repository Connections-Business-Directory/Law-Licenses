;jQuery(document).ready(function ($) {

	/**
	 * Law Licenses Add / Remove license.
	 */
	var CNLL_License = {

		init : function() {
			// this.clone();
			this.add();
			this.remove();
			this.clearable();
			this.reindex();
			this.sortable();
		},
		clone : function( row, key ) {

			var source = $( row );

			// Clone the row.
			var clone = source.clone( true );

			// Change the id and name attributes to the supplied day/period variable.
			clone.find( 'input, select' ).each( function() {

				var name = $( this ).attr( 'name' );

				name = name.replace( /(\[\d+\])/, '[' + parseInt( key ) + ']' );

				// Update name and id attributes.
				$( this )
					.attr( 'name', name )
					.attr( 'id', name );

				// Reset input/select values.
				$( this ).not( 'select' ).val( '' );
				$( this ).not( 'input' ).find( 'option:selected' ).removeAttr( 'selected' );
			});


			var $inp = clone.find( 'input:text' ),
			    $cle = clone.find( '.cnll-clearable__clear' );

			// $inp.on( 'input', function() {
				$cle.toggle( !!this.value );
			// });

			// $cle.on( 'touchstart click', function( e ) {
			// 	e.preventDefault();
			// 	$inp.val( '' ).trigger( 'input' );
			// });


			// Increment the data-key attribute.
			clone.attr( 'data-key', key );

			// Unhide the cloned <tr>.
			clone.toggle();

			return clone;
		},
		add : function() {

			$( '.cnll-add-license' ).on( 'click', function() {

				var table = $( '#cn-law-licenses' );

				var row = $( this ).closest( 'tr' );

				// Increment the row counter.
				var data = table.cnllCount( 1 ).data();

				// Insert the cloned row after the current row.
				row.after( CNLL_License.clone( row, data.count ) );

				// After adding a row, the row input need to be reindexed.
				CNLL_License.reindex();

				// if ( 1 < data.count ) {
				//
				// 	$( '.cnll-remove-license' ).removeClass( 'disabled' );
				// }
			});
		},
		remove : function() {

			$( '.cnll-remove-license' ).on( 'click', function() {

				var table = $( '#cn-law-licenses' );

				// Get table data.
				var data = table.data();

				if ( 1 < data.count ) {

					var row = $( this ).closest( 'tr' );

					// Decrement the period counter for the day.
					table.cnllCount( -1 );

					row.remove();

					// After removing a row, the row inputs need to be reindexed.
					CNLL_License.reindex();

				}

				// if ( 1 >= table.data( 'count' ) ) {
				//
				// 	$( '.cnll-remove-license' ).addClass( 'disabled' );
				// }

			});
		},
		reindex : function() {

			// Process each row.
			$( '#cn-law-licenses tr' ).each( function( i, el ) {

				var row = $( el );

				// In each row find the inputs.
				row.find( 'input, select' ).each( function() {

					// Grab the name of the current row being processed.
					var name = $( this ).attr( 'name' );

					// Replace the name with the current day and index.
					name = name.replace( /(\[\d+\])/, '[' + parseInt( i ) + ']' );

					// Update both the name and id attributes with the new day and index.
					$( this ).attr( 'name', name ).attr( 'id', name );
				});

				row.attr( 'data-key', i );
			});

			var table = $( '#cn-law-licenses' );

			if ( 1 >= table.data( 'count' ) ) {

				$( '.cnll-remove-license' ).addClass( 'disabled' );

			} else {

				$( '.cnll-remove-license' ).removeClass( 'disabled' );
			}
		},
		clearable : function() {

			/**
			 * Clearable text inputs
			 * @link https://stackoverflow.com/a/6258628/5351316
			 */

			$( '.cnll-clearable' ).each( function() {

				var object = $( this );

				var input = object.find( 'input:text' ),
				    clear = object.find( '.cnll-clearable__clear' );

				clear.toggle( !!input.val() );

				input.on( 'input', function() {

					var input = $( this );
					var clear = input.next( '.cnll-clearable__clear' );

					// console.log( input.val() );
					clear.toggle( !!input.val() );
				});

				clear.on( 'touchstart click', function( e ) {
					e.preventDefault();

					var input = $( this ).prev( 'input:text' );

					// console.log( input.val() );
					input.val( '' ).trigger( 'input' );
				});

			});

		},
		sortable : function() {

			var fixHelperModified = function( e, tr ) {

				    var $originals = tr.children();
				    var $helper = tr.clone();

				    $helper.children().each( function( index ) {
					    $( this ).width( $originals.eq( index ).width() )
				    });

				    return $helper;
			    },
			    updateIndex       = function( e, ui ) {

				    // $( 'td.index', ui.item.parent() ).each( function( i ) {
					 //    $( this ).html( i + 1 );
				    // } );

				    // After moving a row, the rows need to be reindexed.
				    CNLL_License.reindex();
			    };

			$( '#cn-law-licenses tbody' ).sortable({
				helper: fixHelperModified,
				containment: 'parent',
				cursor: 'move',
				handle: 'i.fa.fa-sort',
				placeholder: 'widget-placeholder',
				stop:   updateIndex
			}).disableSelection();
		}
	};

	CNLL_License.init();

	// Counter Functions Credit:
	// http://stackoverflow.com/a/5656660
	$.fn.cnllCount = function( val ) {

		return this.each( function() {

			var data = $( this ).data();

			if ( ! ( 'count' in data ) ) {

				data['count'] = 0;
			}

			data['count'] += val;
		});
	};

});
