<?php

namespace CatalogManager;

class ChangeLanguageExtension extends \Frontend {


    protected $strTable = '';
    protected $arrEntity = [];
    protected $arrCatalog = [];
    protected $strLinkColumn = '';
    protected $strMasterAlias = '';


    public function translateUrlParameters( \Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event ) {

        global $objPage;

        $this->strMasterAlias = \Input::get('auto_item');
        $this->strTable = $objPage->catalogChangeLanguageTable;
        $strTargetRoot = $event->getNavigationItem()->getRootPage();
        $strLanguage = $strTargetRoot->rootLanguage ? $strTargetRoot->rootLanguage : $strTargetRoot->language;
        
        if ( !\Config::get('useAutoItem') || !$this->strMasterAlias ) return null;

        $this->getCatalog();

        if ( empty( $this->arrCatalog ) || !is_array( $this->arrCatalog ) || !$this->arrCatalog['useChangeLanguage'] ) {

            return null;
        }

        $this->strLinkColumn = $this->arrCatalog['linkEntityColumn'];

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

        if ( !$this->arrCatalog['languageEntityColumn'] || !$this->arrCatalog['pTable'] || !$this->strLinkColumn ) return null;

        $objCurrentEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias`=? OR `id`=?', $this->strTable ) )->limit(1)->execute( $this->strMasterAlias, (int)$this->strMasterAlias );

        if ( $objCurrentEntity->numRows ) {

            $strLinkValue = $objCurrentEntity->{$this->strLinkColumn};
            $this->arrEntity = $this->Database->prepare(

                sprintf(
                    'SELECT * FROM %s LEFT OUTER JOIN %s ON %s.`pid` = %s.`id` WHERE %s.`%s`=? AND %s.`%s`=?',
                    $this->arrCatalog['pTable'],
                    $this->strTable,
                    $this->strTable,
                    $this->arrCatalog['pTable'],
                    $this->arrCatalog['pTable'],
                    $this->arrCatalog['languageEntityColumn'],
                    $this->strTable,
                    $this->strLinkColumn
                )

            )->limit(1)->execute( $strLanguage, $strLinkValue )->row();
        }
    }


    protected function getEntityByCurrentTable( $strLanguage ) {

        if ( !$this->arrCatalog['languageEntityColumn'] || !$this->strLinkColumn ) return null;

        $objCurrentEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `alias`=?  OR `id`=?', $this->strTable ) )->limit(1)->execute( $this->strMasterAlias, (int)$this->strMasterAlias );

        if ( $objCurrentEntity->numRows ) {

            $strLinkValue = $objCurrentEntity->{$this->strLinkColumn};
            $this->arrEntity = $this->Database->prepare(

                sprintf(
                    'SELECT * FROM %s WHERE `%s`=? AND `%s`=?',
                    $this->strTable,
                    $this->strLinkColumn,
                    $this->arrCatalog['languageEntityColumn']
                )

            )->limit(1)->execute( $strLinkValue, $strLanguage )->row();
        }
    }
}