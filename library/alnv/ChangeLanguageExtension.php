<?php

namespace CatalogManager;

class ChangeLanguageExtension extends \Frontend {


    protected $strTable = 'ovag_wasserversorgung'; // @todo
    protected $arrEntity = [];
    protected $arrCatalog = [];
    protected $strMasterAlias = '';
    protected $strFallbackColumn = '';


    public function translateUrlParameters( \Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event ) {

        global $objPage;

        $this->strMasterAlias = \Input::get('auto_item');
        $strTargetRoot = $event->getNavigationItem()->getRootPage();
        $strLanguage = $strTargetRoot->rootLanguage ? $strTargetRoot->rootLanguage : $strTargetRoot->language;

        if ( !\Config::get('useAutoItem') ) return null;

        $this->getCatalog();

        if ( empty( $this->arrCatalog ) || !is_array( $this->arrCatalog ) || !$this->arrCatalog['useChangeLanguage'] ) {

            return null;
        }

        $this->strFallbackColumn = $this->arrCatalog['fallbackEntityColumn'];

        switch ( $this->arrCatalog['languageEntitySource'] ) {

            case 'parentTable':

                $this->getEntityByPTable( $strLanguage );

                break;

            case 'currentTable':

                $this->getEntityByCurrentTable( $strLanguage );

                break;
        }

        if ( !empty( $this->arrEntity ) && is_array( $this->arrEntity ) ) {

            $event->getUrlParameterBag()->setUrlAttribute( 'items', $this->arrEntity['alias'] );
        }
    }


    protected function getCatalog() {

        $this->arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE tablename = ?')->limit(1)->execute( $this->strTable )->row();
    }


    protected function getEntityByPTable( $strLanguage ) {

        // @todo
    }


    protected function getEntityByCurrentTable( $strLanguage ) {

        if ( !$this->arrCatalog['currentLanguageColumn'] || !$this->strFallbackColumn ) return null;

        $objCurrentEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias` = ? OR `alias` = ?', $this->strTable ) )->limit(1)->execute( $this->strMasterAlias, (int)$this->strMasterAlias );

        if ( $objCurrentEntity->numRows ) {

            $strFallbackValue = $objCurrentEntity->{$this->strFallbackColumn};
            $this->arrEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `%s` = ? AND `%s` = ?', $this->strTable, $this->strFallbackColumn, $this->arrCatalog['currentLanguageColumn'] ) )->limit(1)->execute( $strFallbackValue, $strLanguage )->row();
        }
    }
}