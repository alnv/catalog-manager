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

    protected $arrData = [];
    protected $objFiles = null;


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

                $arrMeta = $this->getMetaData( $this->objFiles->meta, $objPage->language );

                if ( empty( $arrMeta ) ) {

                    if ($this->metaIgnore) {

                        continue;
                    }

                    elseif ($objPage->rootFallbackLanguage !== null) {

                        $arrMeta = $this->getMetaData( $this->objFiles->meta, $objPage->rootFallbackLanguage );
                    }
                }

                if ( $arrMeta['title'] == '' ) {

                    $arrMeta['title'] = specialchars( $objFile->basename );
                }

                $arrImages[ $this->objFiles->path ] = [

                    'id'        => $this->objFiles->id,
                    'uuid'      => $this->objFiles->uuid,
                    'singleSRC' => $this->objFiles->path,
                    'name'      => $this->objFiles->basename,

                    'alt'       => $arrMeta['alt'],
                    'imageUrl'  => $arrMeta['link'],
                    'caption'   => $arrMeta['caption']
                ];

                $arrAuxDate[] = $objFile->mtime;
            }

            else {

                $objSubFiles = \FilesModel::findByPid( $this->objFiles->uuid );

                if ( $objSubFiles === null ) continue;

                while ( $objSubFiles->next() ) {

                    if ( $objSubFiles->type == 'folder' ) continue;

                    $objFile = new \File( $objSubFiles->path, true );

                    if ( !$objFile->isImage )  continue;

                    $arrMeta = $this->getMetaData( $objSubFiles->meta, $objPage->language );

                    if ( empty( $arrMeta ) ) {

                        if ($this->metaIgnore) continue;

                        elseif ($objPage->rootFallbackLanguage !== null) {

                            $arrMeta = $this->getMetaData( $objSubFiles->meta, $objPage->rootFallbackLanguage );
                        }
                    }

                    if ( $arrMeta['title'] == '' ) {

                        $arrMeta['title'] = specialchars($objFile->basename);
                    }

                    $arrImages[ $objSubFiles->path ] = [

                        'id'        => $objSubFiles->id,
                        'uuid'      => $objSubFiles->uuid,
                        'name'      => $objFile->basename,
                        'singleSRC' => $objSubFiles->path,

                        'alt'       => $arrMeta['alt'],
                        'imageUrl'  => $arrMeta['link'],
                        'caption'   => $arrMeta['caption']
                    ];

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

                array_multisort($arrImages, SORT_NUMERIC, $arrAuxDate, SORT_ASC );

                break;

            case 'date_desc':

                array_multisort( $arrImages, SORT_NUMERIC, $arrAuxDate, SORT_DESC );

                break;

            case 'random':

                shuffle( $arrImages );

                break;
        }

        $intOffset = 0;
        $arrImages = array_values( $arrImages );

        if ( $this->numberOfItems > 0 ) {

            $arrImages = array_slice( $arrImages, 0, $this->numberOfItems );
        }

        $intTotal = count( $arrImages );
        $intLimit = $intTotal;

        if ( $this->perPage > 0 && $this->sortBy != 'random' ) {

            $strID = 'page_g' . $this->id;
            $intPage = ( \Input::get( $strID ) !== null ) ? \Input::get( $strID ) : 1;

            if ( $intPage < 1 || $intPage > max( ceil( $intTotal/$this->perPage ), 1 ) ) {

                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate( $objPage->id );
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

                if ( !is_array( $arrImages[ ( $i + $j ) ] ) || ( $j + $i ) >= $intLimit ) {

                    $objCell->colWidth = $intColWidth . '%';
                    $objCell->class = 'col_'. $j . $strRowTDClass;
                }

                else {

                    $arrImages[($i+$j)]['size'] = $this->size;
                    $arrImages[($i+$j)]['fullsize'] = $this->fullsize;
                    $this->addImageToTemplate( $objCell, $arrImages[($i+$j)], $intMaxWidth, $strLightBoxID );

                    $objCell->colWidth = $intColWidth . '%';
                    $objCell->class = 'col_'. $j . $strRowTDClass;
                }

                $arrBody[$strKey][$j] = $objCell;
            }

            ++$intRowCount;
        }

        $this->setDataContainer();

        $objTemplate = new \FrontendTemplate( $this->galleryTpl );
        $objTemplate->setData($this->arrData);
        $objTemplate->body = $arrBody;

        $objMainTemplate->images = $objTemplate->parse();

        return $objMainTemplate->parse();
    }


    protected function setDataContainer(){

        $this->arrData = array(

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
        );
    }
}