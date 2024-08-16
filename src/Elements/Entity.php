<?php

namespace Alnv\CatalogManagerBundle\Elements;

use Alnv\CatalogManagerBundle\CatalogController;
use Alnv\CatalogManagerBundle\SQLQueryBuilder;
use Alnv\CatalogManagerBundle\CatalogFieldBuilder;
use Alnv\CatalogManagerBundle\Toolkit;
use Alnv\CatalogManagerBundle\CatalogException;
use Contao\ArrayUtil;
use Contao\Database;
use Contao\StringUtil;
use Contao\System;
use Contao\Environment;
use Contao\Controller;
use Contao\FrontendTemplate;
use Contao\ContentModel;
use Contao\Date;

class Entity extends CatalogController
{

    protected $catalogTablename = null;

    protected $catalogEntityId = null;

    protected array $arrSettings = [];

    protected array $arrCatalog = [];

    protected array $arrFields = [];


    public function __construct($strId, $strTable, $arrSettings = [])
    {

        $this->catalogEntityId = $strId;
        $this->arrSettings = $arrSettings;
        $this->catalogTablename = $strTable;

        $this->import(Database::class, 'Database');
        $this->import(SQLQueryBuilder::class, 'SQLQueryBuilder');

        System::loadLanguageFile('catalog_manager');

        parent::__construct();
    }


