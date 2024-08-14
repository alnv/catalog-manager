<?php

namespace Alnv\CatalogManagerBundle;

use Contao\StringUtil;
use Contao\System;
use Alnv\CatalogManagerBundle\Fields\DateInput;
use Alnv\CatalogManagerBundle\Fields\Select;
use Alnv\CatalogManagerBundle\Fields\Checkbox;
use Alnv\CatalogManagerBundle\Fields\Text;
use Alnv\CatalogManagerBundle\Fields\Radio;
use Contao\Controller;
use Contao\Input;
use Contao\Database;
use Symfony\Component\HttpFoundation\Request;

class OptionsGetter extends CatalogController
{

    protected $strModuleID;
    protected array $arrCache = [];
    protected array $arrField = [];
    protected array $arrQueries = [];
    protected array $arrCatalog = [];
    protected string $strActiveTable = '';
    protected array $arrActiveEntity = [];
    protected array $arrCatalogFields = [];

    public function __construct($arrField, $strModuleID = '', $arrQueries = [])
    {

        parent::__construct();

        $this->arrField = $arrField;
        $this->strModuleID = $strModuleID;

        foreach ($arrQueries as $strQuery) if (!Toolkit::isEmpty($strQuery)) $this->arrQueries[] = $strQuery;

        $this->import(CatalogInput::class);
        $this->import(OrderByHelper::class);
        $this->import(SQLQueryHelper::class);
        $this->import(SQLQueryBuilder::class);
        $this->import(CatalogDcExtractor::class);
        $this->import(I18nCatalogTranslator::class);
    }

    public function isForeignKey()
    {

        if (isset($this->arrField['optionsType']) && $this->arrField['optionsType'] && $this->arrField['optionsType'] == 'useForeignKey') {

            return true;
        }

        return false;
    }

    public function getForeignKey()
    {

        return $this->setForeignKey();
    }

    public function getOptions()
    {

        switch ($this->arrField['optionsType'] ?? '') {

            case 'useOptions':
                return $this->getKeyValueOptions();

            case 'useForeignKey':
                $this->arrField['dbTableKey'] = 'id';
                return $this->getDbOptions();

            case 'useDbOptions':
                return $this->getDbOptions();

            case 'useActiveDbOptions':
                return $this->getActiveDbOptions();
        }

        if (!isset($this->arrField['optionsType']) && in_array($this->arrField['fieldname'], ['country', 'countries']) && in_array($this->arrField['type'], ['radio', 'select', 'checkbox'])) {

            return System::getCountries();
        }

        return [];
    }

    public function getTableEntities()
    {

        switch ($this->arrField['optionsType'] ?? '') {

            case 'useDbOptions':
            case 'useForeignKey':

                if (!$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] || !$this->arrField['dbTableValue']) {
                    return null;
                }

                if (!$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists($this->arrField['dbTable'])) {
                    return null;
                }

                if (!$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists($this->arrField['dbTableKey'], $this->arrField['dbTable'])) {
                    return null;
                }

                if (!$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists($this->arrField['dbTableValue'], $this->arrField['dbTable'])) {
                    return null;
                }

                return $this->getResults(true);

            case 'useActiveDbOptions':

                $strDbColumn = $this->arrField['dbColumn'];

                if (!$this->arrField['dbTable'] || !$strDbColumn) {
                    return null;
                }

                if (!$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists($this->arrField['dbTable'])) {
                    return null;
                }

                if (!$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists($strDbColumn, $this->arrField['dbTable'])) {
                    return null;
                }

                return $this->getResults(false);
        }

