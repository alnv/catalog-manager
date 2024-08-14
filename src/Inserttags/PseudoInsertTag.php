<?php

namespace Alnv\CatalogManagerBundle\Inserttags;

use Contao\Frontend;

class PseudoInsertTag extends Frontend
{

    public function getInsertTagValue($strTag)
    {

        $arrTags = explode('::', $strTag);

        if (empty($arrTags) || !is_array($arrTags)) {
            return false;
        }

        if (isset($arrTags[0])) {
            if (in_array($arrTags[0], ['pid', 'id'])) {
                return '';
            }
            $objCatalogField = $this->Database->prepare('SELECT fieldname FROM tl_catalog_fields WHERE `fieldname`=?')->limit(1)->execute($arrTags[0]);
            if ($objCatalogField->numRows) {
                return '';
            }
            $objFormField = $this->Database->prepare('SELECT `name` FROM tl_catalog_form_fields WHERE `name`=?')->limit(1)->execute($arrTags[0]);
            if ($objFormField->numRows) {
                return '';
            }
        }

        return false;
    }
}