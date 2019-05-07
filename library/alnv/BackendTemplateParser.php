<?php

namespace CatalogManager;


class BackendTemplateParser {


    public function outputBackendTemplate( $strBuffer, $strTemplate ) {

        if ( $strTemplate == 'be_main' && !isset( $_GET['act'] )  ) {

            foreach ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] as $arrCatalog ) {

                if ( $arrCatalog['isBackendModule'] && $arrCatalog['modulename'] == \Input::get('do') ) {

                    if ( in_array( $arrCatalog['mode'], [ '1', '2' ] ) && in_array( 'cut', $arrCatalog['operations'] ) && in_array( 'sorting', $arrCatalog['sortingFields'] ) && $arrCatalog['showColumns'] ) {

                        $objDoc = $this->getUtf8DomWithContent($strBuffer);
                        $objXPath = new \DOMXPath( $objDoc );

                        $objElement = $objXPath->query("descendant-or-self::li[contains(concat(' ', normalize-space(@class), ' '), ' tl_folder_top ')]");

                        if (!$objElement->length) {
                            return $strBuffer;
                        }

                        $objTable = $this->getUtf8DomWithContent($this->getTableHtml( $arrCatalog ));

                        $objParent = $objElement->item(0);
                        while ($objParent->childNodes->length > 0) {
                            $objParent->removeChild($objParent->childNodes->item(0));
                        }

                        $objNewNode = $objDoc->importNode( $objTable->getElementsByTagName('div')->item(0), true );
                        $objElement->item(0)->appendChild( $objNewNode );
                        $strBuffer = $objDoc->saveHTML();

                        $strBuffer = str_replace(
                            array('%5B', '%5D', '%7B', '%7D', '%20'),
                            array('[', ']', '{',   '}',   ' '),
                            $strBuffer
                        );
                        $strBuffer = preg_replace_callback(
                            '~\{%.*%\}~U',
                            function ($matches) {
                                return html_entity_decode($matches[0], ENT_QUOTES, 'UTF-8');
                            },
                            $strBuffer
                        );
                        $strBuffer = preg_replace_callback(
                            '~##.*##~U',
                            function ($matches) {
                                return html_entity_decode($matches[0], ENT_QUOTES, 'UTF-8');
                            },
                            $strBuffer
                        );
                    }
                }
            }
        }

        return $strBuffer;
    }


    protected function getTableHtml( $arrCatalog ) {

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $arrCatalog['tablename'] );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true );
        $strTemplate = '<div class="cm_table"><div class="cm_table_tr">';

        if ( is_array( $arrCatalog['labelFields'] ) && !empty( $arrCatalog['labelFields'] ) ) {

            $intIndex = 0;
            $intColumns = count( $arrCatalog['labelFields'] );
            $strClass = 'cols_' . $intColumns .' cm_table_th';
            $intWidth = 100 / $intColumns;

            foreach ( $arrCatalog['labelFields'] as $strField ) {
                $arrField = $arrFields[ $strField ];
                if ( !$arrField ) {
                    continue;
                }
                $strTemplate .= '<div class="'. $strClass .' '. $strField .'" style="width: '.$intWidth.'%">'. ( $arrField['_dcFormat']['label'][0] ?: $arrField['label'] ) .'</div>';
                $intIndex++;
            }
        }

        $strTemplate .= '</div></div>';

        return $strTemplate;
    }


    protected function getUtf8DomWithContent($content) {

        $doc = new \DOMDocument();

        $doc->encoding = 'UTF-8';
        $content = '<?xml encoding="UTF-8" ?>' . $content;
        @$doc->loadHTML($content);

        foreach ($doc->childNodes as $item) {
            if ($item->nodeType == XML_PI_NODE) {
                $doc->removeChild($item);
            }
        }

        return $doc;
    }
}