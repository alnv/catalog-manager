<?php

namespace Alnv\CatalogManagerBundle\Widgets;

use Contao\Widget;

class CatalogMessageWidget extends Widget
{

    protected $strTemplate = 'be_widget';

    public function generate()
    {
        return $this->ctlgMessage;
    }
}