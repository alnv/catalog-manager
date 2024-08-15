<?php

namespace Alnv\CatalogManagerBundle;

use Contao\StringUtil;
use Alnv\CatalogManagerBundle\Fields\Checkbox;
use Alnv\CatalogManagerBundle\Fields\Radio;
use Alnv\CatalogManagerBundle\Fields\Select;
use Alnv\CatalogManagerBundle\Fields\Upload;
use Alnv\CatalogManagerBundle\Fields\Number;
use Alnv\CatalogManagerBundle\Fields\DateInput;
use Alnv\CatalogManagerBundle\Fields\Textarea;
use Alnv\CatalogManagerBundle\Fields\DbColumn;
use Ausi\SlugGenerator\SlugGenerator;
use Ausi\SlugGenerator\SlugOptions;
use Contao\Database;
use Contao\PageModel;
use Contao\Controller;
use Contao\System;
use Contao\ArrayUtil;
use Contao\Config;
use Symfony\Component\HttpFoundation\Request;

class Toolkit
{

    public static array $arrDateRgxp = ['date', 'time', 'datim'];

    public static array $arrRequireSortingModes = ['4', '5', '6'];

    public static array $arrOperators = ['cut', 'copy', 'invisible'];

    public static array $arrDoNotRenderInFastMode = ['upload', 'map'];

    public static array $arrDigitRgxp = ['digit', 'natural', 'prcnt'];

    public static array $nullableOperators = ['isNotEmpty', 'isEmpty'];

    public static array $arrModeTypes = ['0', '1', '2', '3', '4', '5', '6'];

    public static array $arrArrayOptions = ['origin', 'catalogFields', 'catalogEntityFields'];

    public static array $arrSocialSharingButtons = ['mail', 'twitter', 'facebook', 'xing', 'linkedin'];

    public static array $arrImageExtensions = ['jpg', 'jpeg', 'gif', 'png', 'svg', 'svgz', 'bmp', 'tiff', 'tif'];

    public static array $arrFileExtensions = ['odt', 'ods', 'odp', 'odg', 'ott', 'ots', 'otp', 'otg', 'pdf', 'doc', 'docx', 'dot', 'dotx', 'xls', 'xlsx', 'xlt', 'xltx', 'ppt', 'pptx', 'pot', 'potx', 'mp3', 'mp4', 'm4a', 'm4v', 'webm', 'ogg', 'ogv', 'wma', 'wmv', 'ram', 'rm', 'mov', 'zip', 'rar', '7z'];

    public static array $arrSqlTypes = [
        'c256' => "varchar(256) NOT NULL default ''",
        'c1' => "char(1) NOT NULL default ''",
        'c16' => "varchar(16) NOT NULL default ''",
        'c32' => "varchar(32) NOT NULL default ''",
        'c64' => "varchar(64) NOT NULL default ''",
        'c128' => "varchar(128) NOT NULL default ''",
        'c512' => "varchar(512) NOT NULL default ''",
        'c1024' => "varchar(1024) NOT NULL default ''",
        'c2048' => "varchar(2048) NOT NULL default ''",
        'i5' => "smallint(5) unsigned NOT NULL default '0'",
        'i10' => "int(10) unsigned NOT NULL default '0'",
        'is10' => "int(10) signed NOT NULL default '0'",
        'iNotNull10' => "int(10) unsigned NULL",
        'isNotNull10' => "int(10) signed NULL",
        'text' => "text NULL",
        'longtext' => "longtext NULL",
        'blob' => "blob NULL",
        'longblob' => "longblob NULL",
        'binary' => "binary(16) NULL"
    ];

    public static array $arrFormTemplates = [
        'checkbox' => 'form_checkbox',
        'textarea' => 'form_textarea',
        'number' => 'form_textfield',
        'text' => 'form_textfield',
        'date' => 'form_textfield',
        'select' => 'form_select',
        'upload' => 'form_upload',
        'hidden' => 'form_hidden',
        'radio' => 'form_radio'
    ];

