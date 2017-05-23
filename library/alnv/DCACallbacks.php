<?php

namespace CatalogManager;

class DCACallbacks extends \Backend {

    public function __construct() {

        parent::__construct();
    }


    public function createRowView( $arrRow ) {

        // @todo hook
        return sprintf( '%s', $arrRow['title'] );
    }


    public function pagePicker( \DataContainer $dc ) {
        
        return ' <a href="' . ( ($dc->value == '' || strpos($dc->value, '{{link_url::') !== false) ? 'contao/page.php' : 'contao/file.php') . '?do=' . \Input::get('do') . '&amp;table=' . $dc->table . '&amp;field=' . $dc->field . '&amp;value=' . rawurlencode(str_replace(array('{{link_url::', '}}'), '', $dc->value)) . '&amp;switch=1' . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']) . '" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['label'][0])) . '\',\'url\':this.href,\'id\':\'' . $dc->field . '\',\'tag\':\'ctrl_'. $dc->field . (( \Input::get('act') == 'editAll') ? '_' . $dc->id : '') . '\',\'self\':this});return false">' . \Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="cursor:pointer"') . '</a>';
    }

    
    public function setMultiSrcFlags( $varValue, \DataContainer $dc ) {

        if ( $dc->table && $dc->field ) {

            $objField = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? LIMIT 1) AND fieldname = ?')->limit(1)->execute( $dc->table, $dc->field );

            switch ( $objField->fileType ) {

                case 'gallery':

                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ]['eval']['isGallery'] = true;
                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ]['eval']['extensions'] = \Config::get('validImageTypes');

                    break;

                case 'files':

                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ]['eval']['isDownloads'] = true;
                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ]['eval']['extensions'] = \Config::get('allowedDownload');

                    break;
            }
        }

        return $varValue;
    }


    public function toggleIcon( $arrRow, $strHref, $strLabel, $strTitle, $strIcon, $strAttributes ) {

        parse_str( $strHref, $arrHrefAttributes );

        $arrOptions = [

            'icon' => 'invisible.gif',
            'fieldname' => 'invisible'
        ];

        $strTable = \Input::get('catalogTable') ? \Input::get('catalogTable') : $arrHrefAttributes['catalogTable'];
        $strCustomFieldname = \Input::get('fieldname') ? \Input::get('fieldname') : $arrHrefAttributes['fieldname'];
        $strIconInVisible = \Input::get('iconVisible') ? \Input::get('iconVisible') : $arrHrefAttributes['iconVisible'];

        if ( $strIconInVisible ) $arrOptions['icon'] = $strIconInVisible;
        if ( $strCustomFieldname ) $arrOptions['fieldname'] = $strCustomFieldname;

        if ( strlen( \Input::get('tid') ) ) {

            $this->toggleVisibility( \Input::get('tid'), ( \Input::get('state') == 1 ), $strTable, $arrOptions, ( @func_get_arg( 12 ) ?: null ) );
            $this->redirect( $this->getReferer() );
        }

        $strHref .= '&amp;tid='. $arrRow['id'] .'&amp;state='. $arrRow[ $arrOptions['fieldname'] ];

        if ( $arrRow[ $arrOptions['fieldname'] ] ) {

            $strIcon = $arrOptions['icon'];
        }

        return '<a href="' . $this->addToUrl( $strHref ) . '" title="' . specialchars( $strTitle ) . '"' . $strAttributes . '>' . \Image::getHtml( $strIcon, $strLabel, 'data-state="' . ( $arrRow[ $arrOptions['fieldname'] ] ? 0 : 1 ) . '"' ) . '</a> ';
    }


    public function toggleVisibility( $intId, $blnVisible, $strTable, $arrOptions, \DataContainer $dc = null ) {

        \Input::setGet( 'id', $intId );
        \Input::setGet( 'act', 'toggle' );

        if ( $dc ) $dc->id = $intId;

        if ( is_array( $GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'] ) ) {

            foreach ( $GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'] as $callback ) {

                if ( is_array( $callback ) ) {

                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}(($dc ?: $this));

                } elseif ( is_callable( $callback ) ) {

                    $callback( ( $dc ?: $this ) );
                }
            }
        }

        $strTstamp = time();

        $arrData = [

            'id' => $intId,
            'table' => $strTable,
            'row' => [

                'tstamp' => $strTstamp
            ]
        ];

        $arrData['row'][ $arrOptions[ 'fieldname' ] ] = ( $blnVisible ? '' : 1 );

        $objEventListener = new CatalogEvents();
        $objEventListener->addEventListener( 'update', $arrData );

        $this->Database->prepare( sprintf( "UPDATE %s SET `tstamp` = %s, `%s` = ? WHERE `id` = ?", $strTable, $strTstamp, $arrOptions[ 'fieldname' ] ) )->execute( ( $blnVisible ? '' : 1 ), $intId );
    }


    public function generateAlias( $varValue, \DataContainer $dc, $strField = 'title', $strTable = '' ) {

        $blnAutoAlias = false;
        $strTable = \Input::get( 'table' ) ? \Input::get( 'table' ) : $strTable;

        if ( !$strTable ) {

            return $varValue . uniqid( '_' );
        }

        if ( !$varValue ) {

            $blnAutoAlias = true;
            $varValue = \StringUtil::generateAlias( $dc->activeRecord->{$strField} );
        }

        $objCatalogs = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias` = ? ', $strTable ) )->execute( $varValue );

        if ( $objCatalogs->numRows > 1 && !$blnAutoAlias ) {

            throw new \Exception( sprintf( $GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue ) );
        }

        if ( $objCatalogs->numRows && $blnAutoAlias ) {

            $varValue .= '_' . $dc->activeRecord->id;
        }

        return $varValue;
    }


    public function generateFEAlias( $varValue, $strTitle, $strTablename, $strID ) {

        if ( !$varValue && $strTitle ) {

            $varValue = \StringUtil::generateAlias( $strTitle );
        }

        $objCatalogs = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias` = ? AND id != ?', $strTablename ) )->execute( $varValue, $strID );

        if ( $objCatalogs->numRows && \Input::get('id') ) {

            $varValue .= '_' . \Input::get('id');
        }

        if ( $objCatalogs->numRows && !\Input::get('id') ) {

            $varValue .= '_' . md5( time() . uniqid() );
        }

        if ( !$varValue ) {

            $varValue .= md5( $objCatalogs->numRows . time() . uniqid() );
        }

        return $varValue;
    }


    public function generateGeoCords( \DataContainer $dc ) {

        if ( !$dc->activeRecord ) return null;

        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ \Input::get('do') ];

        if ( !$arrCatalog ) return null;

        $arrCords = [];
        $objGeoCoding = new GeoCoding();
        $strGeoInputType = $arrCatalog['addressInputType'];

        switch ( $strGeoInputType ) {

            case 'useSingleField':

                $arrCords = $objGeoCoding->getCords( $dc->activeRecord->{$arrCatalog['geoAddress']}, 'en', true );

                break;

            case 'useMultipleFields':

                $objGeoCoding->setCity( $dc->activeRecord->{$arrCatalog['geoCity']} );
                $objGeoCoding->setStreet( $dc->activeRecord->{$arrCatalog['geoStreet']} );
                $objGeoCoding->setPostal( $dc->activeRecord->{$arrCatalog['geoPostal']} );
                $objGeoCoding->setCountry( $dc->activeRecord->{$arrCatalog['geoCountry']} );
                $objGeoCoding->setStreetNumber( $dc->activeRecord->{$arrCatalog['geoStreetNumber']} );

                $arrCords = $objGeoCoding->getCords( '', 'en', true );

                break;
        }

        if ( ( $arrCords['lat'] || $arrCords['lng'] ) && ( $arrCatalog['lngField'] && $arrCatalog['latField'] ) ) {

            $arrSet = [];
            $arrSet[ $arrCatalog['lngField'] ] = $arrCords['lng'];
            $arrSet[ $arrCatalog['latField'] ] = $arrCords['lat'];

            $this->Database->prepare( 'UPDATE '. $dc->table .' %s WHERE id = ?' )->set($arrSet)->execute( $dc->id );
        }
    }


    public function generateRelationWizard( \DataContainer $dc ) {

        $strTable = \Input::get( 'table' ) ? \Input::get( 'table' ) : \Input::get( 'do' );

        if ( !$dc->value || !$strTable || !$dc->field ) return '';

        $objCatalogField = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE fieldname = ? AND pid = ( SELECT id FROM tl_catalog WHERE tablename = ? )' )->limit(1)->execute( $dc->field, $strTable );

        if ( !$objCatalogField->numRows ) return '';

        $strTableAttribute = '';
        $arrField = $objCatalogField->row();
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $arrField['dbTable'] ];
        $strTitle = $objCatalogField->description ? $objCatalogField->description : $objCatalogField->label;

        if ( $arrCatalog['pTable'] ) {

            $strTableAttribute = sprintf( '&amp;table=%s', $arrCatalog['tablename'] );
            $strDoAttribute = sprintf( 'do=%s', $arrCatalog['pTable'] );
        }

        else {

            $strDoAttribute = sprintf( 'do=%s', $arrCatalog['tablename'] );
        }
        
        return '<a href="contao/main.php?' . $strDoAttribute . $strTableAttribute . '&amp;act=edit&amp;id=' . $dc->value . '&amp;rt=' . REQUEST_TOKEN . '" title="' . ( $strTitle ? $strTitle : '' ) . '" style="padding-left:3px">' . \Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:middle"') . '</a>';
    }


    public function onSubmitCallback( \DataContainer $dc ) {

        if ( is_null( $dc->activeRecord ) ) return;

        $strRedirectUrl = \Environment::get('request');
        $strEvent = \Input::get( '_act' ) ? \Input::get( '_act' ) : '';

        $arrData = [

            'id' => $dc->id,
            'table' => $dc->table,
            'row' => method_exists( $dc->activeRecord, 'row' ) ? $dc->activeRecord->row() : [],
        ];

        $objEventListener = new CatalogEvents();
        $objEventListener->addEventListener( ( $strEvent ? $strEvent : 'create' ), $arrData );

        if ( !$strEvent ) {

            $this->redirect( $strRedirectUrl . '&_act=update' );
        }

        if ( $strEvent == 'create' ) {

            $strRedirectUrl = str_replace( '&_act=create', '', $strRedirectUrl );
            $this->redirect( $strRedirectUrl );
        }
    }


    public function onDeleteCallback( \DataContainer $dc, $strID ) {

        if ( is_null( $dc->activeRecord ) || !$strID ) return;

        $arrData = [

            'id' => $strID,
            'table' => $dc->table,
            'row' => method_exists( $dc->activeRecord, 'row' ) ? $dc->activeRecord->row() : [],
        ];

        $objEventListener = new CatalogEvents();
        $objEventListener->addEventListener( 'delete' , $arrData );
    }


    public function onCutCallback( \DataContainer $dc ) {

        $arrData = [

            'row' => [],
            'id' => $dc->id,
            'table' => $dc->table,
        ];

        $objEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $dc->table ) )->limit(1)->execute( $dc->id );

        if ( $objEntity->numRows ) {

            $arrData['row']['pid'] = $objEntity->pid;
        }

        $objEventListener = new CatalogEvents();
        $objEventListener->addEventListener( 'update' , $arrData );
    }
}