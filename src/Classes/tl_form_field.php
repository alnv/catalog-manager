<?php

namespace Alnv\CatalogManagerBundle\Classes;

use Contao\Input;
use Contao\Backend;
use Contao\Message;
use Contao\DataContainer;

class tl_form_field extends Backend
{

    protected array $arrCatalogTypes = [
        'catalogMessageForm',
        'catalogFineUploader'
    ];

    public function setInfo(DataContainer $dc)
    {

        $strId = Input::get('id');
        if (!$strId) return null;
        $objField = $this->Database->prepare('SELECT * FROM tl_form_field WHERE id=?')->limit(1)->execute($strId);

        if ($objField->numRows && in_array($objField->type, $this->arrCatalogTypes)) {
            Message::addError('Currently you can not use catalog manager fields in form generator.');
        }
    }
}