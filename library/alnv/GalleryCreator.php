<?php

namespace CatalogManager;

class GalleryCreator extends \Frontend {

    public $id;
    public $size;
    public $perRow;
    public $sortBy;
    public $perPage;
    public $orderSRC;
    public $fullsize;
    public $galleryTpl;
    public $metaIgnore;
    public $numberOfItems;
    public $multiSRC = [];
    public $useArrayFormat;
    public $usePreviewImage;
    public $previewImagePosition;

    protected $arrData = [];
    protected $objFiles = null;
    protected $arrPreviewImage = [];

    public function __construct( $arrMultiSRC, $arrGallery ) {

        foreach ( $arrGallery as $strKey => $strValue ) {

            $this->{$strKey} = $strValue;
        }

        if ( !$this->objFiles && is_array( $arrMultiSRC ) ) {

            $this->multiSRC = $arrMultiSRC;
            $this->objFiles = \FilesModel::findMultipleByUuids( $arrMultiSRC );
        }
    }


    public function render() {

        global $objPage;

        $objMainTemplate = new \FrontendTemplate( 'ce_gallery' );
        $objMainTemplate->class = 'ce_gallery';

        $arrImages = [];
        $arrAuxDate = [];

        if ( !$this->objFiles ) return '';
        if ( !$this->objFiles->count() ) return '';

        while ( $this->objFiles->next() ) {

            if ( isset( $arrImages[ $this->objFiles->path ] ) || !file_exists( TL_ROOT . '/' . $this->objFiles->path ) ) {

                continue;
            }

            if ( $this->objFiles->type == 'file' ) {

                $objFile = new \File( $this->objFiles->path, true );

                if ( !$objFile->isImage ) continue;

                $arrMeta = $this->getMetaData( $this->objFiles->meta, ($objPage->language ?: 'de') );

                if ( empty( $arrMeta ) ) {

                    if ($this->metaIgnore) {
                        continue;
                    }

                    elseif ($objPage->rootFallbackLanguage !== null) {
                        $arrMeta = $this->getMetaData( $this->objFiles->meta, $objPage->rootFallbackLanguage );
                    }
                }

                if (!isset($arrMeta['title']) || $arrMeta['title'] == '') {
                    $arrMeta['title'] = \StringUtil::specialchars($objFile->basename);
                }

                $arrImages[ $this->objFiles->path ] = [
                    'id' => $this->objFiles->id,
                    'uuid' => $this->objFiles->uuid,
                    'name' => $this->objFiles->basename,
                    'singleSRC' => $this->objFiles->path,
                    'alt' => $arrMeta['alt'] ?? '',
                    'title'  => $arrMeta['title'] ?? '',
                    'imageUrl' => $arrMeta['link'] ?? '',
                    'linkTitle' => $arrMeta['title'] ?? '',
                    'caption' => $arrMeta['caption'] ?? ''
                ];

                if (version_compare(VERSION, '4.4', '>=')) {
                    $arrImages[ $this->objFiles->path ]['filesModel'] = $this->objFiles->current();
                }

                $arrAuxDate[] = $objFile->mtime;
            }

            else {

                $objSubFiles = \FilesModel::findByPid( $this->objFiles->uuid );

                if ( $objSubFiles === null ) continue;

                while ( $objSubFiles->next() ) {

                    if ( $objSubFiles->type == 'folder' ) continue;

                    $objFile = new \File( $objSubFiles->path, true );

                    if ( !$objFile->isImage )  continue;

                    $arrMeta = $this->getMetaData( $objSubFiles->meta, ($objPage->language ?: 'de') );

                    if ( empty( $arrMeta ) ) {

                        if ($this->metaIgnore) continue;

                        elseif ($objPage->rootFallbackLanguage !== null) {

                            $arrMeta = $this->getMetaData( $objSubFiles->meta, $objPage->rootFallbackLanguage );
                        }
                    }

                    if (isset($arrMeta['title']) && $arrMeta['title'] === '') {
                        $arrMeta['title'] = \StringUtil::specialchars($objFile->basename);
                    }

                    $arrImages[ $objSubFiles->path ] = [
                        'id' => $objSubFiles->id,
                        'uuid' => $objSubFiles->uuid,
                        'name' => $objFile->basename,
                        'singleSRC' => $objSubFiles->path,
                        'alt' => $arrMeta['alt'] ?? '',
                        'title'  => $arrMeta['title'] ?? '',
                        'imageUrl' => $arrMeta['link'] ?? '',
                        'linkTitle' => $arrMeta['title'] ?? '',
                        'caption' => $arrMeta['caption'] ?? '',
                    ];

                    if ( version_compare(VERSION, '4.4', '>=' ) ) {
                        $arrImages[ $objSubFiles->path ]['filesModel'] = $objSubFiles->current();
                    }

                    $arrAuxDate[] = $objFile->mtime;
                }
            }
        }

        switch ( $this->sortBy ) {

            case 'name_asc':

                uksort( $arrImages, 'basename_natcasecmp' );

                break;

            case 'name_desc':

                uksort( $arrImages, 'basename_natcasercmp' );

                break;

            case 'date_asc':

                array_multisort( $arrImages, SORT_NUMERIC, $arrAuxDate, SORT_ASC );

                break;

            case 'date_desc':

                array_multisort( $arrImages, SORT_NUMERIC, $arrAuxDate, SORT_DESC );

                break;

            case 'random':

                shuffle( $arrImages );

                break;

            case 'custom':

                if ($this->orderSRC != '') {

                    $arrTmp = \StringUtil::deserialize($this->orderSRC, true);

                    if ( !empty( $arrTmp ) && is_array( $arrTmp ) ) {

                        $arrOrder = array_map( function () {}, array_flip( $arrTmp ));

                        foreach ( $arrImages as $strKey => $arrValue ) {

                            if ( array_key_exists( $arrValue['uuid'], $arrOrder ) ) {

                                $arrOrder[ $arrValue['uuid'] ] = $arrValue;
                                unset( $arrImages[$strKey] );
                            }
                        }

                        if ( !empty( $arrImages ) ) {

                            $arrOrder = array_merge( $arrOrder, array_values( $arrImages ) );
                        }

                        $arrImages = array_values( array_filter( $arrOrder ) );

                        unset( $arrOrder );
                    }
                }

                break;
        }

        $intOffset = 0;
        $arrImages = array_values( $arrImages );

        if ( $this->usePreviewImage ) {

            $inrPosition = 0;

            switch ( $this->previewImagePosition ) {

                case 'first':

                    $inrPosition = 0;

                    break;

                case 'middle':

                    $inrPosition = ceil( ( count( $arrImages ) - 1 ) / 2 );

                    break;

                case 'last':

                    $inrPosition = count( $arrImages ) - 1;

                    break;
            }

            if ( $arrImages[ $inrPosition ] ) {

                $arrPreviewImage = $arrImages[ $inrPosition ];
                $arrPreviewImage['size'] = $this->size;
                $arrPreviewImage['fullsize'] = $this->fullsize;

                $this->arrPreviewImage = $arrPreviewImage;
            }
        }

        if ( $this->numberOfItems > 0 ) {

            $arrImages = array_slice( $arrImages, 0, $this->numberOfItems );
        }

        $intTotal = count( $arrImages );
        $intLimit = $intTotal;

        if ( $this->perPage > 0 && $this->sortBy != 'random' ) {

            $strID = 'page_g' . $this->id;
            $intPage = ( \Input::get( $strID ) !== null ) ? \Input::get( $strID ) : 1;

            if ( $intPage < 1 || $intPage > max( ceil( $intTotal/$this->perPage ), 1 ) ) {

                $objCatalogException = new CatalogException();
                $objCatalogException->set404();
            }

            $intOffset = ( $intPage - 1) * $this->perPage;
            $intLimit = min( $this->perPage + $intOffset, $intTotal );
            $objPagination = new \Pagination( $intTotal, $this->perPage, \Config::get('maxPaginationLinks'), $strID );
            $objMainTemplate->pagination = $objPagination->generate("\n  ");
        }

        $arrBody = [];
        $intRowCount = 0;
        $intColWidth = floor( 100 / $this->perRow );
        $intMaxWidth = ( TL_MODE == 'BE' ) ? floor( ( 640 / $this->perRow ) ) : floor( ( \Config::get('maxImageWidth') / $this->perRow ) );
        $strLightBoxID = 'lightbox[lb' . $this->id . ']';

        for ( $i = $intOffset; $i < $intLimit; $i = ( $i + $this->perRow ) ) {

            $strRowClass = '';

            if ( $intRowCount == 0 ) {
                $strRowClass .= ' row_first';
            }

            if (($i + $this->perRow) >= $intLimit ) {
                $strRowClass .= ' row_last';
            }

            $strRowOEClass = ( ( $intRowCount % 2) == 0 ) ? ' even' : ' odd';

            for ( $j=0; $j < $this->perRow; $j++ ) {

                $strRowTDClass = '';

                if ( $j == 0 ) {

                    $strRowTDClass .= ' col_first';
                }

                if ( $j == ( $this->perRow - 1 ) ) {

                    $strRowTDClass .= ' col_last';
                }

                $objCell = new \stdClass();
                $strKey = 'row_' . $intRowCount . $strRowClass . $strRowOEClass;

                if (!isset($arrImages[($i+$j)])) {
                    continue;
                }

                if (!is_array($arrImages[($i+$j)]) || ($j+$i) >= $intLimit) {
                    $objCell->colWidth = $intColWidth . '%';
                    $objCell->class = 'col_'. $j . $strRowTDClass;
                } else {

                    $arrImages[($i+$j)]['size'] = $this->size;
                    $arrImages[($i+$j)]['fullsize'] = $this->fullsize;

                    if ( version_compare(VERSION, '4.4', '>=' ) ) {
                        $this->addImageToTemplate( $objCell, $arrImages[($i+$j)], $intMaxWidth, $strLightBoxID, $arrImages[($i+$j)]['filesModel'] );
                    }
                    else {
                        $this->addImageToTemplate( $objCell, $arrImages[($i+$j)], $intMaxWidth, $strLightBoxID );
                    }

                    $objCell->colWidth = $intColWidth . '%';
                    $objCell->class = 'col_'. $j . $strRowTDClass;
                    $objCell->meta = [
                        'title' => $arrImages[($i+$j)]['title'] ?? '',
                        'caption' => $arrImages[($i+$j)]['caption'] ?? '',
                        'alt' => $arrImages[($i+$j)]['alt'] ?? '',
                        'link' => $arrImages[($i+$j)]['link'] ?? ''
                    ];
                }

                $arrBody[$strKey][$j] = $objCell;
            }

            ++$intRowCount;
        }

        if ( $this->useArrayFormat ) {

            return is_array( $arrBody ) ? array_values( $arrBody ) : [];
        }

        $this->setDataContainer();

        $objTemplate = new \FrontendTemplate( $this->galleryTpl );
        $objTemplate->setData($this->arrData);
        $objTemplate->body = $arrBody;

        $objMainTemplate->images = $objTemplate->parse();

        return $objMainTemplate->parse();
    }


    public function getPreviewImage() {

        return Upload::generateImage( $this->arrPreviewImage, [

            'imageTemplate' => $this->imageTemplate
        ]);
    }


    protected function setDataContainer(){

        $this->arrData = [

            'hl' => 'h1',
            'id' => $this->id,
            'typePrefix' => 'ce_',
            'sortBy' => $this->sortBy,
            'perRow' => $this->perRow,
            'perPage' => $this->perPage,
            'multiSRC' => $this->multiSRC,
            'orderSRC' => $this->orderSRC,
            'classes' => [ 'first', 'last' ],
            'metaIgnore' => $this->metaIgnore,
            'galleryTpl' => $this->galleryTpl,
            'numberOfItems' => $this->numberOfItems,
        ];
    }
}