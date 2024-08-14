<?php

namespace Alnv\CatalogManagerBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Alnv\CatalogManagerBundle\AlnvCatalogManagerBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            (new BundleConfig(AlnvCatalogManagerBundle::class))
                ->setReplace(['catalog-manager'])
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}