    public static function invisiblePaletteFields(): array
    {
        return [
            'invisible',
            'start',
            'stop'
        ];
    }

    public static function columnOnlyFields(): array
    {
        return [
            'dbColumn'
        ];
    }

    public static function readOnlyFields(): array
    {
        return [
            'message'
        ];
    }

    public static function wrapperFields(): array
    {
        return [
            'fieldsetStart',
            'fieldsetStop'
        ];
    }

    public static function excludeFromDc(): array
    {
        return [
            'map',
            'fieldsetStop',
            'fieldsetStart'
        ];
    }

    public static function setDcConformInputType($strType)
    {
        return $GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPE_CONVERTER'][$strType] ?? '';
    }

    public static function convertCatalogTypeToFormType($strType): string
    {

        $arrFormTypes = [
            'radio' => 'radio',
            'map' => 'textfield',
            'select' => 'select',
            'upload' => 'upload',
            'text' => 'textfield',
            'date' => 'textfield',
            'number' => 'textfield',
            'hidden' => 'textfield',
            'textarea' => 'textarea',
            'checkbox' => 'checkbox',
            'message' => 'catalogMessageWidget'
        ];

        return $arrFormTypes[$strType] ?? '';
    }

    public static function setCatalogConformInputType($strField): string
    {

        $strType = $strField['inputType'] ?? '';

        if (Toolkit::isEmpty($strType)) return '';

        if (!Toolkit::isEmpty($strField['eval']['rgxp'] ?? '')) {
            if (in_array($strField['eval']['rgxp'], self::$arrDateRgxp)) return 'date';
        }

        $arrInputTypes = [
            'text' => 'text',
            'radio' => 'radio',
            'select' => 'select',
            'password' => 'text',
            'fileTree' => 'upload',
            'textarea' => 'textarea',
            'checkbox' => 'checkbox',
        ];

        return $arrInputTypes[$strType] ?? 'text';
    }

    public static function getSqlDataType($strType)
    {

        $strSql = self::$arrSqlTypes[$strType] ?? '';

        return $strSql ?: "varchar(256) NOT NULL default ''";
    }

    public static function isDcConformField($arrField): bool
    {

        if (empty($arrField) && !is_array($arrField)) {
            return false;
        }

        if (!isset($arrField['type'])) {
            return false;
        }

        if (in_array($arrField['type'], self::excludeFromDc())) {
            return false;
        }

        return true;
    }

    public static function columnsBlacklist(): array
    {
        return [
            'id',
            'pid',
            'tstamp',
            'origin',
            'sorting',
            'invisible',
        ];
    }

    public static function customizeAbleFields(): array
    {
        return [
            'title',
            'alias',
            'start',
            'stop'
        ];
    }

    public static function isNumericArray($arrValues): bool
    {
        foreach ($arrValues as $varValue) {
            if (!is_numeric($varValue)) {
                return false;
            }
        }
        return true;
    }

    public static function parseCatalog($arrCatalog): array
    {

        $arrCatalog['cTables'] = self::parseStringToArray(($arrCatalog['cTables'] ?? ''));
        $arrCatalog['languages'] = self::parseStringToArray(($arrCatalog['languages'] ?? ''));
        $arrCatalog['operations'] = self::parseStringToArray(($arrCatalog['operations'] ?? ''));
        $arrCatalog['panelLayout'] = self::parseStringToArray(($arrCatalog['panelLayout'] ?? ''));
        $arrCatalog['labelFields'] = self::parseStringToArray(($arrCatalog['labelFields'] ?? ''));
        $arrCatalog['headerFields'] = self::parseStringToArray(($arrCatalog['headerFields'] ?? ''));
        $arrCatalog['sortingFields'] = self::parseStringToArray(($arrCatalog['sortingFields'] ?? ''));

        return $arrCatalog;
    }

