<?php

namespace CatalogManager;

class DCACallbacks extends \Backend{

    public function __construct() {

        parent::__construct();
    }

    public function createRowView( $arrRow ) {

        // @todo hook
        return sprintf( '%s', $arrRow['title'] );
    }

    public function pagePicker( \DataContainer $dc ) {

        return ' <a href="' . ( ($dc->value == '' || strpos($dc->value, '{{link_url::') !== false) ? 'contao/page.php' : 'contao/file.php') . '?do=' . \Input::get('do') . '&amp;table=' . $dc->table . '&amp;field=' . $dc->field . '&amp;value=' . rawurlencode(str_replace(array('{{link_url::', '}}'), '', $dc->value)) . '&amp;switch=1' . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']) . '" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['label'][0])) . '\',\'url\':this.href,\'id\':\'' . $dc->field . '\',\'tag\':\'ctrl_'. $dc->field . (( \Input::get('act') == 'editAll') ? '_' . $dc->id : '') . '\',\'self\':this});return false">' . \Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
    }

    public function toggleIcon( $arrRow, $strHref, $strLabel, $strTitle, $strIcon, $strAttributes ) {

        parse_str( $strHref, $arrHrefAttributes );

        $strTable = \Input::get('table') ? \Input::get('table') : $arrHrefAttributes['table'];

        if ( strlen( \Input::get('tid') ) ) {

            $this->toggleVisibility( \Input::get('tid'), ( \Input::get('state') == 1 ), $strTable, ( @func_get_arg( 12 ) ?: null ) );
            $this->redirect( $this->getReferer() );
        }

        $strHref .= '&amp;tid='. $arrRow['id'] .'&amp;state='. $arrRow['invisible'];

        if ( $arrRow['invisible'] ) {

            $strIcon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl( $strHref ) . '" title="' . specialchars( $strTitle ) . '"' . $strAttributes . '>' . \Image::getHtml( $strIcon, $strLabel, 'data-state="' . ( $arrRow['invisible'] ? 0 : 1) . '"' ) . '</a> ';
    }

    public function toggleVisibility( $intId, $blnVisible, $strTable, \DataContainer $dc = null ) {

        \Input::setGet( 'id', $intId );
        \Input::setGet( 'act', 'toggle' );

        if ( $dc ) {

            $dc->id = $intId;
        }

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

        $this->Database->prepare( sprintf( "UPDATE %s SET `tstamp` = %s, `invisible` = ? WHERE `id` = ?", $strTable, time() ) )->execute( ( $blnVisible ? '' : 1 ), $intId );
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
}