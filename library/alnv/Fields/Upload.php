<?php

namespace CatalogManager;

class Upload {


    public static function generate( $arrDCAField, $arrField ) {

        $blnMultiple = Toolkit::getBooleanByValue( $arrField['multiple'] );

        $arrDCAField['eval']['files'] = true;
        $arrDCAField['eval']['multiple'] = $blnMultiple;
        $arrDCAField['eval']['filesOnly'] = Toolkit::getBooleanByValue( $arrField['filesOnly'] );

        if ( $blnMultiple || $arrField['fileType'] == 'gallery' ) {

            $arrDCAField['eval']['fieldType'] = 'checkbox';
            $arrDCAField['load_callback'] = [ [ 'DCACallbacks', 'setMultiSrcFlags' ] ];
        }

        else {

            $arrDCAField['eval']['fieldType'] = 'radio';
        }

        if ( $arrField['fileType'] == 'gallery' ) {

            $arrDCAField['eval']['multiple'] = true;

            // @todo custom orderBy
        }

        if ( $arrField['extensions'] ) {

            $arrDCAField['eval']['extensions'] = $arrField['extensions'];
        }

        if ( $arrField['path'] ) {

            $arrDCAField['eval']['path'] = $arrField['path'];
        }

        return $arrDCAField;
    }


    public static function parseValue ( $varValue, $arrField, $arrCatalog = [] ) {

        if ( $arrField['multiple'] || $arrField['fileType'] == 'gallery' ) {
            
            $varValue = Toolkit::deserialize( $varValue );
        }

        switch ( $arrField['fileType'] ) {

            case 'image':

                if ( !$arrField['disableImageRendering'] ) {

                    return static::renderImage( $varValue, $arrField, $arrCatalog );
                }

                return static::renderDefaultFileValue( $varValue );

                break;

            case 'gallery':

                return static::renderGallery( $varValue, $arrField, $arrCatalog );

                break;

            case 'file':

                if ( !$arrField['disableFileRendering'] ) {

                    return static::renderFile( $varValue, $arrField, $arrCatalog );
                }

                return static::renderDefaultFileValue( $varValue );

                break;

            default:

                return static::renderDefaultFileValue( $varValue );

                break;
        }
    }


    public static function renderDefaultFileValue( $varValue ) {

        if ( is_array( $varValue ) ) {

            $arrFiles = [];

            foreach ( $varValue as $strUuid ) {

                $arrFiles[] = static::getImagePath( $strUuid );
            }

            return $arrFiles;
        }

        else {

            return static::getImagePath( $varValue );
        }
    }


    public static function renderGallery( $varValue, $arrField, $arrCatalog ) {

        if ( !empty( $varValue ) && is_array( $varValue ) ) {

            $strTemplate = $arrField['galleryTemplate'] ? $arrField['galleryTemplate'] : 'gallery_default';

            $objGallery = new GalleryCreator( $varValue, [

                'id' => $arrCatalog['id'],
                'size' => $arrField['size'],
                'galleryTpl' => $strTemplate,
                'perRow' => $arrField['perRow'],
                'sortBy' => $arrField['sortBy'],
                'perPage' => $arrField['perPage'],
                'fullsize' => $arrField['fullsize'],
                'metaIgnore' => $arrField['metaIgnore'],
                'numberOfItems' => $arrField['numberOfItems'],

                'orderSRC' => '', // @todo
            ]);

            return $objGallery->render();
        }

        return $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['noGalleryImages'];
    }
    

    public static function renderImage( $varValue, $arrField, $arrCatalog ) {

        if ( is_array( $varValue ) ) {

            $arrImages = [];

            foreach ( $varValue as $strUuid ) {

                $arrImages[] = static::generateImage( static::createImageArray( $strUuid, $arrField, $arrCatalog ), $arrField );
            }

            return $arrImages;
        }

        return static::generateImage( static::createImageArray( $varValue, $arrField, $arrCatalog ), $arrField );
    }


    public static function renderFile( $varValue, $arrField, $arrCatalog ) {

        if ( is_array( $varValue ) ) {

            $arrFiles = [];

            foreach ( $varValue as $intIndex => $strUuid ) {

                $arrFiles[] = static::generateEnclosure( static::createEnclosureArray( $strUuid, $arrField, $arrCatalog ) )->enclosure[0];
            }

            return $arrFiles;
        }

        $objFile = static::generateEnclosure( static::createEnclosureArray( $varValue, $arrField, $arrCatalog ) );

        $objFile->enclosure[0]['name'] = $arrCatalog[ $arrField['fileText'] ] ? $arrCatalog[ $arrField['fileText'] ] : $objFile->enclosure[0]['name'];
        $objFile->enclosure[0]['title'] = $arrCatalog[ $arrField['fileTitle'] ] ? $arrCatalog[ $arrField['fileTitle'] ] : $objFile->enclosure[0]['title'];

        return $objFile->enclosure[0];
    }


    public static function createImageArray( $varValue, $arrField, $arrCatalog ) {

        return [

            'size' => $arrField['size'],
            'fullsize' => $arrField['fullsize'],
            'alt' => $arrCatalog[ $arrField['imageAlt'] ],
            'href' => $arrCatalog[ $arrField['imageURL'] ],
            'singleSRC' => static::getImagePath( $varValue ),
            'title' => $arrCatalog[ $arrField['imageTitle'] ],
            'caption' => $arrCatalog[ $arrField['imageCaption'] ]
        ];
    }


    public static function createEnclosureArray( $varValue, $arrField, $arrCatalog ) {

        $arrReturn = [

            'enclosure' => [ $varValue ]
        ];

        return $arrReturn;
    }


    public static function getImagePath( $singleSRC ) {

        if ( $singleSRC ) {

            $objModel = \FilesModel::findByUuid( $singleSRC );

            if ( $objModel && is_file( TL_ROOT . '/' . $objModel->path ) ) {

                return $objModel->path;
            }
        }

        return $singleSRC;
    }


    public static function generateImage( $arrImage, $arrField = [] ) {

        $strTemplate = $arrField['imageTemplate'] ? $arrField['imageTemplate'] : 'ce_image';

        $objPicture = new \FrontendTemplate( $strTemplate );
        
        \Controller::addImageToTemplate( $objPicture, $arrImage );

        return $objPicture->parse();
    }


    public static function generateEnclosure( $arrEnclosure ) {

        $objEnclosure = new \stdClass();

        \Controller::addEnclosuresToTemplate( $objEnclosure, $arrEnclosure ) ;

        return $objEnclosure;
    }
}