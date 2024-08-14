<?php

namespace Alnv\CatalogManagerBundle;

use Contao\DataContainer;

class CatalogDataContainer extends DataContainer
{

    public function __construct($strTable)
    {

        parent::__construct();
        $this->table = $strTable;
    }

    public function __set($strKey, $varValue)
    {

        switch ($strKey) {
            case 'activeRecord':
                $objEntity = null;
                if (isset($varValue['id']) && $varValue['id']) {
                    $objEntity = $this->Database->prepare(sprintf('SELECT * FROM %s WHERE id = ?', $this->table))->limit(1)->execute($varValue['id']);
                }
                $this->objActiveRecord = $objEntity;
                break;
            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    public function getPalette()
    {
    }

    public function save($varValue)
    {
    }
}