    public static function parseStringToArray($strValue): array
    {

        if (isset($strValue) && $strValue && is_string($strValue)) {
            return StringUtil::deserialize($strValue, true);
        }

        if (is_array($strValue)) {
            return $strValue;
        }

        return [];
    }

    public static function removeBreakLines($strValue)
    {

        if (!$strValue || !is_string($strValue)) {
            return $strValue;
        }

        return preg_replace("/\r|\n/", "", $strValue);
    }

    public static function removeApostrophe($strValue)
    {

        if (!$strValue || !is_string($strValue)) {
            return $strValue;
        }

        return str_replace("'", "", $strValue);
    }

    public static function parseConformSQLValue($varValue): string
    {
        return str_replace('-', '_', $varValue);
    }

    public static function isAssoc($arrAssoc): bool
    {

        if (!is_array($arrAssoc)) return false;

        $arrKeys = array_keys($arrAssoc);

        return array_keys($arrKeys) !== $arrKeys;
    }

    public static function prepareValues4Db($arrValues): array
    {

        $arrReturn = [];
        if (!empty($arrValues) && is_array($arrValues)) {
            foreach ($arrValues as $strKey => $varValue) {
                $arrReturn[$strKey] = self::prepareValue4Db($varValue);
            }
        }

        return $arrReturn;
    }

    public static function prepareValue4Db($varValue)
    {

        if (!self::isDefined($varValue)) return $varValue;
        if (is_array($varValue)) return implode(',', $varValue);
        if (is_float($varValue)) return floatval($varValue);

        return $varValue;
    }

    public static function prepareValueForQuery($varValue)
    {

        if (is_array($varValue)) {

            if (!empty($varValue)) {
                $arrReturn = [];
                $varValue = array_filter($varValue, function ($varValue) {
                    if ($varValue === '0') return true;
                    return $varValue;
                });

                foreach ($varValue as $strKey => $strValue) {
                    $arrReturn[$strKey] = Toolkit::prepareValueForQuery($strValue);
                }

                return $arrReturn;
            } else {

                return '';
            }
        }

        if ($varValue === '0') return 0;
        if (is_null($varValue)) return '';

        return $varValue;
    }

    public static function deserialize($strValue): array
    {
        return StringUtil::deserialize($strValue, true);
    }

    public static function getBooleanByValue($varValue): bool
    {

        if (!$varValue) {
            return false;
        }

        return true;
    }

    public static function deserializeAndImplode($strValue, $strDelimiter = ','): string
    {
        if (!$strValue || !is_string($strValue)) {
            return '';
        }

        $arrValue = StringUtil::deserialize($strValue);
        if (!empty($arrValue) && is_array($arrValue)) {
            return implode($strDelimiter, $arrValue);
        }

        return '';
    }

    public static function isDefined($varValue): bool
    {

        if (is_numeric($varValue)) {
            return true;
        }

        if (is_array($varValue)) {
            return true;
        }

        if ($varValue && is_string($varValue)) {
            return true;
        }

        return false;
    }

    public static function parseColumns($arrColumns): array
    {

        $arrReturn = [];
        if (!empty($arrColumns) && is_array($arrColumns)) {
            foreach ($arrColumns as $arrColumn) {
                if ($arrColumn['name'] == 'PRIMARY' || $arrColumn['type'] == 'index') {
                    continue;
                }
                $arrReturn[$arrColumn['name']] = $arrColumn['name'];
            }
        }

        return $arrReturn;
    }

    public static function parseQueries($arrQueries, $fnCallback = null)
    {

        $arrReturn = [];

        if (!empty($arrQueries) && is_array($arrQueries)) {

            foreach ($arrQueries as $arrQuery) {

                if (in_array($arrQuery['operator'], self::$nullableOperators)) $arrQuery['allowEmptyValues'] = '1';
                if (!is_null($fnCallback) && is_callable($fnCallback)) $arrQuery = $fnCallback($arrQuery);

                $arrQuery = self::parseQuery($arrQuery);

                if (is_null($arrQuery)) continue;

                if (!empty($arrQuery['subQueries']) && is_array($arrQuery['subQueries'])) {

                    $arrSubQueries = self::parseQueries($arrQuery['subQueries']);
                    ArrayUtil::arrayInsert($arrSubQueries, 0, [[
                        'field' => $arrQuery['field'],
                        'value' => $arrQuery['value'],
                        'operator' => $arrQuery['operator'],
                    ]]);

                    $arrReturn[] = $arrSubQueries;
                } else {

                    $arrReturn[] = $arrQuery;
                }
            }
        }

        return $arrReturn;
    }

