(function () {

    'use strict';

    if ( typeof window.addEventListener != 'undefined' ) {

        window.addEventListener( 'DOMContentLoaded', initialize, false );
    }

    function initialize() {

        var arrFields = document.querySelectorAll( '.ctlg_awesomplete' );

        if ( typeof arrFields != 'undefined' && arrFields.length ) {

            var objOptions = {

                end: 3,
                start: 0,
                startswith: ''
            };

            for ( var i = 0; i < arrFields.length; i++ ) {

                var objField = arrFields[i];

                objOptions.startswith = objField.dataset.startswith;
                objOptions.end = objField.dataset.end ? objField.dataset.end : 3;
                objOptions.start = objField.dataset.start ? objField.dataset.start : 0;

                var objAwesompleteConfig = {};

                if ( objOptions.startswith.length ) {

                    objAwesompleteConfig.data = function ( strText, strInput ) {

                        if ( startswith( strInput, objOptions.start, objOptions.end, objOptions.startswith ) ) {

                            return strText;
                        }
                    };

                    objAwesompleteConfig.filter = Awesomplete.FILTER_STARTSWITH;
                }

                new Awesomplete( objField, objAwesompleteConfig );
            }
        }
    }

    function startswith( strInput, intStart, intEnd, $strPrefix ) {

        return strInput.substr( intStart, intEnd ) == $strPrefix;
    }

})();