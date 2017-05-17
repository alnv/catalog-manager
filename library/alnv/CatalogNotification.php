<?php

namespace CatalogManager;

class CatalogNotification extends CatalogController {


    protected $blnEnable = false;


    public function __construct() {

        parent::__construct();

        $this->blnEnable = class_exists( 'NotificationCenter\Model\Notification' );
    }


    public function notifyOnDelete( $intNotificationId, $arrTokens = [] ) {

        if ( !$this->blnEnable ) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk( $intNotificationId );

        if ( $objNotification === null ) {

            $this->log( 'The notification was not found ID ' . $intNotificationId , __METHOD__, TL_ERROR );
            return;
        }

        $objNotification->send( $arrTokens, $GLOBALS['TL_LANGUAGE'] );
    }


    public function notifyOnUpdate( $intNotificationId, $arrTokens = [] ) {

        if ( !$this->blnEnable ) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk( $intNotificationId );

        if ( $objNotification === null ) {

            $this->log( 'The notification was not found ID ' . $intNotificationId , __METHOD__, TL_ERROR );
            return;
        }

        $objNotification->send( $arrTokens, $GLOBALS['TL_LANGUAGE'] );
    }


    public function notifyOnInsert( $intNotificationId, $arrTokens = [] ) {

        if ( !$this->blnEnable ) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk( $intNotificationId );

        if ( $objNotification === null ) {

            $this->log( 'The notification was not found ID ' . $intNotificationId , __METHOD__, TL_ERROR );
            return;
        }

        $objNotification->send( $arrTokens, $GLOBALS['TL_LANGUAGE'] );
    }
}