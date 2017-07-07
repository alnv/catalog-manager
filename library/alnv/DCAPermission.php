<?php

namespace CatalogManager;

class DCAPermission extends CatalogController {


    public function __construct() {

        parent::__construct();

        $this->import( 'Database' );
        $this->import( 'BackendUser', 'User' );
    }


    public function checkPermission( $strTable, $strFieldname, $strFieldPermissions, $strType = 'default' ) {

        $strID = \Input::get( 'id' );
        $strAct = \Input::get( 'act' );

        if ( $this->isAdmin() ) {

            return null;
        }

        $arrRoot = $this->checkAccessAndGetRoot( $strTable, $strFieldname, $strFieldPermissions, $strType );

        switch ( $strAct ) {

            case 'paste':
            case 'create':
            case 'select':

                // allow

                break;

            case 'edit':

                if ( !in_array( $strID, $arrRoot ) ) {

                    $arrNew = $this->Session->get( 'new_records' );

                    if ( is_array( $arrNew[ $strTable ] ) && in_array( $strID, $arrNew[ $strTable ] ) ) {

                        if ( $this->User->inherit != 'custom' ) {

                            $strUserGroups = implode( ',', array_map( 'intval', $this->User->groups ) );
                            $objGroup = $this->Database->execute( sprintf( "SELECT id, %s, %s FROM tl_user_group WHERE id IN( %s )", $strFieldname, $strFieldPermissions, $strUserGroups ) );

                            while ( $objGroup->next() ) {

                                $arrPermissions = deserialize( $objGroup->{$strFieldPermissions} );

                                if ( is_array($arrPermissions) && in_array( 'create', $arrPermissions ) ) {

                                    $arrFields = deserialize( $objGroup->{$strFieldname}, true );

                                    $arrFields[] = $strID;

                                    $this->Database->prepare( sprintf( "UPDATE tl_user_group SET %s = ? WHERE id = ?", $strFieldname ) )->execute( serialize( $arrFields ), $objGroup->id );
                                }
                            }
                        }

                        if ( $this->User->inherit != 'group' ) {

                            $objUser = $this->Database->prepare( sprintf( "SELECT %s, %s FROM tl_user WHERE id = ?", $strFieldname, $strFieldPermissions ) )->limit( 1 )->execute( $this->User->id );
                            $arrPermissions = deserialize ($objUser->{$strFieldPermissions} );

                            if ( is_array( $arrPermissions ) && in_array( 'create', $arrPermissions ) ) {

                                $arrFields = deserialize( $objUser->{$strFieldname}, true );

                                $arrFields[] = $strID;

                                $this->Database->prepare( sprintf( "UPDATE tl_user SET %s = ? WHERE id = ?", $strFieldname ) )->execute( serialize( $arrFields ), $this->User->id );
                            }
                        }

                        $root[] = $strID;

                        $this->User->{$strFieldname} = $root;
                    }
                }

                break;

            case 'cut':
            case 'copy':
            case 'show':
            case 'delete':
            case 'toggle':

                if ( !in_array( $strID, $arrRoot ) || ( $strAct == 'delete' && !$this->User->hasAccess( 'delete', $strFieldPermissions ) ) ) {

                    $this->log( sprintf( 'Not enough permissions to %s entity ID "%s"', $strAct, $strID ), __METHOD__, TL_ERROR );
                    $this->redirect('contao/main.php?act=error');
                }

                break;

            case 'cutAll':
            case 'copyAll':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':

                $arrSession = $this->Session->getData();

                if ( $strAct == 'deleteAll' && !$this->User->hasAccess( 'delete', $strFieldPermissions ) ) {

                    $arrSession['CURRENT']['IDS'] = [];
                }

                else {

                    $arrSession['CURRENT']['IDS'] = array_intersect( $arrSession['CURRENT']['IDS'], $arrRoot );
                }

                $this->Session->setData( $arrSession );

                break;

            default:

                if ( strlen( \Input::get('act') ) ) {

                    $this->log( sprintf( 'Invalid command "%s"', $strAct ), __METHOD__, TL_ERROR);
                    $this->redirect( 'contao/main.php?act=error' );
                }

                break;
        }
    }


    private function getRoot( $strTable, $strFieldname, $strType ) {

        if ( $strType == 'default' ) {

            return $this->getAllRoots( $strTable );
        }

        else {

            return $this->getRootFromUser( $strFieldname );
        }
    }


    private function checkAccessAndGetRoot( $strTable, $strFieldname, $strFieldPermissions, $strType ) {

        $arrRoot = $this->getRoot( $strTable, $strFieldname, $strType );
        $GLOBALS['TL_DCA'][$strTable]['list']['sorting']['root'] = $arrRoot;

        if ( !$this->User->hasAccess( 'create', $strFieldPermissions ) ) {

            $GLOBALS['TL_DCA'][$strTable]['config']['closed'] = true;
            unset( $GLOBALS['TL_DCA'][$strTable]['list']['operations']['copy'] );
        }

        if ( !$this->User->hasAccess( 'delete', $strFieldPermissions ) ) {

            unset( $GLOBALS['TL_DCA'][$strTable]['list']['operations']['delete'] );
        }

        if ( !$this->User->hasAccess( 'edit', $strFieldPermissions ) ) {

            unset( $GLOBALS['TL_DCA'][$strTable]['list']['operations']['edit'] );
        }

        return $arrRoot;
    }


    private function getRootFromUser( $strFieldname ) {

        if ( is_array( $this->User->{$strFieldname} ) && !empty( $this->User->{$strFieldname} ) ) {

            return $this->User->{$strFieldname};
        }

        return [0];
    }


    private function getAllRoots( $strTable ) {

        $arrRoot = [];
        $objRootIds = $this->Database->prepare( sprintf( 'SELECT id FROM %s', $strTable ) )->execute();

        if ( !$objRootIds->numRows ) {

            return [0];
        }

        while ( $objRootIds->next() ) {

            $arrRoot[] = $objRootIds->id;
        }

        return $arrRoot;
    }


    private function isAdmin() {

        return $this->User->isAdmin;
    }
}