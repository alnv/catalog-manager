<?php

namespace CatalogManager;

class CatalogEvents extends CatalogController {


    public function addEventListener( $strEvent, $arrData  ) {

        switch ( $strEvent ) {

            case 'create':

                $this->onCreate( $arrData );

                break;

            case 'update':

                $this->onUpdate( $arrData );

                break;

            case 'delete':

                $this->onDelete( $arrData );

                break;
        }
    }


    protected function onCreate( $arrData ) {

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerEntityOnCreate'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerEntityOnCreate'] ) ) {

            foreach ($GLOBALS['TL_HOOKS']['catalogManagerEntityOnCreate'] as $callback) {

                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}( $arrData );
            }
        }
    }


    protected function onUpdate( $arrData ) {

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerEntityOnUpdate'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerEntityOnUpdate'] ) ) {

            foreach ($GLOBALS['TL_HOOKS']['catalogManagerEntityOnUpdate'] as $callback) {

                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}( $arrData );
            }
        }
    }


    protected function onDelete( $arrData ) {

        if ( isset( $GLOBALS['TL_HOOKS']['catalogManagerEntityOnDelete'] ) && is_array( $GLOBALS['TL_HOOKS']['catalogManagerEntityOnDelete'] ) ) {

            foreach ($GLOBALS['TL_HOOKS']['catalogManagerEntityOnDelete'] as $callback) {

                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}( $arrData );
            }
        }
    }
}