    public function getEntity()
    {

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize($this->catalogTablename);

        $this->arrCatalog = $objFieldBuilder->getCatalog();
        $arrFields = $objFieldBuilder->getCatalogFields();

        foreach ($arrFields as $strFieldname => $strValue) {
            if (!is_numeric($strFieldname)) {
                $this->arrFields[$strFieldname] = $strValue;
            }
        }

        $arrQuery = [
            'table' => $this->catalogTablename,
            'where' => [
                [
                    'field' => 'id',
                    'operator' => 'equal',
                    'value' => $this->catalogEntityId
                ]
            ],
            'joins' => [],
            'pagination' => [

                'limit' => 1,
                'offset' => 0
            ]
        ];

        if (isset($this->arrSettings['queries']) && is_array($this->arrSettings['queries']) && !empty($this->arrSettings['queries'])) {
            ArrayUtil::arrayInsert($arrQuery['where'], 0, $this->arrSettings['queries']);
        }

        if (isset($this->arrCatalog['operations']) && is_array($this->arrCatalog['operations']) && in_array('invisible', $this->arrCatalog['operations']) && (!isset($this->arrSettings['ignoreVisibility']) || !$this->arrSettings['ignoreVisibility'])) {
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

        $arrJoinedTables = [];
        foreach ($this->arrFields as $strFieldname => $arrField) {

            if (($this->arrSettings['noJoins'] ?? '')) {
                continue;
            }

            if (in_array($arrField['type'], ['select', 'checkbox', 'radio'])) {
                if (isset($arrField['optionsType']) && in_array($arrField['optionsType'], ['useDbOptions', 'useForeignKey'])) {
                    $arrField['multiple'] = $arrField['multiple'] ?? '';

                    if (!$arrField['multiple'] && !in_array($arrField['dbTable'], $arrJoinedTables)) {
                        $arrQuery['joins'][] = [
                            'multiple' => false,
                            'type' => 'LEFT JOIN',
                            'field' => $strFieldname,
                            'table' => $this->catalogTablename,
                            'onTable' => $arrField['dbTable'],
                            'onField' => $arrField['dbTableKey']
                        ];

                        $arrJoinedTables[] = $arrField['dbTable'];
                        $objChildFieldBuilder = new CatalogFieldBuilder();
                        $objChildFieldBuilder->initialize($arrField['dbTable']);

                        $this->mergeFields($objChildFieldBuilder->getCatalogFields(true, null), $arrField['dbTable']);
                    }
                }
            }
        }

        if (($this->arrCatalog['pTable'] ?? '') && !($this->arrSettings['noParentJoin'] ?? '')) {
            $arrQuery['joins'][] = [
                'field' => 'pid',
                'onField' => 'id',
                'multiple' => false,
                'table' => $this->catalogTablename,
                'onTable' => $this->arrCatalog['pTable']
            ];

            $objParentFieldBuilder = new CatalogFieldBuilder();
            $objParentFieldBuilder->initialize($this->arrCatalog['pTable']);

            $this->mergeFields($objParentFieldBuilder->getCatalogFields(true, null), $this->arrCatalog['pTable']);
        }

        $objEntity = $this->SQLQueryBuilder->execute($arrQuery);
        if (!$objEntity->numRows) {
            return [];
        }

        $arrEntity = $objEntity->row();
        $arrEntity['origin'] = $arrEntity;

        foreach ($arrEntity as $strFieldname => $strValue) {
            if (isset($this->arrFields[$strFieldname])) {
                $arrField = $this->arrFields[$strFieldname];
                if (isset($arrField['multiple']) && $arrField['multiple'] && in_array($arrField['optionsType'], ['useDbOptions', 'useForeignKey'])) {
                    $arrEntity[$strFieldname] = $this->getJoinedEntities($strValue, $arrField);
                    continue;
                }
                $arrEntity[$strFieldname] = Toolkit::parseCatalogValue($strValue, $arrField, $arrEntity);
            }
        }

        if (isset($this->arrCatalog['cTables']) && is_array($this->arrCatalog['cTables']) && !empty($this->arrCatalog['cTables'])) {
            foreach ($this->arrCatalog['cTables'] as $strChildTable) {
                $arrEntity[$strChildTable] = $this->getChildrenEntities($arrEntity['id'], $strChildTable);
            }
        }

        if (isset($this->arrCatalog['addContentElements']) && $this->arrCatalog['addContentElements']) {
            $arrEntity['contentElements'] = '';
            $objContent = ContentModel::findPublishedByPidAndTable($arrEntity['id'], $this->catalogTablename);
            if ($objContent !== null) {
                while ($objContent->next()) {
                    $arrEntity['contentElements'] .= $this->getContentElement($objContent->current());
                }
            }
        }

        return $arrEntity;
    }

    public function getPdf($strModuleId = '', $strTemplate = 'ctlg_pdf_default')
    {

        $arrEntity = $this->getEntity();
        $arrFields = $this->getTemplateFields();

        if (empty($arrEntity)) {
            $objCatalogException = new CatalogException();
            $objCatalogException->set404();
        }

        $strName = $arrEntity['alias'] . '.pdf';
        $objTemplate = new FrontendTemplate($strTemplate);

        $objTemplate->setData([
            'data' => $arrEntity,
            'fields' => $arrFields,
            'table' => $this->catalogTablename,
            'name' => $this->arrCatalog['name'],
            'fieldLabel' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['field'] ?? '',
            'valueLabel' => $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['value'] ?? ''
        ]);

        $objModule = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ?')->limit(1)->execute($strModuleId);
        $strOrientation = $objModule->catalogPdfOrientation ?: 'P';
        $strDocument = $objTemplate->parse();

        $objPDF = new \TCPDF($strOrientation, 'pt', 'A4', true, 'UTF-8', false);
        $objPDF->SetTitle($arrEntity['title'] ?: $arrEntity['alias']);
        $objPDF->SetPrintHeader(false);
        $objPDF->SetPrintFooter(false);
        $objPDF->SetFont('helvetica', '', 10);

        $objPDF->AddPage();
        $objPDF->lastPage();

        $strDom = <<<EOD
<!DOCTYPE html><html><head></head><body>$strDocument</body></html>
EOD;
        $objPDF->writeHTML($strDom, true, 0, true, 0);
        $objPDF->Output($strName, 'D'); // I

        $strQuery = 'pdf' . $strModuleId . '=' . $arrEntity['id'];
        $strRedirect = StringUtil::ampersand(Environment::get('indexFreeRequest'));
        $strRedirect = preg_replace('/[?&]' . $strQuery . '/gm', '', $strRedirect);
        Controller::redirect($strRedirect);
    }


    protected function mergeFields($arrFields, $strTablename)
    {

        foreach ($arrFields as $strFieldname => $arrField) {

            if (is_numeric($strFieldname)) {

                continue;
            }

            $this->arrFields[$strTablename . ucfirst($strFieldname)] = $arrField;
        }
    }


    public function getTemplateFields(): array
    {

        $arrReturn = [];

        foreach ($this->arrFields as $strFieldname => $arrField) {

            $strLabel = $strFieldname;

            if (is_array($arrField['_dcFormat']) && isset($arrField['_dcFormat']['label'])) {

                $strLabel = $arrField['_dcFormat']['label'][0];
            }

            $arrReturn[$strFieldname] = $strLabel;
        }

        if (is_array($this->arrCatalog['cTables']) && !empty($this->arrCatalog['cTables'])) {

            foreach ($this->arrCatalog['cTables'] as $strTable) {
                $objFieldBuilder = new CatalogFieldBuilder();
                $objFieldBuilder->initialize($strTable);
                $arrCatalog = $objFieldBuilder->getCatalog();
                $arrReturn[$strTable] = $arrCatalog['name'];
            }
        }

        return $arrReturn;
    }


    protected function getJoinedEntities($strValue, $arrField)
    {

        $arrReturn = [];
        if (Toolkit::isCoreTable($arrField['dbTable']) && !in_array($arrField['dbTable'], $GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES'])) {
            return $arrReturn;
        }

        if (!$arrField['dbTableKey']) {
            return $arrReturn;
        }

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize($arrField['dbTable']);
        $arrFields = $objFieldBuilder->getCatalogFields(true, null);
        $arrOrderBy = Toolkit::parseStringToArray($arrField['dbOrderBy']);
        $arrCatalog = $objFieldBuilder->getCatalog();

        $arrQuery = [
            'table' => $arrField['dbTable'],
            'where' => [
                [
                    'operator' => 'findInSet',
                    'field' => $arrField['dbTableKey'],
                    'value' => explode(',', $strValue)
                ]
            ],
            'orderBy' => []
        ];

        if (in_array('invisible', $arrCatalog['operations'])) {
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

        if (is_array($arrOrderBy) && !empty($arrOrderBy)) {

            foreach ($arrOrderBy as $arrOrder) {
                $arrQuery['orderBy'][] = [
                    'field' => $arrOrder['key'],
                    'order' => $arrOrder['value']
                ];
            }
        }

        $objEntities = $this->SQLQueryBuilder->execute($arrQuery);

        if (!$objEntities->numRows) return $arrReturn;

        while ($objEntities->next()) {
            $arrReturn[] = Toolkit::parseCatalogValues($objEntities->row(), $arrFields);
        }

        return $arrReturn;
    }


    protected function getChildrenEntities($strValue, $strTable): array
    {

        $arrReturn = [];

        if (Toolkit::isCoreTable($strTable) && !in_array($strTable, $GLOBALS['TL_CATALOG_MANAGER']['CORE_TABLES'])) {
            return $arrReturn;
        }

        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize($strTable);
        $arrFields = $objFieldBuilder->getCatalogFields(true, null);
        $arrCatalog = $objFieldBuilder->getCatalog();

        $arrQuery = [
            'table' => $strTable,
            'where' => [
                [
                    'field' => 'pid',
                    'operator' => 'equal',
                    'value' => $strValue
                ]
            ],
            'orderBy' => []
        ];

        if (in_array('invisible', $arrCatalog['operations'])) {

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

        if (!empty($arrCatalog['sortingFields'])) {

            $numFlag = (int)$arrCatalog['flag'] ?: 1;

            foreach ($arrCatalog['sortingFields'] as $strSortingField) {
                $arrQuery['orderBy'][] = [
                    'field' => $strSortingField,
                    'order' => ($numFlag % 2 == 0) ? 'DESC' : 'ASC'
                ];
            }
        }

        $objEntities = $this->SQLQueryBuilder->execute($arrQuery);

        if (!$objEntities->numRows) return $arrReturn;

        while ($objEntities->next()) {
            $arrReturn[] = Toolkit::parseCatalogValues($objEntities->row(), $arrFields);
        }

        return $arrReturn;
    }
}