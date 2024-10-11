<?php

namespace Alnv\CatalogManagerBundle;

class CatalogEvents extends CatalogController
{

    public function addEventListener($strEvent, $arrData, $objModule = null): void
    {

        switch ($strEvent) {
            case 'create':
                $this->onCreate($arrData, $objModule);
                break;
            case 'update':
                $this->onUpdate($arrData, $objModule);
                break;
            case 'delete':
                $this->onDelete($arrData, $objModule);
                break;
        }
    }

    protected function onCreate($arrData, $objModule): void
    {
        if (isset($GLOBALS['TL_HOOKS']['catalogManagerEntityOnCreate']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerEntityOnCreate'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerEntityOnCreate'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($arrData, $objModule);
            }
        }
    }

    protected function onUpdate($arrData, $objModule): void
    {
        if (isset($GLOBALS['TL_HOOKS']['catalogManagerEntityOnUpdate']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerEntityOnUpdate'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerEntityOnUpdate'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($arrData, $objModule);
            }
        }
    }

    protected function onDelete($arrData, $objModule): void
    {
        if (isset($GLOBALS['TL_HOOKS']['catalogManagerEntityOnDelete']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerEntityOnDelete'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerEntityOnDelete'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($arrData, $objModule);
            }
        }
    }
}