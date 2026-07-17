<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Frontend;
use Contao\Input;

class ChangeLanguageExtension extends Frontend
{

    protected $strTable = '';
    protected $arrEntity = [];
    protected $arrCatalog = [];
    protected $strLinkColumn = '';
    protected $strMasterAlias = '';

    public function translateUrlParameters(\Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event)
    {
        global $objPage;

        $this->strMasterAlias = ($_GET['auto_item'] ?? '') ? Input::get('auto_item') : '';
        $this->strTable = $objPage->catalogChangeLanguageTable;
        $objTargetRoot = $event->getNavigationItem()->getRootPage();
        $strLanguage = $objTargetRoot->rootLanguage ?: $objTargetRoot->language;

        if (!$this->strMasterAlias) return null;

        $this->getCatalog();

        if (empty($this->arrCatalog) || !is_array($this->arrCatalog) || !$this->arrCatalog['useChangeLanguage']) {
            return null;
        }

        $this->strLinkColumn = $this->arrCatalog['linkEntityColumn'];

        switch ($this->arrCatalog['languageEntitySource']) {

            case 'parentTable':
                $this->getEntityByPTable($strLanguage);
                break;

            case 'currentTable':
                $this->getEntityByCurrentTable($strLanguage);
                break;
        }

        if (!empty($this->arrEntity) && is_array($this->arrEntity)) {
            $arrData = [];
            $arrParameters = [];
            $objTargetPage = $event->getNavigationItem()->getTargetPage();

            if ($objTargetPage->catalogUseRouting) {
                $arrData = $this->arrEntity;
                $arrParameters = Toolkit::getRoutingParameter($objTargetPage->catalogRouting);

                foreach ($arrParameters as $strParameter) {
                    $event->getUrlParameterBag()->removeUrlAttribute($strParameter);
                }
            }

            $event->getUrlParameterBag()->setUrlAttribute('auto_item', Toolkit::generateAliasWithRouting($this->arrEntity['alias'], $arrParameters, $arrData));
        }
    }

    protected function getCatalog()
    {
        $this->arrCatalog = $this->Database
            ->prepare('SELECT * FROM tl_catalog WHERE tablename = ?')
            ->limit(1)
            ->execute($this->strTable)
            ->row();
    }

    protected function getEntityByPTable($strLanguage)
    {
        if (!$this->arrCatalog['languageEntityColumn'] || !$this->arrCatalog['pTable'] || !$this->strLinkColumn) return null;

        $objCurrentEntity = $this->Database->prepare(sprintf('SELECT * FROM %s WHERE `alias`=? OR `id`=?', $this->strTable))->execute($this->strMasterAlias, (int)$this->strMasterAlias);

        if (!$objCurrentEntity->numRows) {
            return;
        }

        if ($objCurrentEntity->numRows) {
            $objParent = $this->Database->prepare('SELECT * FROM ' . $this->arrCatalog['pTable'] . ' WHERE ' . $this->arrCatalog['languageEntityColumn'] . '=?')->limit(1)->execute($strLanguage);
            if (!$objParent->numRows) {
                return;
            }
            if ($objParent->{$this->arrCatalog['languageEntityColumn']} != $strLanguage) {
                return;
            }
            $strLinkValue = $objCurrentEntity->{$this->strLinkColumn};
            $this->arrEntity = $this->Database
                ->prepare(
                    sprintf(
                        'SELECT * FROM %s WHERE `%s`=? AND `%s`=?',
                        $this->strTable,
                        $this->strLinkColumn,
                        'pid'
                    )
                )->limit(1)
                ->execute($strLinkValue, $objParent->id)
                ->row();
        }
    }

    protected function getEntityByCurrentTable($strLanguage)
    {
        if (!$this->arrCatalog['languageEntityColumn'] || !$this->strLinkColumn) return null;

        if (\is_numeric($this->strMasterAlias)) {
            $objCurrentEntity = $this->Database
                ->prepare(sprintf('SELECT * FROM %s WHERE `id`=?', $this->strTable))
                ->limit(1)
                ->execute((int)$this->strMasterAlias);
        } else {
            $objCurrentEntity = $this->Database
                ->prepare(sprintf('SELECT * FROM %s WHERE `alias`=?', $this->strTable))
                ->limit(1)
                ->execute($this->strMasterAlias);
        }

        if ($objCurrentEntity->numRows) {
            $strLinkValue = $objCurrentEntity->{$this->strLinkColumn};
            $this->arrEntity = $this->Database
                ->prepare(
                    sprintf(
                        'SELECT * FROM %s WHERE `%s`=? AND `%s`=?',
                        $this->strTable,
                        $this->strLinkColumn,
                        $this->arrCatalog['languageEntityColumn']
                    )
                )
                ->limit(1)
                ->execute($strLinkValue, $strLanguage)
                ->row();
        }
    }
}