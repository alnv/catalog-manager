<?php

namespace CatalogManager;

class DownloadsCreator extends \Frontend {


    public $sortBy;
    public $orderSRC;
    public $metaIgnore;
    public $multiSRC = [];

    protected $arrData = [];
    protected $objFiles = null;


    public function __construct( $arrMultiSRC, $arrEnclosure ) {

        foreach ( $arrEnclosure as $strKey => $strValue ) {

            $this->{$strKey} = $strValue;
        }

        if ( !$this->objFiles && is_array( $arrMultiSRC ) ) {

            $this->multiSRC = $arrMultiSRC;
            $this->objFiles = \FilesModel::findMultipleByUuids( $arrMultiSRC );
        }

        $this->setDataContainer();
    }


    public function render() {

        global $objPage;

        $arrFiles = [];
        $arrAuxDate = [];

        $objFiles = $this->objFiles;
        $arrAllowedDownload = trimsplit( ',', strtolower( \Config::get('allowedDownload') ) );

        while ( $objFiles->next() ) {

            if ( isset( $files[$objFiles->path] ) || !file_exists( TL_ROOT . '/' . $objFiles->path ) ) {

                continue;
            }

            if ( $objFiles->type == 'file' ) {

                $objFile = new \File( $objFiles->path, true );

                if ( !in_array( $objFile->extension, $arrAllowedDownload ) || preg_match( '/^meta(_[a-z]{2})?\.txt$/', $objFile->basename ) ) {

                    continue;
                }

                $arrMeta = $this->getMetaData( $objFiles->meta, $objPage->language );

                if ( empty( $arrMeta ) ) {

                    if ( $this->metaIgnore ) {

                        continue;
                    }

                    elseif ( $objPage->rootFallbackLanguage !== null ) {

                        $arrMeta = $this->getMetaData( $objFiles->meta, $objPage->rootFallbackLanguage );
                    }
                }

                if ( $arrMeta['title'] == '' ) {

                    $arrMeta['title'] = specialchars( $objFile->basename );
                }

                $strHref = \Environment::get('request');

                if ( preg_match( '/(&(amp;)?|\?)file=/', $strHref ) ) {

                    $strHref = preg_replace( '/(&(amp;)?|\?)file=[^&]+/', '', $strHref );
                }

                $strHref .= ( ( \Config::get('disableAlias') || strpos( $strHref, '?' ) !== false) ? '&amp;' : '?' ) . 'file=' . \System::urlEncode( $objFiles->path );

                $arrFiles[ $objFiles->path ] = [

                    'href' => $strHref,
                    'meta' => $arrMeta,
                    'id' => $objFiles->id,
                    'mime' => $objFile->mime,
                    'uuid' => $objFiles->uuid,
                    'path' => $objFile->dirname,
                    'link' => $arrMeta['title'],
                    'name' => $objFile->basename,
                    'caption' => $arrMeta['caption'],
                    'extension' => $objFile->extension,
                    'icon' => \Image::getPath( $objFile->icon ),
                    'filesize' => $this->getReadableSize( $objFile->filesize, 1 ),
                    'title' => specialchars( sprintf($GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename ) ),
                ];

                $arrAuxDate[] = $objFile->mtime;
            }

            else {

                $objSubFiles = \FilesModel::findByPid( $objFiles->uuid );

                if ( $objSubFiles === null ) {

                    continue;
                }

                while ( $objSubFiles->next() ) {

                    if ( $objSubFiles->type == 'folder' ) {

                        continue;
                    }

                    $objFile = new \File( $objSubFiles->path, true );

                    if (!in_array( $objFile->extension, $arrAllowedDownload ) || preg_match( '/^meta(_[a-z]{2})?\.txt$/', $objFile->basename ) ) {

                        continue;
                    }

                    $arrMeta = $this->getMetaData( $objSubFiles->meta, $objPage->language );

                    if (empty($arrMeta)) {

                        if ($this->metaIgnore) {

                            continue;
                        }

                        elseif ($objPage->rootFallbackLanguage !== null) {

                            $arrMeta = $this->getMetaData( $objSubFiles->meta, $objPage->rootFallbackLanguage );
                        }
                    }

                    if ($arrMeta['title'] == '') {

                        $arrMeta['title'] = specialchars( $objFile->basename );
                    }

                    $strHref = \Environment::get('request');

                    if ( preg_match( '/(&(amp;)?|\?)file=/', $strHref ) ) {

                        $strHref = preg_replace('/(&(amp;)?|\?)file=[^&]+/', '', $strHref);
                    }

                    $strHref .= ( ( \Config::get('disableAlias') || strpos( $strHref, '?' ) !== false ) ? '&amp;' : '?' ) . 'file=' . \System::urlEncode( $objSubFiles->path );

                    $arrFiles[ $objSubFiles->path ] = [

                        'meta' => $arrMeta,
                        'href' => $strHref,
                        'mime' => $objFile->mime,
                        'id' => $objSubFiles->id,
                        'path' => $objFile->dirname,
                        'link' => $arrMeta['title'],
                        'uuid' => $objSubFiles->uuid,
                        'name' => $objFile->basename,
                        'caption' => $arrMeta['caption'],
                        'extension' => $objFile->extension,
                        'icon' => \Image::getPath( $objFile->icon ),
                        'filesize' => $this->getReadableSize( $objFile->filesize, 1 ),
                        'title' => specialchars( sprintf( $GLOBALS['TL_LANG']['MSC']['download'], $objFile->basename ) ),
                    ];

                    $arrAuxDate[] = $objFile->mtime;
                }
            }

            switch ( $this->sortBy ) {

                case 'name_asc':

                    uksort( $arrFiles, 'basename_natcasecmp' );

                    break;

                case 'name_desc':

                    uksort( $arrFiles, 'basename_natcasercmp' );

                    break;

                case 'date_asc':

                    array_multisort( $arrFiles, SORT_NUMERIC, $arrAuxDate, SORT_ASC );

                    break;

                case 'date_desc':

                    array_multisort( $arrFiles, SORT_NUMERIC, $arrAuxDate, SORT_DESC );

                    break;

                case 'random':

                    shuffle( $arrFiles );

                    break;
            }
        }

        $this->arrData['files'] = $arrFiles;

        $objTemplate = new \FrontendTemplate( $this->downloadsTpl );
        $objTemplate->setData( $this->arrData );

        return $objTemplate->parse();
    }


    protected function setDataContainer(){

        $this->arrData = [

            'hl' => 'h1',
            'id' => '',
            'typePrefix' => 'ce_',
            'sortBy' => $this->sortBy,
            'class' => 'ce_downloads',
            'metaIgnore' => $this->metaIgnore,
            'galleryTpl' => $this->downloadsTpl,
        ];
    }
}