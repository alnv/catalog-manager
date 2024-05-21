<?php

namespace CatalogManager;

class CatalogDcExtractor extends CatalogController
{


    protected $strTable = '';
    protected $blnCore = false;
    protected $strOrderBy = '';


    public function __construct()
    {

        parent::__construct();

        $this->import('Database');
        $this->import('DcModifier');
        $this->import('IconGetter');
    }


    public function initialize($strTablename)
    {

        $this->strTable = $strTablename;
        $this->blnCore = Toolkit::isCoreTable($strTablename);
    }


    public function convertDataContainerToCatalog()
    {

        \Controller::loadLanguageFile($this->strTable);
        \Controller::loadDataContainer($this->strTable);

        $arrReturn = [];
        $arrDataContainer = $GLOBALS['TL_DCA'][$this->strTable];

        if (!is_array($arrDataContainer)) return [];

        $arrReturn = $this->convertDcLabelToCatalog($arrReturn, $arrDataContainer, 'list');
        $arrReturn = $this->convertDcSortingToCatalog($arrReturn, $arrDataContainer, 'list');
        $arrReturn = $this->convertDcConfigToCatalog($arrReturn, $arrDataContainer, 'config');
        $arrReturn = $this->convertDcOperationsToCatalog($arrReturn, $arrDataContainer, 'list');

        if ($this->blnCore) {

            $arrReturn['navArea'] = '';
            $arrReturn['navPosition'] = '';
            $arrReturn['isBackendModule'] = '';
            $arrReturn['tablename'] = $this->strTable;
        }

        return $arrReturn;
    }

    public function convertCatalogToDataContainer()
    {

        $this->DcModifier->initialize($this->strTable);

        $arrReturn = $GLOBALS['TL_DCA'][$this->strTable];
        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$this->strTable];

        if (!is_array($arrCatalog)) return [];
        if (!is_array($arrReturn)) $arrReturn = [];

        $arrReturn = $this->convertCatalogToDcConfig($arrReturn, $arrCatalog, 'config');
        $arrReturn = $this->convertCatalogToDcSorting($arrReturn, $arrCatalog, 'list');
        $arrReturn = $this->convertCatalogToDcLabel($arrReturn, $arrCatalog, 'list');
        $arrReturn = $this->convertCatalogToDcOperations($arrReturn, $arrCatalog, 'list');

