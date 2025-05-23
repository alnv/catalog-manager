<?php

namespace Alnv\CatalogManagerBundle\EventListener;

use Alnv\CatalogManagerBundle\SearchIndexBuilder;
use Contao\CoreBundle\Event\SitemapEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class SitemapListener
{
    public function __invoke(SitemapEvent $objEvent): void
    {

        if (!\method_exists($objEvent, 'addUrlToDefaultUrlSet')) {
            return;
        }

        $arrPages = [];

        foreach ($objEvent->getRootPageIds() as $strRootId) {

            foreach ((new SearchIndexBuilder())->initialize([], $strRootId, true) as $strPage) {
                $arrPages[] = $strPage;
            }
        }

        $arrPages = \array_values(\array_unique($arrPages));

        foreach ($arrPages as $strPage) {
            $objEvent->addUrlToDefaultUrlSet($strPage);
        }
    }
}