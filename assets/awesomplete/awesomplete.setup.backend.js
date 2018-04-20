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
                startswith: '',
                multiple: false
            };

            for ( var i = 0; i < arrFields.length; i++ ) {

                var objField = arrFields[i];

                objOptions.startswith = objField.dataset.startswith;
                objOptions.end = objField.dataset.end ? objField.dataset.end : 3;
                objOptions.start = objField.dataset.start ? objField.dataset.start : 0;
                objOptions.multiple = objField.dataset.multiple ? true : false;

                var objAwesompleteConfig = {};

                if ( objOptions.startswith.length ) {

                    objAwesompleteConfig.data = function ( strText, strInput ) {

                        if ( startswith( strInput, objOptions.start, objOptions.end, objOptions.startswith ) ) {

                            return strText;
                        }
                    };

                    objAwesompleteConfig.filter = Awesomplete.FILTER_STARTSWITH;
                }

                if ( objOptions.multiple ) {

                    objAwesompleteConfig.filter = function ( strText, strInput ) {

                        return Awesomplete.FILTER_CONTAINS( strText, strInput.match(/[^,]*$/)[0] );
                    };

                    objAwesompleteConfig.item = function ( strText, strInput ) {

                        return Awesomplete.ITEM( strText, strInput.match(/[^,]*$/)[0] );
                    };

                    objAwesompleteConfig.replace = function ( strText ) {

                        var strName = this.input.name;
                        var strDatalist = 'ctrl_dl_' +  strName;
                        var strBefore = this.input.value.match(/^.+,\s*|/)[0];
                        var objDatalist = document.getElementById( strDatalist );

                        if ( typeof objDatalist.options != 'undefined' && objDatalist.options.length ) {

                            for ( var i = 0; i < objDatalist.options.length; i++ ) {

                                if ( objDatalist.options[i].text == strBefore ) {

                                    strBefore = objDatalist.options[i].value;
                                }

                                if ( objDatalist.options[i].text == strText ) {

                                    strText = objDatalist.options[i].value;
                                }
                            }
                        }

                        this.input.value = strBefore + strText + ', ';
                    };
                }

                new Awesomplete( objField, objAwesompleteConfig );
            }
        }
    }

    function startswith( strInput, intStart, intEnd, $strPrefix ) {

        return strInput.substr( intStart, intEnd ) == $strPrefix;
    }

})();