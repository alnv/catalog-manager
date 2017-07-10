
var CatalogManager = CatalogManager || {};

(function () {

    'use strict';

    CatalogManager.fineUploader =  function ( objElement, objOptions ) {

        objOptions = getOptions( objOptions );

        var objParams = {
            
            element: objElement,
            debug: objOptions.debug,
            multiple: objOptions.multiple,

            failedUploadTextDisplay: {

                mode: 'custom',
                maxChars: 512,
                responseProperty: 'error'
            },

            request: {

                inputName: objOptions.name,
                endpoint: window.location.href,

                params: {
                    name: objOptions.name,
                    action: 'catalogFineUploader',
                    REQUEST_TOKEN: objOptions.REQUEST_TOKEN
                }
            }
        };

        return new qq.FineUploader( objParams );
    };


    function getOptions( objOptions ) {

        var objDefault = {

            debug: false,
            sizeLimit: '0',
            multiple: false
        };

        for ( var strOption in objDefault ) {

            if ( objDefault.hasOwnProperty( strOption ) ) {

                if ( isEmpty( objOptions[ strOption ] ) ) {

                    objOptions[ strOption ] = objDefault[ strOption ];
                }
            }
        }

        return objOptions;
    }


    function isEmpty( varValue ) {

        if ( typeof varValue === 'undefined' ) {

            return true;
        }

        if ( varValue === null && typeof varValue === 'object' ) {

            return true;
        }

        if ( typeof varValue === 'string' && !varValue.length ) {

            return true;
        }

        if ( typeof varValue === 'object' && typeof varValue.length != 'undefined' ) {

            if ( !varValue.length ) return true;
        }

        return false;
    }

})();
