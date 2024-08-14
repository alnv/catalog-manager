<?php

namespace Alnv\CatalogManagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AlnvCatalogManagerBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}