<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Controller;
use Contao\Database;
use Contao\DcaLoader;

class ReviseRelatedTables extends Controller
{

    private array $arrErrorTables = [];

    public function __construct()
    {
        $this->import(Database::class, 'Database');
        parent::__construct();
    }

    public function reviseCatalogTables($strTable, $strPTable, $arrCTables): bool
    {

        $objCatalogDb = $this->Database->prepare('SELECT id FROM tl_catalog WHERE tablename = ?')->execute($strTable);
        if (!$objCatalogDb->count()) {
            return false;
        }

        if ($strPTable && $this->Database->TableExists($strPTable)) {
            if (isset($GLOBALS['TL_DCA'][$strTable]['config']['dynamicPtable']) && $GLOBALS['TL_DCA'][$strTable]['config']['dynamicPtable']) {
                $objStmt = $this->Database->prepare(sprintf(' SELECT * FROM %s WHERE ptable=? AND NOT EXISTS( SELECT * FROM %s WHERE %s.pid = %s.id )', $strTable, $strPTable, $strTable, $strPTable))->execute($strPTable);
            } else {
                $objStmt = $this->Database->prepare(sprintf('SELECT * FROM %s WHERE NOT EXISTS( SELECT * FROM %s WHERE %s.pid = %s.id )', $strTable, $strPTable, $strTable, $strPTable))->execute();
            }
            if ($objStmt->count() > 0) {
                $this->arrErrorTables[] = $strPTable;
                return true;
            }
        }

        if (!empty($arrCTables) && is_array($arrCTables)) {

            foreach ($arrCTables as $v) {

                if ($v && $this->Database->TableExists($v)) {

                    if (!isset($GLOBALS['TL_DCA'][$v])) {
                        $objLoader = new DcaLoader($strTable);
                        $objLoader->load();
                    }

                    if (!($GLOBALS['TL_DCA'][$v] ?? '')) {
                        continue;
                    }

                    if (isset($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable']) && $GLOBALS['TL_DCA'][$v]['config']['dynamicPtable']) {
                        $objStmt = $this->Database->prepare(sprintf(' SELECT * FROM %s  WHERE ptable=? AND NOT EXISTS( SELECT * FROM %s WHERE %s.pid = %s.id)', $v, $strTable, $v, $strTable))->execute($v);
                    } else {
                        $objStmt = $this->Database->prepare(sprintf('SELECT * FROM %s WHERE NOT EXISTS( SELECT * FROM %s WHERE %s.pid = %s.id)', $v, $strTable, $v, $strTable))->execute();
                    }

                    if ($objStmt->count() > 0) {
                        $this->arrErrorTables[] = $v;
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function getErrorTables(): array
    {
        return $this->arrErrorTables;
    }
}