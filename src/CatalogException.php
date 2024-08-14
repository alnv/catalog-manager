<?php

namespace Alnv\CatalogManagerBundle;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Environment;

class CatalogException
{

    public function set404()
    {
        throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
    }

    public function set403()
    {
        throw new AccessDeniedException('Page access denied:  ' . Environment::get('uri'));
    }
}