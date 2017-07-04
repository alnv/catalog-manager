
var CatalogManager = CatalogManager || {};

(function () {

    'use strict';

    CatalogManager.fineUploader =  function ( objElement, objOptions ) {

        objOptions = getOptions( objOptions );

        var objParams = {

            validation: {},
            element: objElement,
            debug: objOptions.debug,
            multiple: objOptions.multiple
        };

        if ( !isEmpty( objOptions.sizeLimit ) && objOptions.sizeLimit != '0' ) {

            objParams.validation.sizeLimit = objOptions.sizeLimit;
        }

        if ( !isEmpty( objOptions.allowedExtensions ) && objOptions.allowedExtensions.length ) {

            var arrAllowedExtensions = [];

            for ( var i = 0; i < objOptions.allowedExtensions.length; i++ ) {

                if ( !isEmpty( objOptions.allowedExtensions[i] ) ) {

                    arrAllowedExtensions.push( objOptions.allowedExtensions[i] );
                }
            }

            if ( arrAllowedExtensions.length ) {

                objParams.validation.allowedExtensions = arrAllowedExtensions;
            }
        }

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