    public static function isEmpty($varValue): bool
    {

        if (is_null($varValue) || $varValue === '') return true;

        return false;
    }

    public static function parseQuery($arrQuery)
    {

        $blnAllowEmptyValue = isset($arrQuery['allowEmptyValues']) && $arrQuery['allowEmptyValues'];

        if (isset($arrQuery['value']) && is_array($arrQuery['value'])) {

            if (!empty($arrQuery['value'])) {
                foreach ($arrQuery['value'] as $strK => $strV) {
                    $arrQuery['value'][$strK] = self::replaceInsertTags($strV);
                }
            }

            if ($arrQuery['operator'] == 'between' && is_array($arrQuery['value'])) {
                if ($arrQuery['value'][0] === '' && $arrQuery['value'][1] === '') return null;
                if ($arrQuery['value'][0] === '') $arrQuery['value'][0] = '0';
                if ($arrQuery['value'][1] === '') {
                    $numOff = (float)$arrQuery['value'][0];
                    $arrQuery['value'][1] = $numOff * $numOff;
                }
            }
        }

        if (isset($arrQuery['value']) && is_string($arrQuery['value'])) {

            $arrQuery['value'] = self::replaceInsertTags($arrQuery['value']);

            if (strpos($arrQuery['value'], ',') && $arrQuery['operator'] != 'equal') {
                $arrQuery['value'] = explode(',', $arrQuery['value']);
            }

            if (is_string($arrQuery['value']) && $arrQuery['operator'] == 'regexp') {
                if (strpos($arrQuery['value'], ' ')) {
                    $arrQuery['value'] = explode(' ', $arrQuery['value']);
                }
            }
        }

        if (isset($arrQuery['value']) && is_array($arrQuery['value']) && !in_array($arrQuery['operator'], ['contain', 'notContain', 'between'])) {
            $arrQuery['multiple'] = true;
        }

        if ((!isset($arrQuery['value']) || $arrQuery['value'] === '') && !$blnAllowEmptyValue) {
            return null;
        }

        if ((is_array($arrQuery['value']) && empty($arrQuery['value'])) && !$blnAllowEmptyValue) {
            return null;
        }


        if (is_numeric($arrQuery['value']) && in_array($arrQuery['operator'], ['lte', 'lt', 'gt', 'gte'])) {
            if (strpos((string)$arrQuery['value'], '.') !== false) {
                $arrQuery['value'] = floatval($arrQuery['value']);
            } else {
                $arrQuery['value'] = (int)$arrQuery['value'];
            }
        }

        $arrQuery['value'] = self::prepareValueForQuery($arrQuery['value']);

        return $arrQuery;
    }

    public static function returnOnlyExistedItems($arrItems, $arrExistedFields, $blnKeysOnly = false): array
    {

        $arrReturn = [];
        $arrExistedValues = $blnKeysOnly ? array_keys($arrExistedFields) : $arrExistedFields;

        if (!empty($arrItems) && is_array($arrItems)) {
            foreach ($arrItems as $varValue) {
                if (!$varValue || !is_string($varValue)) continue;
                if (!in_array($varValue, $arrExistedValues)) continue;
                $arrReturn[] = $varValue;
            }
        }

        return $arrReturn;
    }

