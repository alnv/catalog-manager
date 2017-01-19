<?php

namespace CatalogManager;

class DCACallbacks extends \Backend{

    public function createRowView( $arrRow, $strHref, $strLabel, $strTitle, $strIcon, $strAttributes ) {

        // @todo hook
        return sprintf( '%s', $arrRow['title'] );
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
}