        return $this->convertCatalogToDcFieldsAndPalettes($arrReturn, $arrCatalog, 'fields');
    }

    public function extract()
    {

        $objModule = $this->Database->prepare('SELECT * FROM tl_catalog WHERE tablename = ? LIMIT 1')->execute($this->strTable);

        if ($objModule->numRows) {

            $arrSorting = [

                'mode' => (int)$objModule->mode,
                'flag' => $objModule->flag,
                'fields' => Toolkit::deserialize($objModule->sortingFields)
            ];

            $this->extractDCASorting($arrSorting);

            return null;
        }

        $this->loadDataContainer($this->strTable);

        if ($GLOBALS['TL_DCA'][$this->strTable]['config']['dataContainer'] == 'File') {

            return null;
        }

        if (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']) && is_array($GLOBALS['TL_DCA'][$this->strTable]['list'])) {

            if (!empty($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting']) && is_array($GLOBALS['TL_DCA'][$this->strTable]['list']['sorting'])) {

                $arrSorting = $GLOBALS['TL_DCA'][$this->strTable]['list']['sorting'];

                if (!Toolkit::isEmpty($arrSorting['mode']) && in_array($arrSorting['mode'], [5, 6]) && empty($arrSorting['fields'])) {

                    $arrSorting['fields'] = ['sorting'];
                }

                $this->extractDCASorting($arrSorting);
            }
        }
    }


    public function getOrderByStatement()
    {

        return $this->strOrderBy;
    }


    public function hasOrderByStatement()
    {

        return !Toolkit::isEmpty($this->strOrderBy);
    }


    public function setDcSortingByMode($strMode, $arrCatalog = [], $arrDefaults = [])
    {

        $strMode = (int)$strMode;

        $arrReturn = [
            'mode' => $strMode ?: 0
        ];

        $arrCatalog['panelLayout'] = $arrCatalog['panelLayout'] ?? '';
        $arrCatalog['flag'] = $arrCatalog['flag'] ?? '';
        $arrCatalog['sortingFields'] = $arrCatalog['sortingFields'] ?? [];
        $arrCatalog['headerFields'] = $arrCatalog['headerFields'] ?? '';
        // $arrCatalog['labelFields'] = $arrCatalog['labelFields'] ?? [];

        switch ($strMode) {
            case 1:
                if (!Toolkit::isEmpty($arrCatalog['flag'])) {
                    $arrReturn['flag'] = !Toolkit::isEmpty($arrCatalog['flag']) ? $arrCatalog['flag'] : $arrDefaults['flag'];
                }
                if (is_array($arrCatalog['sortingFields']) && !empty($arrCatalog['sortingFields'])) {
                    $arrReturn['fields'] = $arrCatalog['sortingFields'];
                } else {
                    $arrReturn['fields'] = $arrDefaults['fields'];
                }
                return $arrReturn;
            case 2:
                $arrReturn['panelLayout'] = Toolkit::createPanelLayout($arrCatalog['panelLayout']);
                if (is_array($arrCatalog['sortingFields']) && !empty($arrCatalog['sortingFields'])) {
                    $arrReturn['fields'] = $arrCatalog['sortingFields'];
                } else {
                    $arrReturn['fields'] = $arrDefaults['fields'];
                }
                return $arrReturn;
            case 0:
            case 5:
            case 3:
                return $arrReturn;
            case 4:
                if (is_array($arrCatalog['sortingFields']) && !empty($arrCatalog['sortingFields'])) {
                    $arrReturn['fields'] = $arrCatalog['sortingFields'];
                } else {
                    $arrReturn['fields'] = $arrDefaults['fields'];
                }
                if (!Toolkit::isEmpty($arrCatalog['flag'])) {
                    $arrReturn['flag'] = $arrCatalog['flag'];
                }
                /*
                if (isset($arrCatalog['labelFields']) && is_array($arrCatalog['labelFields']) && !empty($arrCatalog['labelFields'])) {
                    $arrReturn['label'] = $arrCatalog['labelFields'];
                } else {
                    $arrReturn['label'] = $arrDefaults['labelFields'] ?? ['id'];
                }
                */
                if (is_array($arrCatalog['headerFields']) && !empty($arrCatalog['headerFields'])) {
                    $arrReturn['headerFields'] = $arrCatalog['headerFields'];
                } else {
                    $arrReturn['headerFields'] = $arrDefaults['headerFields'];
                }
                break;
            case 6:
                if (is_array($arrCatalog['sortingFields']) && !empty($arrCatalog['sortingFields'])) {
                    $arrReturn['fields'] = $arrCatalog['sortingFields'];
                } else {
                    $arrReturn['fields'] = $arrDefaults['fields'];
                }
                break;
        }

        return $arrReturn;
    }


    public function setDcLabelByMode($strMode, $arrCatalog = [], $arrDefaults = [])
    {

        $strMode = (int)$strMode;
        $arrReturn = [];

        if (in_array($strMode, [0, 1, 2, 3])) {

            if (is_array($arrCatalog['labelFields']) && !empty($arrCatalog['labelFields'])) {

                $arrReturn['fields'] = $arrCatalog['labelFields'];
            } else {

                $arrReturn['fields'] = $arrDefaults['fields'];
            }

            $arrReturn['showColumns'] = $arrCatalog['showColumns'] ? true : false;

            if (!Toolkit::isEmpty($arrCatalog['format'])) $arrReturn['format'] = $arrCatalog['format'];

            return $arrReturn;
        }

        if ($strMode === 4) {

            return $arrReturn;
        }

        if ($strMode === 5 || $strMode === 6) {

            if (is_array($arrCatalog['labelFields']) && !empty($arrCatalog['labelFields'])) {

                $arrReturn['fields'] = $arrCatalog['labelFields'];
            } else {

                $arrReturn['fields'] = $arrDefaults['fields'];
            }

            if (!Toolkit::isEmpty($arrCatalog['format'])) $arrReturn['format'] = $arrCatalog['format'];

            return $arrReturn;
        }

        return $arrReturn;
    }


    protected function extractDCASorting($arrSorting)
    {

        $arrTemps = [];
        $arrOrderBy = [];
        $intFlag = (isset($arrSorting['flag']) && $arrSorting['flag']) ? (int)$arrSorting['flag'] : 1;
        $arrFields = !empty($arrSorting['fields']) && is_array($arrSorting['fields']) ? $arrSorting['fields'] : [];
        $strOrder = $intFlag % 2 ? 'ASC' : 'DESC';

        foreach ($arrFields as $strField) {

            if (in_array($strField, $arrTemps)) {
                continue;
            } else {
                $arrTemps[] = $strField;
            }

            $strUpperCaseField = strtoupper($strField);

            if (stripos($strUpperCaseField, 'ASC') || stripos($strUpperCaseField, 'DESC')) {
                $arrOrderBy[] = $strField;
                continue;
            }

            if ($this->Database->fieldExists($strField, $this->strTable)) {

                $arrOrderBy[] = $strField . ' ' . $strOrder;
            }
        }

        $this->strOrderBy = implode(',', $arrOrderBy);
    }


    protected function convertDcConfigToCatalog($arrReturn, $arrDataContainer, $strDcConfigType)
    {

        if ($arrDataContainer[$strDcConfigType]['enableVersioning']) {

            $arrReturn['useVC'] = $arrDataContainer['config']['enableVersioning'] ? '1' : '';
        }

        if (($arrDataContainer[$strDcConfigType]['ptable']??'')) {
            $arrReturn['pTable'] = $arrDataContainer['config']['ptable'];
        }

        $arrDataContainer[$strDcConfigType]['ctable'] = $arrDataContainer[$strDcConfigType]['ctable'] ?? [];
        if (is_array($arrDataContainer[$strDcConfigType]['ctable']) && !empty($arrDataContainer['config']['ctable'])) {

            if (in_array('tl_content', $arrDataContainer[$strDcConfigType]['ctable'])) {

                $arrReturn['addContentElements'] = '1';
            }

            $arrReturn['cTables'] = serialize($arrDataContainer[$strDcConfigType]['ctable']);
        }

        return $arrReturn;
    }


    protected function convertDcSortingToCatalog($arrReturn, $arrDataContainer, $strDcConfigType)
    {

        if (is_array($arrDataContainer[$strDcConfigType]['sorting'])) {

            if (isset($arrDataContainer[$strDcConfigType]['sorting']['mode'])) {

                $arrReturn['mode'] = $arrDataContainer[$strDcConfigType]['sorting']['mode'];
            }

            if (isset($arrDataContainer[$strDcConfigType]['sorting']['flag'])) {

                $arrReturn['flag'] = $arrDataContainer[$strDcConfigType]['sorting']['flag'];
            }

            if (isset($arrDataContainer[$strDcConfigType]['sorting']['panelLayout']) && is_string($arrDataContainer[$strDcConfigType]['sorting']['panelLayout'])) {

                $arrPanelLayout = preg_split('/(,|;)/', $arrDataContainer[$strDcConfigType]['sorting']['panelLayout']);
                $arrReturn['panelLayout'] = serialize($arrPanelLayout);
            }

            if (is_array($arrDataContainer[$strDcConfigType]['sorting']['fields']) && !empty($arrDataContainer[$strDcConfigType]['sorting']['fields'])) {

                $arrFields = [];
                $arrSortingFields = $arrDataContainer[$strDcConfigType]['sorting']['fields'];

                foreach ($arrSortingFields as $strField) {

                    $strUpperCaseField = strtoupper($strField);

                    if (stripos($strUpperCaseField, 'ASC') || stripos($strUpperCaseField, 'DESC')) {
                        $arrFieldParameter = explode(' ', $strField);
                        if (!Toolkit::isEmpty($arrFieldParameter[0])) {
                            $arrFields[] = $arrFieldParameter[0];
                        }

                        continue;
                    }

                    $arrFields[] = $strField;
                }

                $arrReturn['sortingFields'] = serialize($arrFields);
            }

            $arrDataContainer[$strDcConfigType]['sorting']['headerFields'] = $arrDataContainer[$strDcConfigType]['sorting']['headerFields'] ?? [];

            if (is_array($arrDataContainer[$strDcConfigType]['sorting']['headerFields']) && !empty($arrDataContainer[$strDcConfigType]['sorting']['headerFields'])) {
                $arrReturn['headerFields'] = $arrDataContainer[$strDcConfigType]['sorting']['headerFields'];
            }
        }

        return $arrReturn;
    }


    protected function convertDcLabelToCatalog($arrReturn, $arrDataContainer, $strDcConfigType)
    {

        if (isset($arrDataContainer[$strDcConfigType]['label']) && is_array($arrDataContainer[$strDcConfigType]['label'])) {

            if (isset($arrDataContainer[$strDcConfigType]['label']['format'])) {
                $arrReturn['format'] = $arrDataContainer[$strDcConfigType]['label']['format'];
            }
            if (($arrDataContainer[$strDcConfigType]['label']['showColumns']??'')) {
                $arrReturn['showColumns'] = '1';
            }
            if (isset($arrDataContainer[$strDcConfigType]['label']['fields']) && is_array($arrDataContainer[$strDcConfigType]['label']['fields']) && !empty($arrDataContainer[$strDcConfigType]['label']['fields'])) {
                $arrReturn['labelFields'] = serialize($arrDataContainer[$strDcConfigType]['label']['fields']);
            }
        }

        return $arrReturn;
    }


    protected function convertDcOperationsToCatalog($arrReturn, $arrDataContainer, $strDcConfigType)
    {

        if ($this->blnCore) {
            $arrReturn['operations'] = '';
            return $arrReturn;
        }

        if (isset($arrDataContainer[$strDcConfigType]['operations']) && is_array($arrDataContainer[$strDcConfigType]['operations'])) {

            $arrOperators = [];
            $arrOperatorParameter = array_keys($arrDataContainer[$strDcConfigType]['operations']);

            if (is_array($arrOperatorParameter) && !empty($arrOperatorParameter)) {

                foreach ($arrOperatorParameter as $strOperator) {

                    if (in_array($strOperator, Toolkit::$arrOperators)) {

                        $arrOperators[] = $strOperator;
                    }
                }

                $arrReturn['operations'] = serialize($arrOperators);
            }
        }

        return $arrReturn;
    }


    protected function convertCatalogToDcConfig($arrReturn, $arrCatalog, $strDcConfigType)
    {

        if (!isset($arrReturn[$strDcConfigType])) {
            $arrReturn[$strDcConfigType] = [];
        }

        if (!is_array($arrReturn[$strDcConfigType])) {
            $arrReturn[$strDcConfigType] = [];
        }

        $arrConfigDc = [
            '_tables' => [],
            'enableVersioning' => (bool)$arrCatalog['useVC'] ?? false,
            'ptable' => $arrReturn[$strDcConfigType]['ptable'] ?? '',
            'ctable' => $arrReturn[$strDcConfigType]['ctable'] ?? [],
            'onsubmit_callback' => is_array($arrReturn[$strDcConfigType]['onsubmit_callback']) ? $arrReturn[$strDcConfigType]['onsubmit_callback'] : []
        ];

        if (!Toolkit::isEmpty($arrCatalog['pTable'])) {

            $arrConfigDc['ptable'] = $arrCatalog['pTable'];

            if ($arrCatalog['pTable'] !== $arrConfigDc['ptable']) {
                $arrConfigDc['_tables'][] = $arrCatalog['pTable'];
            }
        }

        if (is_array($arrCatalog['cTables']) && !empty($arrCatalog['cTables'])) {
            foreach ($arrCatalog['cTables'] as $strTable) {
                if (is_array($arrConfigDc['ctable']) && !in_array($strTable, $arrConfigDc['ctable'])) {
                    $arrConfigDc['ctable'][] = $strTable;
                    $arrConfigDc['_tables'][] = $strTable;
                }
            }
        }

        if ($arrCatalog['addContentElements'] && is_array($arrConfigDc['ctable']) && !in_array('tl_content', $arrConfigDc['ctable'])) {
            $arrConfigDc['ctable'][] = 'tl_content';
            $arrConfigDc['_tables'][] = 'tl_content';
        }

        if ($arrCatalog['useGeoCoordinates']) {
            $arrConfigDc['onsubmit_callback'][] = ['CatalogManager\DcCallbacks', 'generateGeoCords'];
        }

        $arrConfigDc['onsubmit_callback'][] = ['CatalogManager\DcCallbacks', 'checkForDynValues'];

        foreach ($arrConfigDc as $strKey => $strValue) {

            $arrReturn[$strDcConfigType][$strKey] = $strValue;
        }

        return $arrReturn;
    }

    protected function convertCatalogToDcSorting($arrReturn, $arrCatalog, $strDcConfigType)
    {

        if (!is_array($arrReturn[$strDcConfigType])) $arrReturn[$strDcConfigType] = [];

        $arrDefaults = [
            'flag' => $arrReturn[$strDcConfigType]['sorting']['flag'] ?? '',
            'fields' => $arrReturn[$strDcConfigType]['sorting']['fields'] ?? '',
            'panelLayout' => $arrReturn[$strDcConfigType]['sorting']['panelLayout'] ?? '',
            'headerFields' => $arrReturn[$strDcConfigType]['sorting']['headerFields'] ?? ''
        ];

        $arrSortingDc = $this->setDcSortingByMode((int)$arrCatalog['mode'], $arrCatalog, $arrDefaults);

        foreach ($arrSortingDc as $strKey => $strValue) {
            $arrReturn[$strDcConfigType]['sorting'][$strKey] = $strValue;
        }

        return $arrReturn;
    }

    protected function convertCatalogToDcLabel($arrReturn, $arrCatalog, $strDcConfigType)
    {

        if (!is_array($arrReturn[$strDcConfigType])) $arrReturn[$strDcConfigType] = [];

        $arrDefaults = [
            'fields' => $arrReturn[$strDcConfigType]['sorting']['fields'] ?? ''
        ];

        $arrLabelDc = $this->setDcLabelByMode((int)$arrCatalog['mode'], $arrCatalog, $arrDefaults);

        foreach ($arrLabelDc as $strKey => $strValue) {

            $arrReturn[$strDcConfigType]['label'][$strKey] = $strValue;
        }

        return $arrReturn;
    }


    protected function convertCatalogToDcFieldsAndPalettes($arrReturn, $arrCatalog, $strDcConfigType)
    {

        $arrDcFields = [];
        $blnFieldsetStart = false;
        $objFieldBuilder = new CatalogFieldBuilder();
        $objFieldBuilder->initialize($this->strTable);
        $arrFields = $objFieldBuilder->getCatalogFields(true, null, true);

        $arrPaletteFields = [];
        $arrFieldsetStart = [];
        $arrPickedPalettes = null;

        if (is_array($arrFields) && !empty($arrFields)) {

            foreach ($arrFields as $strFieldname => $arrField) {

                if ($arrField['type'] == 'fieldsetStart') {

                    $arrPickedPalettes = \StringUtil::deserialize($arrField['dcPaletteLegend']);
                    $arrFieldsetStart = $arrField;
                    $blnFieldsetStart = true;

                    continue;
                }

                if ($arrField['type'] == 'fieldsetStop') {

                    if (is_array($arrPickedPalettes)) $this->DcModifier->addLegendToPalette($arrPaletteFields, $arrPickedPalettes, $arrReturn['palettes'], $arrFieldsetStart);

                    $arrFieldsetStart = [];
                    $arrPaletteFields = [];
                    $blnFieldsetStart = false;
                    $arrPickedPalettes = null;

                    continue;
                }

                if ($blnFieldsetStart && is_array($arrPickedPalettes)) $arrPaletteFields[] = $strFieldname;

                if (isset($arrField['dcPaletteField']) && $arrField['dcPaletteField'] && !$blnFieldsetStart) {

                    $arrPickedPalettes = \StringUtil::deserialize($arrField['dcPaletteField']);

                    if (is_array($arrPickedPalettes)) $this->DcModifier->addFieldToPalette($arrField, $arrPickedPalettes, $arrReturn['palettes']);

                    $arrPickedPalettes = null;
                }

                if (isset($arrField['_dcFormat'])) {
                    if ($arrField['_core'] ?? '') {
                        $arrField['_dcFormat']['_disableFEE'] = '';
                        $arrField['_dcFormat']['_placeholder'] = '';
                        $arrField['_dcFormat']['_fieldname'] = $strFieldname;
                        $arrField['_dcFormat']['_palette'] = 'general_legend';
                        $arrField['_dcFormat']['_cssID'] = ['', $strFieldname];
                        $arrField['_dcFormat']['_type'] = $arrField['_dcFormat']['inputType'] ?? '';
                    }
                    $arrDcFields[$strFieldname] = $arrField['_dcFormat'];
                }
            }
        }

        $arrReturn[$strDcConfigType] = $arrDcFields;

        return $arrReturn;
    }


    protected function convertCatalogToDcOperations($arrReturn, $arrCatalog, $strDcConfigType)
    {

        $arrErrorTables = [];
        $objReviseRelatedTables = new ReviseRelatedTables();

        if ($objReviseRelatedTables->reviseCatalogTables($this->strTable, $arrReturn['config']['ptable'], $arrReturn['config']['ctable'])) {

            foreach ($objReviseRelatedTables->getErrorTables() as $strTable) {

                \Message::addError(sprintf("Table '%s' can not be used as relation. Please delete all rows or create valid pid value.", $strTable));
                $arrErrorTables[] = $strTable;

                if ($strTable == $arrReturn['config']['ptable']) {
                    $arrReturn['config']['ptable'] = '';
                }

                if (in_array($strTable, $arrReturn['config']['ctable'])) {
                    $intIndex = array_search($strTable, $arrReturn['config']['ctable']);
                    unset($arrReturn['config']['ctable'][$intIndex]);
                }
            }
        }

        if (!is_array($arrReturn[$strDcConfigType]['operations'])) {

            $arrReturn[$strDcConfigType]['operations'] = [];
        }

        if (is_array($arrReturn['config']['ctable'])) {

            foreach ($arrReturn['config']['ctable'] as $strTable) {

                if (is_array($arrReturn['config']['_tables']) && !in_array($strTable, $arrReturn['config']['_tables'])) continue;

                $arrOperator = [];
                $strOperation = sprintf('go_to_%s', $strTable);

                $arrOperator[$strOperation] = [

                    'href' => sprintf('table=%s&ctlg_table=%s', $strTable, $this->strTable),
                    'label' => [sprintf($GLOBALS['TL_LANG']['catalog_manager']['operations']['goTo'][0], $strTable), sprintf($GLOBALS['TL_LANG']['catalog_manager']['operations']['goTo'][1], $strTable)],
                    'icon' => $strTable !== 'tl_content' ? $this->IconGetter->setCatalogIcon($strTable) : 'articles.gif'
                ];

                array_insert($arrReturn[$strDcConfigType]['operations'], 1, $arrOperator);
            }
        }

        return $arrReturn;
    }
}