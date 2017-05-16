<?php

namespace CatalogManager;

class ModuleMasterView extends \Module {


    protected $strMasterAlias;
    protected $strTemplate = 'mod_catalog_master';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->title = $this->headline;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            $objTemplate->wildcard = '### ' . utf8_strtoupper( $GLOBALS['TL_LANG']['FMD']['catalogMasterView'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        $this->strMasterAlias = \Input::get( 'auto_item' );

        return parent::generate();
    }


    protected function compile() {

        $this->import( 'CatalogView' );

        $arrQuery = [

            'where' => [

                [
                    [
                        'field' => 'id',
                        'operator' => 'equal',
                        'value' => $this->strMasterAlias
                    ],

                    [
                        'field' => 'alias',
                        'operator' => 'equal',
                        'value' => $this->strMasterAlias
                    ]
                ]
            ]
        ];

        $this->CatalogView->strMode = 'master';
        $this->CatalogView->arrOptions = $this->arrData;
        $this->CatalogView->objMainTemplate = $this->Template;
        $this->CatalogView->strTemplate = $this->catalogMasterTemplate ? $this->catalogMasterTemplate : 'catalog_master';
        $this->CatalogView->initialize();
        $this->CatalogView->getCommentForm();

        $blnHasPermission = $this->CatalogView->checkPermission();
        $strOutput = $blnHasPermission ? $this->CatalogView->getCatalogView( $arrQuery ) : '';

        $this->Template->data = is_array( $strOutput ) ? $strOutput : [];
        $this->Template->output = is_string( $strOutput ) ? $strOutput : '';

        if ( !$blnHasPermission ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate( $this->CatalogView->arrMasterPage['id'] );

            return null;
        }
        
        if ( empty( $strOutput ) ) {

            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate( $this->CatalogView->arrMasterPage['id'] );

            return null;
        }
    }
}