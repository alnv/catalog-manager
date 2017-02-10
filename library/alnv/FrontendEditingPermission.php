<?php

namespace CatalogManager;

class FrontendEditingPermission extends CatalogController {


    public $blnDisablePermissions = false;

    private $arrGroups = [];
    private $arrCatalogManagerPermissions = [];


    public function __construct() {

        parent::__construct();

        $this->import( 'SQLQueryHelper' );
        $this->import( 'FrontendUser', 'User' );
    }


    public function initialize() {

        if ( TL_MODE !== 'FE' ) return null;

        $this->arrGroups = Toolkit::deserialize( $this->User->allGroups );

        if ( empty( $this->arrGroups ) || !is_array( $this->arrGroups ) ) return null;

        $this->setAttributes();

        $this->setCatalogManagerPermissionFields();

        $this->setMemberGroup();
    }


    public function isAdmin() {

        return $this->isAdmin ? true : false;
    }


    public function hasPermission( $strMode, $strCatalogname ) {

        if ( $this->isAdmin() || $this->blnDisablePermissions ) {

            return true;
        }
        
        if ( !empty( $this->{$strCatalogname. 'p'} ) && is_array( $this->{$strCatalogname. 'p'} ) ) {
            
            return in_array( $strMode, $this->{$strCatalogname. 'p'} );
        }

        return false;
    }


    public function hasAccess( $strCatalogname ) {

        if ( $this->isAdmin() || $this->blnDisablePermissions ) {

            return true;
        }

        return $this->{$strCatalogname} ? true : false;
    }


    private function setCatalogManagerPermissionFields() {

        $objCatalogs = $this->SQLQueryHelper->getCatalogs();

        while ( $objCatalogs->next() ) {

            $this->arrCatalogManagerPermissions[] = $objCatalogs->tablename;
            $this->arrCatalogManagerPermissions[] = $objCatalogs->tablename . 'p';
        }
    }


    private function setAttributes() {

        $arrUser = $this->User->getData();

        if ( !empty( $arrUser ) && is_array( $arrUser ) ) {

            foreach ( $arrUser as $strKey => $varValue ) {

                $this->{$strKey} = $varValue;
            }
        }
    }


    private function setMemberGroup() {

        $intTime = \Date::floorToMinute();

        if ( !empty( $this->arrGroups ) && is_array( $this->arrGroups ) ) {

            foreach ( $this->arrGroups as $strID ) {

                $objGroup = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare(sprintf( "SELECT * FROM tl_member_group WHERE id = ? AND disable != '1' AND (start='' OR start<='%s') AND (stop='' OR stop>'" . ( $intTime  + 60) . "')", $intTime ) )
                    ->limit(1)
                    ->execute( $strID );

                if ( $objGroup->numRows > 0 ) {

                    if ( $objGroup->isAdmin ) {

                        $this->{'isAdmin'} = true;
                    }

                    foreach ( $this->arrCatalogManagerPermissions as $strField ) {

                        if (  $objGroup->{$strField} ) {

                            $this->{$strField} = deserialize( $objGroup->{$strField} );
                        }
                    }
                }
            }
        }
    }
}