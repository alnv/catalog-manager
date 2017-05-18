<?php

namespace CatalogManager;

class CatalogNotification extends CatalogController {

    protected $strID = null;
    protected $arrCatalog = [];
    protected $blnEnable = false;
    protected $strTablename = null;
    protected $arrCatalogFields = [];


    public function __construct( $strTablename = null, $strID = null ) {

        parent::__construct();

        $this->import( 'SQLQueryHelper' );
        $this->import( 'DCABuilderHelper' );

        $this->blnEnable = ( class_exists( 'NotificationCenter\Model\Notification' ) && $this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( 'tl_nc_notification' ) );
        $this->strID = $strID;
        $this->strTablename = $strTablename;

        if ( $this->blnEnable && $strTablename ) {

            $this->arrCatalogFields = $this->DCABuilderHelper->getPredefinedFields();
            $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename( $this->strTablename );
            $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename( $this->strTablename, $this->arrCatalogFields );
        }
    }


    public function notifyOnDelete( $intNotificationId, $arrData = [] ) {
        
        if ( !$this->blnEnable ) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk( $intNotificationId );

        if ( $objNotification === null ) {

            $this->log( 'The notification was not found ID ' . $intNotificationId , __METHOD__, TL_ERROR );
            return;
        }

        $arrTokens = $this->setDataTokens( $arrData );
        $arrTokens = $this->getOldData( $arrTokens );
        $arrTokens[ 'domain' ] = $this->getDomain();
        $arrTokens[ 'admin_email' ] = $this->getAdminEmail();

        $objNotification->send( $arrTokens, $GLOBALS['TL_LANGUAGE'] );
    }


    public function notifyOnUpdate( $intNotificationId, $arrData = [] ) {

        if ( !$this->blnEnable ) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk( $intNotificationId );

        if ( $objNotification === null ) {

            $this->log( 'The notification was not found ID ' . $intNotificationId , __METHOD__, TL_ERROR );
            return;
        }

        $arrTokens = $this->setDataTokens( $arrData );
        $arrTokens = $this->getOldData( $arrTokens );
        $arrTokens[ 'domain' ] = $this->getDomain();
        $arrTokens[ 'admin_email' ] = $this->getAdminEmail();

        $objNotification->send( $arrTokens, $GLOBALS['TL_LANGUAGE'] );
    }


    public function notifyOnInsert( $intNotificationId, $arrData = [] ) {

        if ( !$this->blnEnable ) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk( $intNotificationId );

        if ( $objNotification === null ) {

            $this->log( 'The notification was not found ID ' . $intNotificationId , __METHOD__, TL_ERROR );
            return;
        }

        $arrTokens = $this->setDataTokens( $arrData );
        $arrTokens[ 'domain' ] = $this->getDomain();
        $arrTokens[ 'admin_email' ] = $this->getAdminEmail();
        
        $objNotification->send( $arrTokens, $GLOBALS['TL_LANGUAGE'] );
    }


    protected function getOldData( $arrTokens = [] ) {

        if ( $this->strID && $this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $this->strTablename ) ) {

            $objData =  $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $this->strTablename ) )->limit( 1 )->execute( $this->strID );

            if ( $objData->numRows ) {

                $arrData = $objData->row();

                if ( !empty( $arrData ) && is_array( $arrData ) ) {

                    foreach ( $arrData as $strKey => $strValue ) {

                        $arrTokens[ 'rawOld_' . $strKey ] = $strValue;

                        if ( is_array ( $this->arrCatalogFields[ $strKey ] ) ) {

                            $varValue = $this->parseCatalogValues( $strValue, $this->arrCatalogFields[ $strKey ], $arrData );

                            if ( is_array( $varValue ) ) $varValue = implode( ',', $varValue );

                            $arrTokens[ 'cleanOld_' . $strKey ] = $varValue;
                        }
                    }
                }
            }
        }

        return $arrTokens;
    }


    protected function setDataTokens( $arrData ) {

        $arrTokens = [];

        if ( !empty( $arrData ) && is_array( $arrData ) ) {

            foreach ( $arrData as $strKey => $strValue ) {

                if ( is_array ( $this->arrCatalogFields[ $strKey ] ) ) {

                    $varValue = $this->parseCatalogValues( $strValue, $this->arrCatalogFields[ $strKey ], $arrData );

                    if ( is_array( $varValue ) ) $varValue = implode( ',', $varValue );

                    $arrTokens[ 'clean_' . $strKey ] = $varValue;

                    foreach ( $this->arrCatalogFields[ $strKey ] as $strOptionName => $strOptionValue ) {

                        $arrTokens[ 'field_' . $strKey . '_' .  $strOptionName ] = $strOptionValue;
                    }
                }

                $arrTokens[ 'raw_' . $strKey ] = $strValue;
            }
        }

        if ( !empty( $this->arrCatalog ) && is_array( $this->arrCatalog ) ) {

            foreach ( $this->arrCatalog as $strOptionName => $strOptionValue ) {

                $arrTokens[ 'table_' .  $strOptionName ] = $strOptionValue;
            }
        }

        return $arrTokens;
    }


    protected function getAdminEmail() {

        return $GLOBALS['TL_ADMIN_EMAIL'];
    }


    protected function getDomain() {

        return \Idna::decode( \Environment::get('host') );
    }

    
    protected function parseCatalogValues( $varValue, $arrField, &$arrCatalog ) {

        $strFieldname = $arrField['fieldname'];

        switch ( $arrField['type'] ) {

            case 'upload':

                if ( is_null( $varValue ) ) return '';

                $varValue = Upload::parseValue( $varValue, $arrField, $arrCatalog );

                if ( is_array( $varValue ) ) {

                    if ( $varValue['preview'] ) {

                        $arrCatalog[ $strFieldname . 'Preview' ] = $varValue['preview'];
                    }

                    return $varValue['gallery'];
                }

                return $varValue;

                break;

            case 'select':

                return Select::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'checkbox':

                return Checkbox::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'radio':

                return Radio::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'date':

                return DateInput::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'number':

                return Number::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'textarea':

                return Textarea::parseValue( $varValue, $arrField, $arrCatalog );

                break;

            case 'dbColumn':

                return DbColumn::parseValue( $varValue, $arrField, $arrCatalog );

                break;
        }

        return $varValue;
    }
}