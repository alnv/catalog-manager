<?php

namespace CatalogManager;

class Upload {


    public static function generate( $arrDCAField, $arrField ) {

        $arrDCAField['eval']['files'] = true;
        $arrDCAField['eval']['filesOnly'] = Toolkit::getBooleanByValue( $arrField['filesOnly'] );

        if ( $arrField['fileType'] == 'gallery' ) {

            $arrDCAField['eval']['multiple'] = true;
            $arrDCAField['eval']['fieldType'] = 'checkbox';
            $arrDCAField['load_callback'] = [ [ 'DcCallbacks', 'setMultiSrcFlags' ] ];

            if ( $arrField['sortBy'] == 'custom' && $arrField['orderField'] ) {

                $arrDCAField['eval']['orderField'] = $arrField['orderField'];
            }
        }

        if ( $arrField['fileType'] == 'image' ) {

            $arrDCAField['eval']['multiple'] = false;
            $arrDCAField['eval']['fieldType'] = 'radio';
        }

        if ( $arrField['fileType'] == 'file' ) {

            $arrDCAField['eval']['multiple'] = false;
            $arrDCAField['eval']['fieldType'] = 'radio';
        }

        if ( $arrField['fileType'] == 'files' ) {

            $arrDCAField['eval']['multiple'] = true;
            $arrDCAField['eval']['fieldType'] = 'checkbox';
            $arrDCAField['load_callback'] = [ [ 'DcCallbacks', 'setMultiSrcFlags' ] ];

            if ( $arrField['sortBy'] == 'custom' && $arrField['orderField'] ) {

                $arrDCAField['eval']['orderField'] = $arrField['orderField'];
            }
        }

        if ( $arrField['extensions'] ) {

            $arrDCAField['eval']['extensions'] = $arrField['extensions'];
        }

        if ( $arrField['path'] ) {

            $arrDCAField['eval']['path'] = $arrField['path'];
        }

        $arrDCAField['eval']['maxsize'] = $arrField['maxsize'];

        return $arrDCAField;
    }


    public static function parseValue ( $varValue, $arrField, $arrCatalog = [] ) {

        switch ( $arrField['fileType'] ) {

            case 'image':

                return static::renderImage( $varValue, $arrField, $arrCatalog );

                break;

            case 'gallery':

                $varValue = Toolkit::deserialize( $varValue );

                return static::renderGallery( $varValue, $arrField, $arrCatalog );

                break;

            case 'file':

                return static::renderFile( $varValue, $arrField, $arrCatalog );

                break;

            case 'files':

                $varValue = Toolkit::deserialize( $varValue );

                return static::renderFiles( $varValue, $arrField, $arrCatalog );

                break;
        }

        return '';
    }


    public static function parseThumbnails( $varValue, $arrField, $arrCatalog = [] ) {

        switch ( $arrField['fileType'] ) {

            case 'image':

                return static::renderThumbnail( $varValue, $arrField );

                break;

            case 'gallery':

                $strThumbnails = '';
                $varValues = Toolkit::deserialize( $varValue );

                if ( !empty( $varValues ) && is_array( $varValues ) ) {

                    $strThumbnails .= '<ul class="ctlg_thumbnails_preview">';

                    foreach ( $varValues as $varValue ) {

                        $strThumbnails .= '<li>' . static::renderThumbnail( $varValue, $arrField ) . '</li>';
                    }

                    $strThumbnails .= '</ul>';
                }

                return $strThumbnails;

                break;

            case 'file':

                $arrFile = static::createEnclosureArray( $varValue, $arrField, $arrCatalog );

                if ( is_array( $arrFile ) && !Toolkit::isEmpty( $arrFile['name'] ) ) {

                    return $arrFile['name'];
                }

                break;

            case 'files':

                $strFiles = '';
                $varValues = Toolkit::deserialize( $varValue );

                if ( !empty( $varValues ) && is_array( $varValues ) ) {

                    $arrNames = [];
                    $strFiles .= '<ul class="ctlg_files_preview">';

                    foreach ( $varValues as $varValue ) {

                        $arrFile = static::createEnclosureArray( $varValue, $arrField, $arrCatalog );

                        if ( is_array( $arrFile ) && !Toolkit::isEmpty( $arrFile['name'] ) ) {

                            $arrNames[] = '<li>' . $arrFile['name'] . '</li>';
                        }
                    }

                    $strFiles .= implode( ', ' , $arrNames ) . '</ul>';
                }

                return $strFiles;

                break;
        }

        return '';
    }


    public static function renderThumbnail( $varValue, $arrField = [] ) {

        if ( $varValue != '' ) {

            $objFile = \FilesModel::findByUuid( $varValue );

            if ($objFile !== null) {

                return \Image::getHtml( \Image::get( $objFile->path, 0, 0 ), '', 'class="'. $arrField['fieldname'] .'_preview ctlg_thumbnail_preview"' );
            }
        }

        return $varValue;
    }


    public static function parseAttachment ( $varValue, $arrField, $arrCatalog = [] ) {

        $objFile = \FilesModel::findByUuid( $varValue );

        if ( $objFile === null ) return '';

        return $objFile->path ?  $objFile->path : '';
    }


