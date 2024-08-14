<?php

namespace Alnv\CatalogManagerBundle\classes;


use Alnv\CatalogManagerBundle\CatalogFieldBuilder;
use Alnv\CatalogManagerBundle\SQLQueryBuilder;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\Backend;
use Contao\Date;
use Contao\DataContainer;
use Contao\BackendUser;

class tl_content extends Backend
{

    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function getCatalogForms()
    {

        $arrForms = [];

        if (!$this->User->isAdmin && !is_array($this->User->filterform)) {
            return $arrForms;
        }

        $objForms = $this->Database->execute("SELECT id, title FROM tl_catalog_form ORDER BY title");

        while ($objForms->next()) {
            if ($this->User->hasAccess($objForms->id, 'filterform')) {
                $arrForms[$objForms->id] = $objForms->title . ' (ID ' . $objForms->id . ')';
            }
        }

        return $arrForms;
    }

    public function editCatalogForm(DataContainer $dc)
    {
        return ($dc->value < 1) ? '' : ' <a href="contao/main.php?do=filterform&amp;table=tl_catalog_form_fields&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="' . sprintf(specialchars($GLOBALS['TL_LANG']['tl_content']['editalias'][1]), $dc->value) . '" style="padding-left:3px" onclick="Backend.openModalIframe({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", sprintf($GLOBALS['TL_LANG']['tl_content']['editalias'][1], $dc->value))) . '\',\'url\':this.href});return false">' . \Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:top"') . '</a>';
    }

    public function getSocialSharingButtons()
    {
        return Toolkit::$arrSocialSharingButtons;
    }

    public function getSocialSharingTemplates()
    {
        return $this->getTemplateGroup('ce_social_sharing_buttons');
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


    public function getCatalogFields(DataContainer $dc)
    {

        $arrReturn = [];
        $strTable = $dc->activeRecord->catalogSocialSharingTable;

        if ($strTable) {

            $objFieldBuilder = new CatalogFieldBuilder();
            $objFieldBuilder->initialize($strTable);
            $arrFields = $objFieldBuilder->getCatalogFields(true, null, false, false);

            if (!empty($arrFields) && is_array($arrFields)) {

                foreach ($arrFields as $strFieldname => $arrField) {

                    if (in_array($arrField['type'], Toolkit::excludeFromDc())) continue;

                    $arrReturn[$strFieldname] = $arrField['_dcFormat']['label'][0] ?: $strFieldname;
                }
            }
        }

        return $arrReturn;
    }


    public function getFilterFormTemplates()
    {
        return $this->getTemplateGroup('ce_catalog_filterform');
    }


    public function getTablenames()
    {

        $arrReturn = [];
        $objCatalogs = $this->Database->prepare('SELECT * FROM tl_catalog WHERE tstamp > 0')->execute();

        if (!$objCatalogs->numRows) {

            return $arrReturn;
        }

        while ($objCatalogs->next()) {

            $arrReturn[$objCatalogs->tablename] = $objCatalogs->name;
        }

        return $arrReturn;
    }


    public function getCatalogEntities(DataContainer $dc)
    {

        $arrReturn = [];

        if (!$dc->activeRecord->catalogTablename) {

            return $arrReturn;
        }

        if (!$this->Database->tableExists($dc->activeRecord->catalogTablename)) {

            return $arrReturn;
        }

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize($dc->activeRecord->catalogTablename);

        $arrQuery = [

            'table' => $dc->activeRecord->catalogTablename,
            'where' => [],
            'orderBy' => [

                [
                    'field' => 'tstamp',
                    'order' => 'ASC'
                ]
            ],
        ];

        $arrCatalog = $objFieldBuilder->getCatalog();

        if (is_array($arrCatalog['operations']) && in_array('invisible', $arrCatalog['operations'])) {

            $dteTime = Date::floorToMinute();

            $arrQuery['where'][] = [

                'field' => 'tstamp',
                'operator' => 'gt',
                'value' => 0
            ];

            $arrQuery['where'][] = [

                [
                    'value' => '',
                    'field' => 'start',
                    'operator' => 'equal'
                ],

                [
                    'field' => 'start',
                    'operator' => 'lte',
                    'value' => $dteTime
                ]
            ];

            $arrQuery['where'][] = [

                [
                    'value' => '',
                    'field' => 'stop',
                    'operator' => 'equal'
                ],

                [
                    'field' => 'stop',
                    'operator' => 'gt',
                    'value' => $dteTime
                ]
            ];

            $arrQuery['where'][] = [

                'field' => 'invisible',
                'operator' => 'not',
                'value' => '1'
            ];
        }

        $this->import(SQLQueryBuilder::class);

        $objEntities = $this->SQLQueryBuilder->execute($arrQuery);

        if (!$objEntities->numRows) {
            return $arrReturn;
        }

        while ($objEntities->next()) {
            $arrReturn[$objEntities->id] = $objEntities->title;
        }

        return $arrReturn;
    }


    public function getEntityTemplates()
    {
        return $this->getTemplateGroup('ce_catalog_entity');
    }
}