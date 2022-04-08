<?php

namespace CatalogManager;

class ChangeLanguageExtension extends \Frontend {

    protected $strTable = '';
    protected $arrEntity = [];
    protected $arrCatalog = [];
    protected $strLinkColumn = '';
    protected $strMasterAlias = '';

    public function translateUrlParameters(\Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event) {

        global $objPage;

        if (isset($_GET['auto_item']) && $_GET['auto_item']) {
            $this->strMasterAlias = \Input::cleanKey($_GET['auto_item']);
        }

        $this->strTable = $objPage->catalogChangeLanguageTable;
        $objTargetRoot = $event->getNavigationItem()->getRootPage();
        $strLanguage = $objTargetRoot->rootLanguage ? $objTargetRoot->rootLanguage : $objTargetRoot->language;
        
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

            $arrData = [];
            $arrParameters = [];
            $objTargetPage = $event->getNavigationItem()->getTargetPage();

            if ( $objTargetPage->catalogUseRouting ) {

                $arrData = $this->arrEntity;
                $arrParameters = Toolkit::getRoutingParameter( $objTargetPage->catalogRouting );

                foreach ( $arrParameters as $strParameter ) {

                    $event->getUrlParameterBag()->removeUrlAttribute( $strParameter );
                }
            }

            $event->getUrlParameterBag()->setUrlAttribute( 'items', Toolkit::generateAliasWithRouting( $this->arrEntity['alias'], $arrParameters, $arrData ) );
        }
    }


    protected function getCatalog() {

        $this->arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE tablename = ?')->limit(1)->execute( $this->strTable )->row();
    }


    protected function getEntityByPTable($strLanguage) {

        if (!$this->arrCatalog['languageEntityColumn'] || !$this->arrCatalog['pTable'] || !$this->strLinkColumn) return null;

        $objCurrentEntity = $this->Database->prepare(sprintf( 'SELECT * FROM %s WHERE `alias`=? OR `id`=?', $this->strTable))->execute( $this->strMasterAlias, (int)$this->strMasterAlias );

        if (!$objCurrentEntity->numRows) {
            return null;
        }

        if ($objCurrentEntity->numRows) {
            $objParent = $this->Database->prepare('SELECT * FROM ' . $this->arrCatalog['pTable'] . ' WHERE '.$this->arrCatalog['languageEntityColumn'].'=?')->limit(1)->execute($strLanguage);
            if (!$objParent->numRows) {
                return null;
            }
            if ($objParent->{$this->arrCatalog['languageEntityColumn']} != $strLanguage) {
                return;
            }
            $strLinkValue = $objCurrentEntity->{$this->strLinkColumn};
            $this->arrEntity = $this->Database->prepare(
                sprintf(
                    'SELECT * FROM %s WHERE `%s`=? AND `%s`=?',
                    $this->strTable,
                    $this->strLinkColumn,
                    'pid'
                )
            )->limit(1)->execute($strLinkValue, $objParent->id)->row();
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


    protected function generateUrl( $strAlias, $objEvent ) {

        $arrCatalog = [];
        $arrParameters = [];
        $objPage = $objEvent->getNavigationItem()->getTargetPage();

        if ( $objPage->catalogRoutingTable ) {

            $strTable = $objPage->catalogRoutingTable;
            $arrParameters = Toolkit::getRoutingParameter( $objPage->catalogRouting );
            // @todo get catalog and language
            $objCatalog = $this->Database->prepare( 'SELECT * FROM ' . $strTable . ' WHERE alias = ?' )->limit(1)->execute( $strAlias );

            if ( $objCatalog->numRows ) {

                $arrCatalog = $objCatalog->row();
            }

            foreach ( $arrParameters as $strParameter ) {

                $objEvent->getUrlParameterBag()->removeUrlAttribute( $strParameter );
            }
        }

        return Toolkit::generateAliasWithRouting( $strAlias, $arrParameters, $arrCatalog );
    }
}