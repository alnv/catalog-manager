<?php

namespace CatalogManager;

class tl_content extends \Backend {


    public function __construct() {

        parent::__construct();

        $this->import( 'BackendUser', 'User' );
    }


    public function getCatalogForms() {

        $arrForms = [];

        if ( !$this->User->isAdmin && !is_array( $this->User->filterform ) ) {

            return $arrForms;
        }

        $objForms = $this->Database->execute("SELECT id, title FROM tl_catalog_form ORDER BY title");

        while ( $objForms->next() ) {

            if ( $this->User->hasAccess( $objForms->id, 'filterform' ) ) {

                $arrForms[ $objForms->id ] = $objForms->title . ' (ID ' . $objForms->id . ')';
            }
        }

        return $arrForms;
    }


    public function editCatalogForm( \DataContainer $dc ) {

        return ( $dc->value < 1 ) ? '' : ' <a href="contao/main.php?do=filterform&amp;table=tl_catalog_form_fields&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:top"') . '</a>';
    }
}