<?php

namespace CatalogManager;

class Upload {

    public static function generate( $arrDCAField, $arrField ) {

        $blnMultiple = Toolkit::getBooleanByValue( $arrField['multiple'] );

        $arrDCAField['eval']['files'] = true;
        $arrDCAField['eval']['multiple'] = $blnMultiple;
        $arrDCAField['eval']['filesOnly'] = Toolkit::getBooleanByValue( $arrField['filesOnly'] );

        if ( $blnMultiple ) {

            $arrDCAField['eval']['fieldType'] = 'checkbox';
        }

        else {

            $arrDCAField['eval']['fieldType'] = 'radio';
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

        if ( $arrField['multiple'] ) {

            $varValue = Toolkit::deserialize( $varValue );
        }

        switch ( $arrField['fileType'] ) {

            case 'image':

                if ( !$arrField['disableImageRendering'] ) {

                    return static::renderImage( $varValue, $arrField, $arrCatalog );
                }

                return static::renderDefaultFileValue( $varValue );

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

    public static function renderImage( $varValue, $arrField, $arrCatalog ) {

        if ( is_array( $varValue ) ) {

            $arrImages = [];

            foreach ( $varValue as $strUuid ) {

                $arrImages[] = static::generateImage( static::createImageArray( $strUuid, $arrField, $arrCatalog ) );
            }

            return $arrImages;
        }

        return static::generateImage( static::createImageArray( $varValue, $arrField, $arrCatalog ) );
    }

    public static function renderFile( $varValue, $arrField, $arrCatalog ) {

        if ( is_array( $varValue ) ) {

            $arrFiles = [];

            foreach ( $varValue as $strUuid ) {

                $arrFiles[] = static::generateImage( static::createImageArray( $strUuid, $arrField, $arrCatalog ) );
            }

            return $arrFiles;
        }

        return static::generateImage( static::createImageArray( $varValue, $arrField, $arrCatalog ) );
    }

    public static function createImageArray( $varValue, $arrField, $arrCatalog ) {

        return [

            'size' => $arrField['size'],
            'fullsize' => $arrField['fullsize'],
            'alt' => $arrCatalog[ $arrField['imageAlt'] ],
            'singleSRC' => static::getImagePath( $varValue ),
            'title' => $arrCatalog[ $arrField['imageTitle'] ],
            'caption' => $arrCatalog[ $arrField['imageCaption'] ]
        ];
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

    public static function generateImage( $arrImage ) {

        $objPicture = new \stdClass();

        \Controller::addImageToTemplate( $objPicture, $arrImage );

        return $objPicture;
    }

    public static function generateEnclosure( $arrEnclosure ) {

        $objEnclosure = new \stdClass();

        \Controller::addEnclosuresToTemplate( $objEnclosure, $arrEnclosure) ;

        return $objEnclosure;
    }
}