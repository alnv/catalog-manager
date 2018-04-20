document.addEventListener( 'DOMContentLoaded', function( objDomEvent ) {

    'use strict';

    var arrFields = document.querySelectorAll( '.datepicker-field' );

    function isNull( varValue ) {

        return varValue === null && typeof varValue === "object";
    }

    function setupDatepicker( objInput ) {

        //
    }

    if ( !isNull( arrFields ) && typeof arrFields == 'object' && typeof arrFields.length != 'undefined' ) {

        for ( var index = 0; index < arrFields.length; index++ ) {

            setupDatepicker( arrFields[ index ] );
        }
    }
});