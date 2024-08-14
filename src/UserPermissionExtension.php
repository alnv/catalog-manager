<?php

namespace Alnv\CatalogManagerBundle;

class UserPermissionExtension extends CatalogController
{

    public function __construct()
    {

        parent::__construct();

        $this->import(SQLBuilder::class);
        $this->import(I18nCatalogTranslator::class);
    }

    public function initialize($strDcName)
    {

        if ($strDcName == 'tl_user' || $strDcName == 'tl_user_group') {

            if (!empty($GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS']) && is_array($GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'])) {

                foreach ($GLOBALS['TL_CATALOG_MANAGER']['PROTECTED_CATALOGS'] as $arrCatalog) {

                    $this->extendUserAndUserGroupDCA($arrCatalog['tablename'], $strDcName, $arrCatalog['type']);
                }
            }
        }
    }

    protected function extendUserAndUserGroupDCA($strCatalogname, $strDcName, $strType)
    {

        $arrLabels = $this->I18nCatalogTranslator->get('module', $strCatalogname);
        if ($strDcName == 'tl_user') {
            $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('fop;', sprintf('fop;{%s},%s,%s;', $arrLabels[0], ($strType == 'extended' ? $strCatalogname : ''), $strCatalogname . 'p'), $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
            $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('fop;', sprintf('fop;{%s},%s,%s;', $arrLabels[0], ($strType == 'extended' ? $strCatalogname : ''), $strCatalogname . 'p'), $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);
        } else {
            $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('fop;', sprintf('fop;{%s},%s,%s;', $arrLabels[0], ($strType == 'extended' ? $strCatalogname : ''), $strCatalogname . 'p'), $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);
        }

        if ($strType == 'extended') {
            $GLOBALS['TL_DCA'][$strDcName]['fields'][$strCatalogname] = [
                'label' => $arrLabels,
                'inputType' => 'checkbox',
                'foreignKey' => sprintf('%s.title', $strCatalogname),
                'eval' => [
                    'multiple' => true
                ],
                'exclude' => true,
                'sql' => "blob NULL"
            ];
        }

        $GLOBALS['TL_DCA'][$strDcName]['fields'][$strCatalogname . 'p'] = [
            'label' => $this->I18nCatalogTranslator->get('module', $strCatalogname, ['postfix' => $this->getPermissionLabel()]),
            'inputType' => 'checkbox',
            'options' => [
                'edit',
                'create',
                'delete'
            ],
            'eval' => [
                'multiple' => true
            ],
            'reference' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'],
            'exclude' => true,
            'sql' => "blob NULL"
        ];
    }


    protected function getPermissionLabel()
    {

        if (isset($GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']) && is_array($GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'])) {
            return $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['permission'];
        }

        return '';
    }
}