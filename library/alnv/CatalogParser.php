<?php

namespace CatalogManager;

class CatalogParser extends CatalogController {


    protected $arrFields = [];
    protected $arrCatalog = [];
    protected $blnActive = false;


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
    }


    protected function initialize( $strTable ) {

        $objFieldBuilder = new CatalogFieldBuilder();
        $this->blnActive = $objFieldBuilder->initialize( $strTable );

        if ( !$this->blnActive ) return null;

        $this->arrCatalog = $objFieldBuilder->getCatalog();
        $arrFields = $objFieldBuilder->getCatalogFields( true, null );

        if ( !is_array( $arrFields ) ) return null;

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !isset( $arrField['_core'] ) || !$arrField['_core'] ) $this->arrFields[ $strFieldname ] = $arrField;
        }
    }


    public function getAllEvents( $arrCalendarEvents, $arrCalendars, $intStart, $intEnd, $objEvents ) {

        $this->initialize('tl_calendar_events');

        if ( !is_array( $arrCalendarEvents ) || !$this->blnActive ) return $arrCalendarEvents;

        $arrReturn = [];

        foreach ( $arrCalendarEvents as $intArchive => $arrArchive ) {

            foreach ( $arrArchive as $arrEventIndex => $arrEvents ) {

                foreach ( $arrEvents as $arrEvent ) {

                    $arrReturn[ $intArchive ][ $arrEventIndex ][] = $this->parseCatalogValues( $arrEvent );
                }
            }
        }

        return $arrReturn;
    }


    protected function parseCatalogValues( $arrData ) {

        return Toolkit::parseCatalogValues( $arrData, $this->arrFields );
    }
}