        return null;
    }

    protected function setValueToOption(&$arrOptions, $strValue, $strLabel = '', $strTable = '')
    {

        $strKey = $strValue;
        $strKey = StringUtil::decodeEntities($strKey);
        $strText = $strLabel ?: $strKey;

        if (isset($this->arrField['dbParseDate']) && $this->arrField['dbParseDate']) {
            $strFormat = $this->arrField['dbMonthBeginFormat'] ?: 'F Y';
            if ($this->arrField['dbDateFormat'] == 'yearBegin') $strFormat = $this->arrField['dbYearBeginFormat'] ?: 'Y';
            if ($this->arrField['dbDateFormat'] == 'dayBegin') $strFormat = $this->arrField['dbDayBeginFormat'] ?: 'l, F Y';
            $strKey = DateInput::parseValue($strValue, ['rgxp' => $this->arrField['dbDateFormat']], []);
            $strText = Controller::parseDate($strFormat, $strValue);
        } else {
            $strText = $this->I18nCatalogTranslator->get('option', $strKey, ['title' => $strText, 'table' => $strTable]);
        }

        if ($strKey && !in_array($strKey, array_keys($arrOptions))) {
            $arrOptions[$strKey] = $strText;
        }

        return $arrOptions;
    }

    protected function parseCatalogValues($varValue, $strFieldname, $arrCatalog)
    {

        $arrField = $this->arrCatalogFields[$strFieldname];

        switch ($arrField['type']) {
            case 'select':
                return Select::parseValue($varValue, $arrField, $arrCatalog);
            case 'checkbox':
                return Checkbox::parseValue($varValue, $arrField, $arrCatalog);
            case 'radio':
                return Radio::parseValue($varValue, $arrField, $arrCatalog);
            case 'text':
                return Text::parseValue($varValue, $arrField, $arrCatalog);
        }

        return $varValue;
    }

    protected function getResults($blnUseValidValue = false)
    {

        $arrSQLQuery = [
            'table' => $this->arrField['dbTable']
        ];

        $this->getActiveTable();
        $this->getActiveEntityValues();
        $strOrderBy = $this->getOrderBy();
        $arrDbTaxonomies = Toolkit::deserialize($this->arrField['dbTaxonomy']);
        $arrQueries = is_array($arrDbTaxonomies) && isset($arrDbTaxonomies['query']) ? $arrDbTaxonomies['query'] : [];

        $arrSQLQuery['where'] = Toolkit::parseQueries($arrQueries, function ($arrQuery) use ($blnUseValidValue) {

            $blnValidValue = true;
            $blnIgnoreEmptyValues = isset($this->arrField['dbIgnoreEmptyValues']) && $this->arrField['dbIgnoreEmptyValues'];

            if ($blnIgnoreEmptyValues && in_array($arrQuery['operator'], ['isEmpty', 'isNotEmpty'])) $blnIgnoreEmptyValues = false;

            $arrQuery['value'] = $this->getParseQueryValue($arrQuery['value'], $arrQuery['operator'], $blnValidValue);
            $arrQuery['allowEmptyValues'] = $blnIgnoreEmptyValues ? false : true;

            if (!$blnValidValue && $blnUseValidValue) return null;

            return $arrQuery;
        });

        if (is_array($this->arrQueries) && !empty($this->arrQueries)) {
            $arrSQLQuery['where'][] = [
                'multiple' => true,
                'operator' => 'regexp',
                'field' => $this->arrField['dbTableValue'],
                'value' => implode(',', $this->arrQueries)
            ];
        }

        $strWhereStatement = $this->SQLQueryBuilder->getWhereQuery($arrSQLQuery);

        if (Toolkit::isEmpty($strOrderBy)) {
            $this->CatalogDcExtractor->initialize($this->arrField['dbTable']);
            $this->CatalogDcExtractor->extract();
            if ($this->CatalogDcExtractor->hasOrderByStatement()) {
                $strOrderBy = ' ORDER BY ' . $this->CatalogDcExtractor->getOrderByStatement();
            }
        }

        $strQuery = sprintf('SELECT * FROM %s%s%s', $this->arrField['dbTable'], $strWhereStatement, $strOrderBy);

        if (isset($GLOBALS['TL_HOOKS']['catalogManagerModifyOptionsGetter']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerModifyOptionsGetter'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerModifyOptionsGetter'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($strQuery, $arrSQLQuery, $this->arrField, $this->strModuleID);
            }
        }

        $objDbOptions = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare($strQuery)->execute($this->SQLQueryBuilder->getValues());

        return $objDbOptions;
    }

    protected function getActiveTable()
    {

        $this->strActiveTable = Input::get('table') ? Input::get('table') : Input::get('ctlg_table');

        if (Toolkit::isEmpty($this->strActiveTable) && Input::get('do')) {

            $arrTables = Toolkit::getBackendModuleTablesByDoAttribute(Input::get('do'));

            if (is_array($arrTables) && isset($arrTables[0])) $this->strActiveTable = $arrTables[0];
        }
    }

    protected function getDbOptions()
    {

        $arrOptions = [];
        $objDbOptions = $this->getTableEntities();

        if ($objDbOptions === null) return $arrOptions;
        if (!$objDbOptions->numRows) return $arrOptions;

        while ($objDbOptions->next()) {
            $this->setValueToOption($arrOptions, $objDbOptions->{$this->arrField['dbTableKey']}, $objDbOptions->{$this->arrField['dbTableValue']}, $this->arrField['dbTable']);
        }

        $arrOrderBy = StringUtil::deserialize($this->arrField['dbOrderBy'], true);

        if (empty($arrOrderBy) && count($arrOptions) < 50) {
            asort($arrOptions);
        }

        if (isset($GLOBALS['TL_HOOKS']['catalogManagerModifyOptions']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerModifyOptions'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerModifyOptions'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($arrOptions, $this->arrField, $this->arrCatalog, $this->arrCatalogFields);
                }
            }
        }

        return $arrOptions;
    }

    protected function getActiveDbOptions()
    {

        $arrOptions = [];
        $objEntities = $this->getTableEntities();
        $strDbColumn = $this->arrField['dbColumn'];

        if ($objEntities === null) return $arrOptions;
        if (!$objEntities->numRows) return $arrOptions;

        $this->arrCatalog = $this->SQLQueryHelper->getCatalogByTablename($this->arrField['dbTable']);
        $this->arrCatalogFields = $this->SQLQueryHelper->getCatalogFieldsByCatalogTablename($this->arrField['dbTable']);

        while ($objEntities->next()) {

            $strOriginValue = $objEntities->{$strDbColumn};
            $varValue = $this->parseCatalogValues($strOriginValue, $strDbColumn, []);

            if (is_array($varValue)) {

                $arrLabels = array_values($varValue);
                $arrOriginValues = array_keys($varValue);

                if (!empty($arrLabels) && is_array($arrLabels)) {
                    foreach ($arrLabels as $intPosition => $strLabel) {
                        $this->setValueToOption($arrOptions, $arrOriginValues[$intPosition], $strLabel, $this->arrField['dbTable']);
                    }
                }
            } else {
                $this->setValueToOption($arrOptions, $strOriginValue, $varValue, $this->arrField['dbTable']);
            }
        }

        $arrOrderBy = StringUtil::deserialize($this->arrField['dbOrderBy'], true);

        if (empty($arrOrderBy) && count($arrOptions) < 50) {
            asort($arrOptions);
        }

        if (isset($GLOBALS['TL_HOOKS']['catalogManagerModifyOptions']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerModifyOptions'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerModifyOptions'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $this->{$arrCallback[0]}->{$arrCallback[1]}($arrOptions, $this->arrField, $this->arrCatalog, $this->arrCatalogFields);
                }
            }
        }

        return $arrOptions;
    }

    protected function getParseQueryValue($strValue = '', $strOperator = '', &$blnValidValue = true)
    {

        if (!empty($strValue) && is_string($strValue)) {

            $strInsertTagValue = Controller::replaceInsertTags($strValue);

            if (!Toolkit::isEmpty($strInsertTagValue)) {

                $strValue = $strInsertTagValue;
            }
        }

        if (!empty($strValue) && is_string($strValue) && strpos($strValue, '{{') !== false) {

            $strActiveValue = '';
            $arrTags = preg_split('/{{(([^{}]*|(?R))*)}}/', $strValue, -1, PREG_SPLIT_DELIM_CAPTURE);

            $strInsertTag = implode('', $arrTags);
            $strParameter = explode('::', $strInsertTag);
            $strFieldname = $strParameter[0];
            $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));

            if ($strFieldname !== '' && $strFieldname !== null) {
                $strActiveValue = $this->arrActiveEntity[$strFieldname] ?? '';

                if (!$blnIsBackend) {
                    $strActiveValue = $this->CatalogInput->getActiveValue($strFieldname);
                }

                if (!$blnIsBackend && (Toolkit::isEmpty(Input::post('FORM_SUBMIT')) && Input::get('act' . $this->strModuleID))) {
                    $strActiveValue = $this->arrActiveEntity[$strFieldname] ?: '';
                }

                if (isset($strParameter[1]) && $strParameter[1] == 'tree') {
                    $objDatabase = Database::getInstance();
                    $objField = $objDatabase->prepare('SELECT * FROM tl_catalog_fields WHERE `fieldname` = ?')->limit(1)->execute($strFieldname); //@todo don`t work with multiple fieldnames

                    if ($objField->numRows) {
                        if ($objField->optionsType) {
                            $objEntity = $objDatabase->prepare('SELECT * FROM ' . $objField->dbTable . ' WHERE `' . $objField->dbTableKey . '` = ?')
                                ->limit(1)
                                ->execute($strActiveValue);

                            if ($objEntity->numRows) {
                                $strActiveValue = $objEntity->id ?: $strActiveValue;
                            }
                        }
                    }
                }
            }

            $blnValidValue = $this->isValidValue($strActiveValue);
            $strValue = $strActiveValue;
        }

        if (in_array($strOperator, ['contain', 'notContain']) && is_string($strValue)) {
            $strValue = explode(',', $strValue);
        }

        return Toolkit::prepareValueForQuery($strValue);
    }

    protected function getKeyValueOptions()
    {

        $arrOptions = [];

        if ($this->arrField['options']) {

            $arrFieldOptions = StringUtil::deserialize($this->arrField['options']);

            if (!empty($arrFieldOptions) && is_array($arrFieldOptions)) {

                foreach ($arrFieldOptions as $arrOption) {

                    $this->setValueToOption($arrOptions, $arrOption['key'], $arrOption['value']);
                }
            }
        }

        return $arrOptions;
    }

    protected function setForeignKey()
    {

        $strLabelColumn = $this->arrField['dbTableValue'] ?: $this->arrField['dbTableKey'];

        if (!$this->arrField['dbTable'] || !$strLabelColumn) {

            return '';
        }

        return $this->arrField['dbTable'] . '.' . $strLabelColumn;
    }

    protected function getActiveEntityValues()
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));
        $strMode = $blnIsBackend ? 'BE' : 'FE';

        switch ($strMode) {

            case 'BE':

                $strID = Input::get('id');

                if (Toolkit::isEmpty($strID) || Toolkit::isEmpty($this->strActiveTable)) {

                    return null;
                }

                if (!$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists($this->strActiveTable)) {

                    return null;
                }

                $arrQuery = [

                    'table' => $this->strActiveTable,

                    'pagination' => [

                        'limit' => 1
                    ],

                    'where' => [

                        [
                            'field' => 'id',
                            'value' => $strID,
                            'operator' => 'equal'
                        ]
                    ],

                    'joins' => []
                ];

                $strLanguageColumn = '';
                $strDefaultLanguage = '';
                $objCatalog = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare('SELECT * FROM tl_catalog WHERE tablename = ? LIMIT 1')->execute($this->strActiveTable);

                if ($objCatalog->numRows) {

                    if ($objCatalog->pTable && $this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists('pid', $this->strActiveTable)) {

                        $arrQuery['joins'][] = [

                            'field' => 'pid',
                            'onField' => 'id',
                            'multiple' => false,
                            'table' => $this->strActiveTable,
                            'onTable' => $objCatalog->pTable
                        ];
                    }

                    if ($this->hasLanguageNavigationBar($objCatalog)) {

                        $strDefaultLanguage = Input::get('ctlg_language') ?: $objCatalog->fallbackLanguage;
                        $strLanguageColumn = $objCatalog->languageEntityColumn;
                    }
                }

                $this->arrActiveEntity = $this->SQLQueryBuilder->execute($arrQuery)->row();

                if ($strLanguageColumn && $strDefaultLanguage) {

                    $this->arrActiveEntity[$strLanguageColumn] = $strDefaultLanguage;
                }

                return null;

            case 'FE':

                if (!$this->arrField['pid']) {

                    return null;
                }

                $objCatalog = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare('SELECT * FROM tl_catalog WHERE id = ?')->limit(1)->execute($this->arrField['pid']);

                if (!$objCatalog->tablename || !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists($objCatalog->tablename)) {

                    return null;
                }

                $strID = $this->strModuleID ? Input::get('id' . $this->strModuleID) : Input::get('id');
                $strAct = $this->strModuleID ? Input::get('act' . $this->strModuleID) : Input::get('act');

                if (!$strID && !$strAct) {

                    return null;
                }

                $arrQuery = [

                    'table' => $objCatalog->tablename,

                    'pagination' => [

                        'limit' => 1
                    ],

                    'where' => [

                        [
                            'field' => 'id',
                            'value' => $strID,
                            'operator' => 'equal'
                        ]
                    ],

                    'joins' => []
                ];

                if ($objCatalog->pTable && $this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists('pid', $objCatalog->tablename)) {
                    $arrQuery['joins'][] = [
                        'field' => 'pid',
                        'onField' => 'id',
                        'multiple' => false,
                        'onTable' => $objCatalog->pTable,
                        'table' => $objCatalog->tablename
                    ];
                }

                $this->arrActiveEntity = $this->SQLQueryBuilder->execute($arrQuery)->row();

                if ($this->hasLanguageNavigationBar($objCatalog)) {

                    $this->arrActiveEntity[$objCatalog->languageEntityColumn] = $GLOBALS['TL_LANGUAGE'] ?: $objCatalog->fallbackLanguage;
                }

                break;

        }

        if (!is_array($this->arrActiveEntity)) {

            $this->arrActiveEntity = [];
        }
    }

    protected function getOrderBy(): string
    {

        if (isset($this->arrField['dbOrderBy'])) {
            $arrOrderBy = StringUtil::deserialize($this->arrField['dbOrderBy']);
            if (is_array($arrOrderBy) && !empty($arrOrderBy)) {
                $this->arrField['_orderBy'] = $this->OrderByHelper->getOrderByQuery($arrOrderBy, $this->arrField['dbTable']);
            }
        }

        if (isset($this->arrField['_orderBy']) && !Toolkit::isEmpty($this->arrField['_orderBy'])) {
            return ' ' . $this->arrField['_orderBy'];
        }

        return '';
    }

    protected function isValidValue($strValue)
    {

        if (!Toolkit::isEmpty($strValue)) return true;

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));
        $strMode = $blnIsBackend ? 'BE' : 'FE';

        switch ($strMode) {
            case 'BE':
                $strID = Input::get('id');
                if (Toolkit::isEmpty($strID) || Toolkit::isEmpty($this->strActiveTable)) return false;
                break;
            case 'FE':
                $strID = $this->strModuleID ? Input::get('id' . $this->strModuleID) : Input::get('id');
                if (Input::get('act' . $this->strModuleID)) return true;
                if (!$strID) return false;
                break;
        }

        return true;
    }

    protected function hasLanguageNavigationBar($objCatalog)
    {
        return $objCatalog->enableLanguageBar && !in_array($objCatalog->mode, ['5', '6']) && $objCatalog->languageEntitySource == 'currentTable';
    }
}