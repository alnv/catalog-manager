<?php

namespace Alnv\CatalogManagerBundle\Security;

use Contao\Environment;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class Authorization
{

    private string $strUrl = "https://shop.catalog-manager.org";

    private string $strMethod = 'key-license/getLicense';

    public function isValid($strLicense): bool
    {

        if (!$strLicense) {
            return false;
        }

        $objClient = new Client();
        $objRequest = new Request('GET', $this->strUrl . '/' . $this->strMethod . '/' . $strLicense, [], '');
        $objResponse = $objClient->send($objRequest);
        $arrOrder = \json_decode($objResponse->getBody()->getContents(), true);

        if ($arrOrder['status'] === 403) { // BC
            return true;
        }

        if ($arrOrder['status'] !== 200) {
            return false;
        }

        $strUrl = Environment::get('uri');
        $arrItems = $arrOrder['data']['order']['items'] ?? [];

        foreach ($arrItems as $arrItem) {

            if ($arrItem['product_id'] != '1') {
                continue;
            }

            $arrAttributes = \array_values($arrItem['attributes'] ?? []);
            foreach ($arrAttributes as $arrData) {
                if (is_string($arrData)) {
                    $arrData = [$arrData];
                }
                foreach ($arrData as $strDomain) {
                    if ($this->parseDomain($strUrl) === $this->parseDomain($strDomain)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function parseDomain(string $strDomain): string
    {

        $arrFragments = \parse_url($strDomain) ?? [];
        $strHost = $arrFragments['host'] ?? '';
        $strPort = $arrFragments['port'] ?? '';

        if (!$strHost) {
            return $strDomain;
        }

        $strHost = \str_replace('www.', '', $strHost);

        return \trim($strHost) . ($strPort ? ':' . $strPort : '');
    }

    public function getUrl(): string
    {
        return $this->strUrl;
    }
}