    public static function getRoutingParameter($strRoutingParameter, $blnEmptyArray = false)
    {

        $arrReturn = [];
        $arrRoutingFragments = explode('/', $strRoutingParameter);
        if (!empty($arrRoutingFragments) && is_array($arrRoutingFragments)) {
            foreach ($arrRoutingFragments as $strRoutingFragment) {
                if (!$strRoutingFragment) continue;
                preg_match_all('/{(.*?)}/', $strRoutingFragment, $arrMatches);
                $strParamName = implode('', $arrMatches[1]);
                if ($strParamName) {
                    $arrReturn[$strParamName] = $blnEmptyArray ? [] : $strParamName;
                }
            }
        }

        return $arrReturn;
    }

    public static function parseMultipleOptions($varValue)
    {

        if (is_string($varValue)) {
            $varValue = explode(',', $varValue);
        }

        return $varValue;
    }

    public static function isCoreTable($strTable): bool
    {
        return is_string($strTable) && substr($strTable, 0, 3) == 'tl_';
    }

    public static function getColumnsFromCoreTable($strTable, $blnFullContext = false): array
    {

        $arrReturn = [];

        System::loadLanguageFile($strTable);
        Controller::loadDataContainer($strTable);

        $arrFields = $GLOBALS['TL_DCA'][$strTable]['fields'];

        if (!empty($arrFields) && is_array($arrFields)) {
            foreach ($arrFields as $strFieldname => $arrField) {
                if (!isset($arrField['sql'])) continue;
                $varContext = $arrField;
                if (!$blnFullContext) {
                    $strTitle = $strFieldname;
                    if (is_array($arrField['label'])) {
                        $varContext = $arrField['label'][0] ?: $strTitle;
                    }
                }
                $arrReturn[$strFieldname] = $varContext;
            }
        }

        return $arrReturn;
    }

    public static function parseCatalogValues($arrData, $arrFields = [], $blnJustStrings = false, $strJoinedTable = '')
    {

        if (!empty($arrData) && is_array($arrData)) {

            foreach ($arrData as $strFieldname => $strOriginValue) {

                $strField = $strFieldname;

                if (Toolkit::isEmpty($strOriginValue)) continue;

                if ($strJoinedTable) {
                    $strField = $strJoinedTable . ucfirst($strFieldname);
                }

                $arrField = $arrFields[$strField] ?? null;

                if (is_null($arrField)) continue;
                if (!isset($arrField['type']) || !$arrField['type']) continue;

                $varValue = static::parseCatalogValue($strOriginValue, $arrField, $arrData);

                if ($blnJustStrings) {
                    if (is_array($varValue) && $arrField['type'] != 'upload') {
                        $varValue = implode(', ', $varValue);
                    }
                    if (is_array($varValue) && $arrField['type'] == 'upload') {
                        if (isset($varValue[0])) {
                            $varValue = $varValue[0]['singleSRC'];
                        } else {
                            $varValue = $varValue['singleSRC'];
                        }
                    }
                }

                $arrData[$strFieldname] = Toolkit::isEmpty($varValue) ? $strOriginValue : $varValue;
            }
        }

        return $arrData;
    }

