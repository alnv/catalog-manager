<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Input;
use Contao\Config;
use Contao\Pagination;

class TemplateHelper extends CatalogController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function addComments($objTemplate, $arrConfig, $strTablename, $strID, $arrNotifies = [])
    {

        $this->import('Comments');

        $objCommentConfig = new \stdClass();
        if (!empty($arrConfig) && is_array($arrConfig)) {
            foreach ($arrConfig as $strKey => $varValue) {
                $objCommentConfig->{$strKey} = $varValue;
            }
        }

        $this->Comments->addCommentsToTemplate($objTemplate, $objCommentConfig, $strTablename, $strID, $arrNotifies);
    }

    public function addPagination($intTotal, $intPerPage, $strPageID, $pageID)
    {

        $strPage = (Input::get($strPageID) !== null) ? Input::get($strPageID) : 1;
        if ($strPage < 1 || $strPage > max(ceil($intTotal / $intPerPage), 1)) {
            $objCatalogException = new CatalogException();
            $objCatalogException->set404();
        }

        $objPagination = new Pagination($intTotal, $intPerPage, Config::get('maxPaginationLinks'), $strPageID);

        return $objPagination->generate("\n  ");
    }
}