<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Database;

class SQLCompileCommands extends CatalogController
{

    protected array $arrCatalogs = [];

    public function __construct()
    {

        parent::__construct();

        $this->import(Database::class);
    }

    public function initialize($arrSQLCommands)
    {

        if (!is_array($arrSQLCommands) || empty($arrSQLCommands)) return $arrSQLCommands;
        if (!$this->Database->tableExists('tl_catalog')) return $arrSQLCommands;

        $objCatalog = $this->Database->prepare('SELECT tl_catalog.tablename, tl_catalog.id FROM tl_catalog')->execute('');

        if (!$objCatalog->numRows) return $arrSQLCommands;

        $arrCmTables = [];
        while ($objCatalog->next()) {

            if (Toolkit::isEmpty($objCatalog->tablename)) continue;
            $this->arrCatalogs[$objCatalog->tablename] = [
                'table' => $objCatalog->tablename,
                'fields' => []
            ];

            if (!Toolkit::isCoreTable($objCatalog->tablename)) {
                $arrCmTables[] = $objCatalog->tablename;
            }

            $objFields = $this->Database->prepare('SELECT tl_catalog_fields.fieldname, tl_catalog_fields.id FROM tl_catalog_fields WHERE pid = ?')->execute($objCatalog->id);
            if (!$objFields->numRows) continue;
            while ($objFields->next()) {
                if (Toolkit::isEmpty($objFields->fieldname)) continue;
                $this->arrCatalogs[$objCatalog->tablename]['fields'][] = $objFields->fieldname;
            }
        }

        foreach ($arrSQLCommands as $strType => $arrSQLCommandGroup) {
            switch ($strType) {
                case 'ALTER_DROP':
                    $this->preventPermissionFieldsFromAlter($arrSQLCommands, $strType);
                    $this->preventModifiedFieldsFromAlter($arrSQLCommands, $strType);
                    break;
                case 'ALTER_CHANGE':
                    $this->preventModifiedFieldsFromAlter($arrSQLCommands, $strType);
                    break;
                case 'DROP':
                    foreach ($arrSQLCommandGroup as $strHex => $strSqlCommand) {
                        $strTable = str_replace('DROP TABLE', '', $strSqlCommand);
                        $strTable = str_replace(' ', '', $strTable);
                        if (in_array($strTable, $arrCmTables)) {
                            unset($arrSQLCommands[$strType][$strHex]);
                        }
                    }
                    if (empty($arrSQLCommands[$strType])) {
                        unset($arrSQLCommands[$strType]);
                    }
                    break;
            }
        }

        if (empty($arrSQLCommands['ALTER_DROP'])) unset($arrSQLCommands['ALTER_DROP']);
        if (empty($arrSQLCommands['ALTER_CHANGE'])) unset($arrSQLCommands['ALTER_CHANGE']);

        return $arrSQLCommands;
    }

    protected function preventPermissionFieldsFromAlter(&$arrSQLCommands, $strType)
    {

        foreach ($arrSQLCommands[$strType] as $strHash => $arrSQLCommand) {
            if (strpos($arrSQLCommand, 'tl_member') !== false || strpos($arrSQLCommand, 'tl_user') !== false || strpos($arrSQLCommand, 'tl_user_group') !== false) {
                foreach ($this->arrCatalogs as $strTable => $arrCatalog) {
                    if (strpos($arrSQLCommand, $strTable) !== false || strpos($arrSQLCommand, $strTable . 'p') !== false) {
                        if (isset($arrSQLCommands[$strType][$strHash])) unset($arrSQLCommands[$strType][$strHash]);
                    }
                }
            }
        }
    }

    protected function preventModifiedFieldsFromAlter(&$arrSQLCommands, $strType)
    {

        foreach ($arrSQLCommands[$strType] as $strHash => $arrSQLCommand) {
            foreach ($this->arrCatalogs as $strTable => $arrCatalog) {
                foreach ($arrCatalog['fields'] as $strField) {
                    if (strpos($arrSQLCommand, $strTable) !== false && strpos($arrSQLCommand, $strField) !== false) {
                        if (isset($arrSQLCommands[$strType][$strHash])) unset($arrSQLCommands[$strType][$strHash]);
                    }
                }
            }
        }
    }
}