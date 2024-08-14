<?php

namespace Alnv\CatalogManagerBundle\classes;

use Alnv\CatalogManagerBundle\Toolkit;
use Contao\Backend;
use Contao\DataContainer;
use Contao\System;

class tl_page extends Backend
{

    public function removeRouting()
    {

    }

    public function getCatalogTables()
    {

        $arrReturn = [];
        $objCatalogs = $this->Database->prepare('SELECT * FROM tl_catalog')->execute();

        if (!$objCatalogs->numRows) return $arrReturn;

        while ($objCatalogs->next()) {

            $arrReturn[$objCatalogs->tablename] = $objCatalogs->name ? $objCatalogs->name . ' [' . $objCatalogs->tablename . ']' : $objCatalogs->tablename;
        }

        return $arrReturn;
    }

    public function getRoutingFields(DataContainer $dc)
    {

        if (!$dc->activeRecord->catalogRoutingTable) return [];

        $arrReturn = [];
        $objCatalogFields = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? )')->execute($dc->activeRecord->catalogRoutingTable);

        while ($objCatalogFields->next()) {
            if (!$objCatalogFields->fieldname) continue;
            if (!in_array($objCatalogFields->type, ['select', 'radio', 'checkbox', 'text', 'dbColumn', 'number'])) continue;
            $arrReturn[$objCatalogFields->fieldname] = $objCatalogFields->title ? $objCatalogFields->title . ' <span style="color:#333; font-size:12px; display:inline">[' . $objCatalogFields->fieldname . ']</span>' : $objCatalogFields->fieldname;
        }

        System::loadLanguageFile('catalog_manager');
        if (!in_array('alias', $arrReturn)) {
            $arrReturn['alias'] = $GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0];
        }

        return $arrReturn;
    }


    public function setRoutingParameter(DataContainer $dc)
    {

        if ($dc->id && $dc->activeRecord->catalogUseRouting) {

            $arrRoutingSchema = [];
            $arrCatalogRoutingParameter = Toolkit::deserialize($dc->activeRecord->catalogRoutingParameter);

            if (!empty($arrCatalogRoutingParameter) && is_array($arrCatalogRoutingParameter)) {

                foreach ($arrCatalogRoutingParameter as $arrParameter) {

                    if ($arrParameter) {

                        $arrRoutingSchema[] = '{' . $arrParameter . '}';
                    }
                }
            }

            if ($dc->activeRecord->catalogSetAutoItem) {

                $arrRoutingSchema[] = '{auto_item}';
            }

            if (!empty($arrRoutingSchema) && is_array($arrRoutingSchema)) {

                $strRoutingFragments = implode('/', $arrRoutingSchema);
                $this->Database->prepare('UPDATE tl_page SET catalogRouting = ? WHERE id = ?')->execute($strRoutingFragments, $dc->id);
            }
        }
    }
}