    public static function renderGallery( $varValue, $arrField, $arrCatalog ) {

        if ( !empty( $varValue ) && is_array( $varValue ) ) {

            $strTemplate = $arrField['galleryTemplate'] ? $arrField['galleryTemplate'] : 'gallery_default';
            $strOrderField = $arrCatalog[ $arrField['orderField'] ] ? $arrCatalog[ $arrField['orderField'] ] : '';

            $objGallery = new GalleryCreator( $varValue, [

                'id' => $arrCatalog['id'],
                'size' => $arrField['size'],
                'galleryTpl' => $strTemplate,
                'orderSRC' => $strOrderField,
                'perRow' => $arrField['perRow'],
                'sortBy' => $arrField['sortBy'],
                'perPage' => $arrField['perPage'],
                'fullsize' => $arrField['fullsize'],
                'metaIgnore' => $arrField['metaIgnore'],
                'numberOfItems' => $arrField['numberOfItems'],

                'imageTemplate' => $arrField['imageTemplate'],
                'useArrayFormat' => $arrField['useArrayFormat'],
                'usePreviewImage' => $arrField['usePreviewImage'],
                'previewImagePosition' => $arrField['previewImagePosition'],
            ]);

            return [

                'gallery' => $objGallery->render(),
                'preview' => $arrField['usePreviewImage'] ? $objGallery->getPreviewImage() : '',
            ];
        }

        return [

            'preview' => '',
            'gallery' => $arrField['useArrayFormat'] ? [] : ''
        ];
    }
    

    public static function renderFiles( $varValue, $arrField, $arrCatalog ) {

        if ( !empty( $varValue ) && is_array( $varValue ) ) {

            $strTemplate = $arrField['filesTemplate'] ? $arrField['filesTemplate'] : 'ce_downloads';
            $strOrderField = $arrCatalog[ $arrField['orderField'] ] ? $arrCatalog[ $arrField['orderField'] ] : '';

            $objDownloads = new DownloadsCreator( $varValue, [

                'orderSRC' => $strOrderField,
                'downloadsTpl' => $strTemplate,
                'sortBy' => $arrField['sortBy'],
                'metaIgnore' => $arrField['metaIgnore'],
                'useArrayFormat' => $arrField['useArrayFormat'],
            ]);

            return $objDownloads->render();
        }

        return $arrField['useArrayFormat'] ? [] : '';
    }


    public static function renderImage( $varValue, $arrField, $arrCatalog ) {

        if ( !is_string( $varValue ) ) {

            return $arrField['useArrayFormat'] ? [] : '';
        }

        $arrImage = static::createImageArray( $varValue, $arrField, $arrCatalog );

        if ( $arrField['useArrayFormat'] ) {

            return $arrImage;
        }

        return static::generateImage( $arrImage, $arrField );
    }


    public static function renderFile( $varValue, $arrField, $arrCatalog ) {

        if ( !is_string( $varValue ) ) {

            return $arrField['useArrayFormat'] ? [] : '';
        }

        $arrFile = static::createEnclosureArray( $varValue, $arrField, $arrCatalog );

        if ( $arrField['useArrayFormat'] ) {

            return $arrFile;
        }

        return static::generateEnclosure( $arrFile, $arrField );
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

        global $objPage;

        $strDownload = \Input::get( 'file', true );
        $objFileEntity = \FilesModel::findByUuid( $varValue );

        if ( !$objFileEntity->path || $objFileEntity->type != 'file' ) return [];

        $objFile = new \File( $objFileEntity->path, true );
        $strTitle = $arrCatalog[ $arrField['fileTitle'] ];
        $strDescription = $arrCatalog[ $arrField['fileText'] ];

        if ( !$strTitle ) {

            $strTitle = specialchars( $objFile->name );
        }

        $strHref = \Environment::get('request');

        if (preg_match('/(&(amp;)?|\?)file=/', $strHref)) {

            $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
        }

        $strHref .= ( ( \Config::get( 'disableAlias' ) || strpos( $strHref, '?' ) !== false) ? '&amp;' : '?' ) . 'file=' . \System::urlEncode( $objFile->value );
        $arrMeta = \Frontend::getMetaData( $objFileEntity->meta, $objPage->language );

        if ( empty( $arrMeta ) && $objPage->rootFallbackLanguage !== null ) {

            $arrMeta = \Frontend::getMetaData( $objFileEntity->meta, $objPage->rootFallbackLanguage );
        }

        if ($arrMeta['title'] == '') {

            $arrMeta['title'] = specialchars( $objFile->basename );
        }

        if ( $strDownload != '' && $objFileEntity->path ) \Controller::sendFileToBrowser( $strDownload );

        return [

            'href' => $strHref,
            'meta' => $arrMeta,
            'link' => $strTitle,
            'mime' => $objFile->mime,
            'id' => $objFileEntity->id,
            'path' => $objFile->dirname,
            'name' => $objFile->basename,
            'extension' => $objFile->extension,
            'icon' => \Image::getPath( $objFile->icon ),
            'filesize' => \Controller::getReadableSize( $objFile->filesize ),
            'title' => specialchars( $strDescription ?: sprintf( $GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename ) )
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


    public static function generateImage( $arrImage, $arrField = [] ) {

        $strTemplate = $arrField['imageTemplate'] ? $arrField['imageTemplate'] : 'ce_image';
        $objPicture = new \FrontendTemplate( $strTemplate );

        \Controller::addImageToTemplate( $objPicture, $arrImage );

        return $objPicture->parse();
    }


    public static function generateEnclosure( $arrEnclosure, $arrField = [] ) {

        $strTemplate = $arrField['fileTemplate'] ? $arrField['fileTemplate'] : 'ce_download';
        $objTemplate = new \FrontendTemplate( $strTemplate );
        $objTemplate->setData( $arrEnclosure );

        return $objTemplate->parse();
    }
}