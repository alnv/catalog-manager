<?php

namespace CatalogManager;

class DCAPermission extends CatalogController {

    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
        $this->import( 'BackendUser', 'User' );
    }

    public function checkPermission( $strTable, $strFieldname, $strFieldPermissions ) {

        $strID = \Input::get( 'id' );
        $strAct = \Input::get( 'act' );

        if ( $this->isAdmin() ) {

            return null;
        }

        $arrRoot = $this->checkAccessAndGetRoot( $strTable, $strFieldname, $strFieldPermissions );

        // @todo add hook

        switch ( $strAct ) {

            case 'paste':
            case 'create':
            case 'select':

                // Allow

                break;

            case 'edit':

                //

                break;

            case 'cut':
            case 'copy':
            case 'show':
            case 'delete':
            case 'toggle':

                //

                break;

            case 'cutAll':
            case 'copyAll':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':

                //

                break;

            default:

                if ( strlen( \Input::get('act') ) ) {

                    $this->log( sprintf( 'Invalid command "%s"', $strAct ), __METHOD__, TL_ERROR);
                    $this->redirect( 'contao/main.php?act=error' );
                }

                break;
        }
    }

    public function checkPermissionByParent( $strTable, $strFieldname, $strFieldPermissions ) {

        if ( $this->isAdmin() ) {

            return null;
        }

        $arrRoot = $this->getRoot( $strFieldname );

        //
    }

    private function checkAccessAndGetRoot( $strTable, $strFieldname, $strFieldPermissions ) {

        $arrRoot = $this->getRoot( $strFieldname );

        $GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root'] = $arrRoot;

        if ( !$this->User->hasAccess( 'create', $strFieldPermissions ) ) {

            $GLOBALS['TL_DCA'][$strTable]['config']['closed'] = true;
        }

        if ( !$this->User->hasAccess( 'delete', $strFieldPermissions ) ) {

            unset( $GLOBALS['TL_DCA'][$strTable]['list']['operations']['copy'] );
            unset( $GLOBALS['TL_DCA'][$strTable]['list']['operations']['delete'] );
        }

        if ( !$this->User->hasAccess( 'edit', $strFieldPermissions ) ) {

            unset( $GLOBALS['TL_DCA'][$strTable]['list']['operations']['edit'] );
        }

        return $arrRoot;
    }

    private function getRoot( $strFieldname ) {

        if ( !is_array( $this->User->{$strFieldname} ) || empty( $this->User->{$strFieldname} ) ) {

            $arrRoot = [0];
        }

        else {

            $arrRoot = $this->User->{$strFieldname};
        }

        return $arrRoot;
    }

    private function isAdmin() {

        return $this->User->isAdmin;
    }
}