    public static function parseCatalogValue($strValue, $arrField, $arrData = []): string
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));

        switch ($arrField['type']) {
            case 'upload':
                $blnFrontend = !$blnIsBackend || $arrField['useArrayFormat'];
                if ($blnFrontend) {
                    $strValue = Upload::parseValue($strValue, $arrField, $arrData);
                    if (is_array($strValue) && $arrField['fileType'] == 'gallery') {
                        if ($strValue['preview']) $arrData[$arrField['fieldname'] . 'Preview'] = $strValue['preview'];
                        $strValue = $strValue['gallery'];
                    }
                } else {
                    $strValue = Upload::parseThumbnails($strValue, $arrField, $arrData);
                }
                break;
            case 'select':
                $strValue = Select::parseValue($strValue, $arrField, $arrData);
                break;
            case 'checkbox':
                $strValue = Checkbox::parseValue($strValue, $arrField, $arrData);
                break;
            case 'radio':
                $strValue = Radio::parseValue($strValue, $arrField, $arrData);
                break;
            case 'date':
                $strValue = DateInput::parseValue($strValue, $arrField, $arrData);
                break;
            case 'number':
                $strValue = Number::parseValue($strValue, $arrField, $arrData);
                break;
            case 'textarea':
                $strValue = Textarea::parseValue($strValue, $arrField, $arrData);
                break;
            case 'dbColumn':
                $strValue = DbColumn::parseValue($strValue, $arrField, $arrData);
                break;
        }

        return $strValue;
    }

    public static function createPanelLayout($arrPanelLayout)
    {

        $arrPanelLayout = is_array($arrPanelLayout) ? $arrPanelLayout : [];
        $strPanelLayout = implode(',', $arrPanelLayout);

        if (strpos($strPanelLayout, 'filter') !== false) {
            $strPanelLayout = preg_replace('/,/', ';', $strPanelLayout, 1);
        }

        return $strPanelLayout;
    }

    public static function getLabelValue($varValue, $strFallback)
    {
        if (Toolkit::isEmpty($varValue)) return $strFallback;
        if (is_array($varValue)) return $varValue[0] ?: '';
        if (is_string($varValue)) return $varValue ?: '';

        return $strFallback;
    }

    public static function getBackendModuleTablesByDoAttribute($strDo): array
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));
        if (is_array($GLOBALS['BE_MOD']) && $blnIsBackend && !Toolkit::isEmpty($strDo)) {
            foreach ($GLOBALS['BE_MOD'] as $arrModules) {
                foreach ($arrModules as $strModulename => $arrModule) {
                    if ($strModulename == $strDo) {
                        return is_array($arrModule['tables']) ? $arrModule['tables'] : [];
                    }
                }
            }
        }

        return [];
    }

    public static function strictMode(): bool
    {
        if (!isset($GLOBALS['TL_CONFIG']['ctlg_strict_mode']) || !is_bool($GLOBALS['TL_CONFIG']['ctlg_strict_mode'])) return true;
        return $GLOBALS['TL_CONFIG']['ctlg_strict_mode'] ? true : false;
    }

    public static function ignoreRomanization(): bool
    {
        if (!isset($GLOBALS['TL_CONFIG']['ctlg_ignore_romanization']) || !is_bool($GLOBALS['TL_CONFIG']['ctlg_ignore_romanization'])) return false;
        return $GLOBALS['TL_CONFIG']['ctlg_ignore_romanization'] ? true : false;
    }

    public static function dump($varDump)
    {
        echo '<pre>' . var_export($varDump, true) . '</pre>';
    }

    public static function flatter($arrValues, &$arrFlattedArray = []): array
    {

        foreach ($arrValues as $strKey => $varValue) {

            if (is_array($varValue)) {
                static::flatter($varValue, $arrFlattedArray);
                continue;
            }

            $arrFlattedArray[] = [
                'key' => $strKey,
                'value' => $varValue
            ];
        }

        return $arrFlattedArray;
    }

    public static function hasDynAlias(): bool
    {
        return is_bool(Config::get('dynAlias')) && Config::get('dynAlias') === true;
    }

    public static function setTokens($arrRow, $strName, &$arrTokens = [])
    {

        foreach ($arrRow as $strFieldname => $strValue) {
            $arrTokens[$strName . $strFieldname] = $strValue;
        }

        return $arrTokens;
    }

    public static function parsePseudoInserttag($strValue = '', $arrData = []): string
    {

        if (!empty($strValue) && is_string($strValue) && strpos($strValue, '{{') !== false) {
            $arrTags = preg_split('/{{(([^{}]*|(?R))*)}}/', $strValue, -1, PREG_SPLIT_DELIM_CAPTURE);
            $strTag = implode('', $arrTags);
            if ($strTag && isset($arrData) && is_array($arrData)) {
                return Toolkit::isEmpty($arrData[$strTag]) ? '' : $arrData[$strTag];
            }
        }

        return $strValue;
    }

    public static function flatterWithoutKeyValue($arrValues, &$arrReturn, $strPrefix = ''): void
    {

        if (is_array($arrValues) && !empty($arrValues)) {
            foreach ($arrValues as $strField => $varValue) {
                if (is_array($varValue)) {
                    static::flatterWithoutKeyValue($varValue, $arrReturn, $strField);
                } else {
                    $arrReturn[($strPrefix ? $strPrefix . '_' : '') . $strField] = $varValue;
                }
            }
        }
    }

    public static function getIcon($strType)
    {
        return $GLOBALS['CM_ICON_SET'][$strType] ?? '';
    }

    public static function slug($strValue, $arrOptions = [])
    {

        $strValue = StringUtil::generateAlias($strValue);
        $strDelimiter = '-';

        if (isset($arrOptions['delimiter']) && $arrOptions['delimiter']) {
            $strDelimiter = $arrOptions['delimiter'];
        }

        $objSlugGenerator = new SlugGenerator((new SlugOptions)
            ->setValidChars('a-zA-Z0-9')
            ->setLocale('de')
            ->setDelimiter($strDelimiter)
        );

        return $objSlugGenerator->generate($strValue);
    }

    public static function getEntityUrl($strModuleId, $strEntityId): string
    {

        $objDatabase = Database::getInstance();
        $objModule = $objDatabase->prepare('SELECT * FROM tl_module WHERE id = ?')->limit(1)->execute($strModuleId);

        if (!$objModule->catalogTablename || !$objModule->catalogUseMasterPage || !$objModule->catalogMasterPage) {
            return '';
        }

        $objEntity = $objDatabase->prepare('SELECT * FROM ' . $objModule->catalogTablename . ' WHERE id = ?')->limit(1)->execute($strEntityId);

        if (!$objEntity->alias) {
            return '';
        }

        $objPage = PageModel::findByPk($objModule->catalogMasterPage);

        if ($objPage == null) {
            return '';
        }

        if ($objPage->catalogUseRouting && $objPage->catalogRouting) {
            $objEntity->alias = static::generateAliasWithRouting($objEntity->alias, static::getRoutingParameter($objPage->catalogRouting), $objEntity->row());
        }

        return $objPage->getFrontendUrl(($objEntity->alias ? '/' . $objEntity->alias : ''));
    }

    public static function generateAliasWithRouting($strAlias, $arrRoutingParameter, $arrCatalog = [])
    {

        $strAliasWithFragments = '';

        if (!in_array('auto_item', $arrRoutingParameter)) {
            return $strAlias;
        }

        $intIndex = 0;
        $intTotal = count($arrRoutingParameter) - 1;
        $blnAutoItem = in_array('auto_item', $arrRoutingParameter);

        foreach ($arrRoutingParameter as $strParameter) {

            ++$intIndex;

            if ($strParameter === 'auto_item') {
                if (Toolkit::isEmpty($strAlias)) continue;
                $strAliasWithFragments .= '/' . $strAlias;
                continue;
            }

            if (!Toolkit::isEmpty($arrCatalog[$strParameter])) {
                $strAliasWithFragments .= $arrCatalog[$strParameter] . ($intIndex != $intTotal && $blnAutoItem ? '/' : '');
            } else {
                $strAliasWithFragments .= ' ' . ($intIndex != $intTotal && $blnAutoItem ? '/' : '');
            }
        }

        if ($strAliasWithFragments) {
            $strAlias = $strAliasWithFragments;
        }

        return $strAlias;
    }

    public static function parseSimpleTokens($strSimpleTokens, $arrValues): string
    {
        $objSimpleTokenParser = System::getContainer()->get('contao.string.simple_token_parser');
        return $objSimpleTokenParser->parse($strSimpleTokens, $arrValues);
    }

    public static function replaceInsertTags($strTemplate)
    {
        return System::getContainer()->get('contao.insert_tag.parser')->replaceInline($strTemplate);
    }
}