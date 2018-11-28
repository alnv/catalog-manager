<?php

namespace CatalogManager;


class ContentCatalogEntity extends \ContentElement {


    protected $arrFields = [];
    protected $arrCatalog = [];
    protected $strTemplate = 'ce_catalog_entity';


    public function generate() {

        if ( TL_MODE == 'BE' ) {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . utf8_strtoupper( $GLOBALS['TL_LANG']['CTE']['catalogCatalogEntity'][0] ) . ' ###';

            return $objTemplate->parse();
        }

        $this->catalogEntityId = $this->catalogEntityId ?: '0';

        if ( !$this->catalogTablename || !$this->catalogEntityId ) {

            return '';
        }

        if ( !$this->Database->tableExists( $this->catalogTablename ) ) {

            return '';
        }

        if ( $this->catalogEntityTemplate ) {

            $this->strTemplate = $this->catalogEntityTemplate;
        }
        
        return parent::generate();
    }


    protected function compile() {

        $objEntity = new Entity( $this->catalogEntityId, $this->catalogTablename );
        $arrEntity = $objEntity->getEntity();

        foreach ( $arrEntity as $strFieldname => $strValue ) {

            $this->Template->{$strFieldname} = $strValue;
        }

        $strMasterUrl = '';

        if ( $this->catalogRedirectType ) {

            switch ( $this->catalogRedirectType ) {

                case 'internal':

                    $objPage = $this->getPage();

                    if ( $objPage !== null ) {

                        $strMasterUrl = $this->generateFrontendUrl( $objPage->row() );
                    }

                    $this->catalogRedirectTarget = '';

                    break;

                case 'master':

                    $objPage = $this->getPage();

                    if ( $objPage !== null ) {

                        $strMasterUrl = $this->generateFrontendUrl( $objPage->row(), ( $arrEntity['alias'] ? '/' . $arrEntity['alias'] : '' ) );
                    }

                    $this->catalogRedirectTarget = '';

                    break;

                case 'link':

                    $strMasterUrl = \Controller::replaceInsertTags( $this->catalogRedirectUrl );

                    break;
            }
        }

        $this->Template->masterUrl = $strMasterUrl;
        $this->Template->masterUrlText = $this->getLinkText();
        $this->Template->fields = $objEntity->getTemplateFields();
        $this->Template->masterUrlTarget = $this->catalogRedirectTarget;
        $this->Template->masterUrlTitle = \Controller::replaceInsertTags( $this->catalogRedirectTitle );
    }


    protected function getPage() {

        $objPage = null;

        if ( !$this->catalogRedirectPage ) {

            return null;
        }

        return \PageModel::findByPK( $this->catalogRedirectPage );
    }


    protected function getLinkText() {

        $strText = \Controller::replaceInsertTags( $this->catalogRedirectText );

        if ( $strText ) {

            return $strText;
        }

        return $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['detailLink'];
    }
}