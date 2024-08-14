<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Database;
use Contao\Input;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class CatalogDcAdapter extends CatalogController
{

    public function __construct()
    {
        parent::__construct();
    }


    public function initialize($strTablename): void
    {
        if ($this->shouldLoadDc($strTablename)) {
            $objDcExtractor = new CatalogDcExtractor();
            $objDcExtractor->initialize($strTablename);
            $GLOBALS['TL_DCA'][$strTablename] = $objDcExtractor->convertCatalogToDataContainer();
        }
    }


    protected function shouldLoadDc($strTablename): bool
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));
        $blnIsCoreTable = in_array($strTablename, $GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES']);

        if (!$blnIsBackend && !$blnIsCoreTable) {

            $objDatabase = Database::getInstance();
            $objCatalog = $objDatabase->prepare('SELECT id FROM tl_catalog WHERE tablename = ?')->limit(1)->execute($strTablename);

            return (bool)$objCatalog->numRows;
        }

        return $blnIsCoreTable && (Input::get('do') != 'catalog-manager' || Input::get('table') == 'tl_catalog_fields');
    }
}