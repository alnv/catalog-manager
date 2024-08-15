<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Database;
use Contao\System;
use Contao\Controller;
use Contao\DataContainer;
use Alnv\CatalogManagerBundle\Fields\Text;
use Alnv\CatalogManagerBundle\Fields\DateInput;
use Alnv\CatalogManagerBundle\Fields\Textarea;
use Alnv\CatalogManagerBundle\Fields\Checkbox;
use Alnv\CatalogManagerBundle\Fields\Hidden;
use Alnv\CatalogManagerBundle\Fields\Radio;
use Alnv\CatalogManagerBundle\Fields\Select;
use Alnv\CatalogManagerBundle\Fields\MessageInput;
use Alnv\CatalogManagerBundle\Fields\Upload;
use Alnv\CatalogManagerBundle\Fields\Number;
use Alnv\CatalogManagerBundle\Fields\DbColumn;
use Symfony\Component\HttpFoundation\Request;

class CatalogFieldBuilder extends CatalogController
{

    protected string $strTable = '';

    protected array $arrCatalog = [];

    protected bool $blnActive = true;

    protected array $arrCatalogFields = [];

    public function __construct()
    {

        parent::__construct();

        $this->import(Database::class, 'Database');
        $this->import(I18nCatalogTranslator::class, 'I18nCatalogTranslator');
    }

    public function initialize($strTablename, $blnActive = true)
    {

        $this->blnActive = $blnActive;
        $this->strTable = $strTablename;
        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));

        if ($blnIsBackend && !Toolkit::isEmpty(($GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTablename] ?? ''))) {
            $this->arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][$strTablename];
            return true;
        } else {

            $objCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE `tablename` = ?')->limit(1)->execute($strTablename);
            if ($objCatalog !== null) {
                if ($objCatalog->numRows) {
                    $this->arrCatalog = Toolkit::parseCatalog($objCatalog->row());
                    return true;
                }
            }
        }

        if (Toolkit::isCoreTable($strTablename)) {

            $objDcExtractor = new CatalogDcExtractor();
            $objDcExtractor->initialize($strTablename);

            $this->arrCatalog = Toolkit::parseCatalog($objDcExtractor->convertDataContainerToCatalog());

            return true;
        }

        return false;
    }

    public function getCatalog()
    {
        return $this->arrCatalog;
    }

    public function getCatalogFields($blnDcFormat = true, $objModule = null, $blnExcludeDefaults = false, $blnVisible = true)
    {

        $arrFields = [];
        $blnIsCoreTable = Toolkit::isCoreTable($this->strTable);

        if ($blnIsCoreTable) {

            $blnDcFormat = true;
            $arrFields = $this->getCoreFields($blnDcFormat);
        }

        if (!$blnExcludeDefaults) {

            foreach ($this->getDefaultCatalogFields() as $strFieldname => $arrField) {

                $arrFields[$strFieldname] = $arrField;
            }
        }

        $objCatalogFields = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE `pid` = ( SELECT id FROM tl_catalog WHERE `tablename` = ? LIMIT 1 )' . ($blnVisible ? ' AND invisible != "1" ' : '') . 'ORDER BY `sorting`')->execute($this->strTable);

        if ($objCatalogFields !== null) {

            if ($objCatalogFields->numRows) {

                while ($objCatalogFields->next()) {

                    $arrField = $objCatalogFields->row();

                    if ($objCatalogFields->fieldname && in_array($objCatalogFields->fieldname, Toolkit::customizeAbleFields())) {

                        $arrOrigin = $arrFields[$objCatalogFields->fieldname] ?? null;

                        if (is_null($arrOrigin)) continue;

                        unset($arrFields[$objCatalogFields->fieldname]);
                    }

                    $strFieldname = $objCatalogFields->fieldname ?: ('f' . $objCatalogFields->id);

                    if (!$strFieldname) {
                        continue;
                    }

                    $arrFields[$strFieldname] = $arrField;
                }
            }
        }

        $this->arrCatalogFields = $this->parseFieldsForDcFormat($arrFields, $blnDcFormat, $objModule, $blnIsCoreTable);

        return $this->arrCatalogFields;
    }

    public function getDcFormatOnly()
    {

        $arrReturn = [];

        foreach ($this->arrCatalogFields as $strFieldname => $arrField) {

            if (!empty($arrField['_dcFormat']) && is_array($arrField['_dcFormat'])) {

                $arrReturn[$strFieldname] = $arrField['_dcFormat'];
            }
        }

        return $arrReturn;
    }

    public function parseFieldsForDcFormat($arrFields, $blnDcFormat, $objModule = null, $blnCoreTable = false)
    {

        $arrReturn = [];

        foreach ($arrFields as $strFieldname => $arrField) {

            if (!isset($arrField['_dcFormat'])) $arrField['_dcFormat'] = null;
            if ($blnDcFormat && Toolkit::isDcConformField($arrField) && !$arrField['_dcFormat']) $arrField['_dcFormat'] = $this->setDcFormatAttributes($arrField, $objModule);
            if ($arrField == null) continue;

            $arrReturn[$strFieldname] = $this->prepareDefaultFields($arrField, $strFieldname, $blnCoreTable);
        }

        return $arrReturn;
    }

    public function setDcFormatAttributes($arrField, $objModule = null)
    {

        $strTlClass = $arrField['tl_class'] ?? '';
        $strCSSBackendClasses = Toolkit::deserializeAndImplode($strTlClass, ' ');

        if (Toolkit::isEmpty($strCSSBackendClasses)) $strCSSBackendClasses = 'clr';

        $arrDcField = [
            'label' => $this->I18nCatalogTranslator->get('field', $arrField['fieldname'], ['table' => $this->strTable, 'title' => ($arrField['label'] ?? ''), 'description' => ($arrField['description'] ?? '')]),
            'inputType' => Toolkit::setDcConformInputType($arrField['type']),
            'eval' => [
                'tl_class' => $strCSSBackendClasses,
                'unique' => Toolkit::getBooleanByValue($arrField['isUnique'] ?? ''),
                'nospace' => Toolkit::getBooleanByValue($arrField['nospace'] ?? ''),
                'mandatory' => Toolkit::getBooleanByValue($arrField['mandatory'] ?? ''),
                'doNotCopy' => Toolkit::getBooleanByValue($arrField['doNotCopy'] ?? ''),
                'allowHtml' => Toolkit::getBooleanByValue($arrField['allowHtml'] ?? ''),
                'doNotSaveEmpty' => Toolkit::getBooleanByValue($arrField['doNotSaveEmpty'] ?? ''),
                'spaceToUnderscore' => Toolkit::getBooleanByValue($arrField['spaceToUnderscore'] ?? ''),
            ],
            'sorting' => Toolkit::getBooleanByValue($arrField['sort'] ?? ''),
            'search' => Toolkit::getBooleanByValue($arrField['search'] ?? ''),
            'filter' => Toolkit::getBooleanByValue($arrField['filter'] ?? ''),
            'exclude' => Toolkit::getBooleanByValue($arrField['exclude'] ?? ''),
            'sql' => Toolkit::getSqlDataType($arrField['statement'] ?? ''),
        ];

        if ($arrField['trailingSlash'] ?? '') {
            $arrDcField['eval']['trailingSlash'] = true;
        }

        if ($arrField['statement'] == 'iNotNull10') {
            $arrDcField['eval']['nullIfEmpty'] = true;
        }

        if ($arrField['type'] == 'date' && Toolkit::isEmpty($arrField['flag'])) {
            $arrField['flag'] = 6;
        }

        if (!Toolkit::isEmpty($arrField['flag'] ?? '')) {
            $arrDcField['flag'] = $arrField['flag'];
        }

        $arrDcField['_cssID'] = Toolkit::deserialize($arrField['cssID'] ?? '');
        $arrDcField['_placeholder'] = $arrField['placeholder'] ?? '';
        $arrDcField['_disableFEE'] = $arrField['disableFEE'] ?? '';
        $arrDcField['_fieldname'] = $arrField['fieldname'] ?? '';
        $arrDcField['_palette'] = $arrField['_palette'] ?? '';
        $arrDcField['_type'] = $arrField['type'] ?? '';

        if (isset($arrField['value']) && Toolkit::isDefined($arrField['value']) && is_string($arrField['value'])) {
            $strDefaultValue = Toolkit::replaceInsertTags($arrField['value']);
            if (Toolkit::isDefined($strDefaultValue)) {
                $arrDcField['default'] = $strDefaultValue;
            }
        }

        if (isset($arrField['useIndex']) && Toolkit::isDefined($arrField['useIndex'])) {
            $arrDcField['eval']['doNotCopy'] = true;
            if ($arrField['useIndex'] == 'unique') $arrDcField['eval']['unique'] = true;
        }

        if (isset($this->arrCatalog['tablename']) && $this->arrCatalog['tablename'] == 'tl_member') {
            $arrDcField['eval']['feEditable'] = true;
            $arrDcField['eval']['feViewable'] = true;
            $arrDcField['eval']['feGroup'] = 'personal';
        }

        switch ($arrField['type']) {

            case 'text':

                $arrDcField = Text::generate($arrDcField, $arrField, null, $this->blnActive);

                break;

            case 'date':

                $arrDcField = DateInput::generate($arrDcField, $arrField);

                break;

            case 'hidden':

                $arrDcField = Hidden::generate($arrDcField, $arrField);

                break;

            case 'number':

                $arrDcField = Number::generate($arrDcField, $arrField);

                break;

            case 'textarea':

                $arrDcField = Textarea::generate($arrDcField, $arrField);

                break;

            case 'select':

                $arrDcField = Select::generate($arrDcField, $arrField, $objModule, $this->blnActive);

                break;

            case 'radio':

                $arrDcField = Radio::generate($arrDcField, $arrField, $objModule, $this->blnActive);

                break;

            case 'checkbox':

                $arrDcField = Checkbox::generate($arrDcField, $arrField, $objModule, $this->blnActive);

                break;

            case 'upload':

                $arrDcField = Upload::generate($arrDcField, $arrField);

                break;

            case 'message':

                $arrDcField = MessageInput::generate($arrDcField, $arrField);

                break;

            case 'dbColumn':

                $arrDcField = DbColumn::generate($arrDcField, $arrField);

                break;
        }

        if (isset($GLOBALS['TL_HOOKS']['catalogManagerSetDcFormatAttributes']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerSetDcFormatAttributes'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerSetDcFormatAttributes'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $arrDcField = $this->{$arrCallback[0]}->{$arrCallback[1]}($arrDcField, $arrField, $this->strTable, $this->arrCatalog, $this);
                }
            }
        }

        return $arrDcField;
    }


    public function shouldBeUsedParentTable()
    {

        if (!isset($this->arrCatalog['pTable']) || !$this->arrCatalog['pTable']) {
            return false;
        }

        if (isset($this->arrCatalog['isBackendModule']) && $this->arrCatalog['isBackendModule']) {
            return false;
        }

        if (!in_array($this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes)) {
            return false;
        }

        return true;
    }


    protected function getDefaultCatalogFields($arrIncludeOnly = [])
    {

        $arrFields = [
            'id' => [
                'type' => '',
                'sort' => '1',
                'search' => '1',
                'invisible' => '',
                'fieldname' => 'id',
                'statement' => 'i10',
                'disableFEE' => true,
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['id'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['id'][0]
            ],
            'tstamp' => [
                'flag' => 6,
                'type' => '',
                'sort' => '1',
                'invisible' => '',
                '_isDate' => true,
                'statement' => 'i10',
                'disableFEE' => true,
                'fieldname' => 'tstamp',
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['tstamp'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['tstamp'][0]
            ],
            'pid' => [
                'type' => '',
                'invisible' => '',
                'disableFEE' => true,
                'statement' => 'i10',
                'fieldname' => 'pid',
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['pid'][0]
            ],
            'sorting' => [
                'type' => '',
                'invisible' => '',
                'statement' => 'i10',
                'disableFEE' => true,
                'fieldname' => 'sorting',
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['sorting'][0]
            ],
            'title' => [
                'sort' => '1',
                'search' => '1',
                'type' => 'text',
                'exclude' => '1',
                'invisible' => '',
                'maxlength' => '255',
                'statement' => 'c256',
                'fieldname' => 'title',
                '_palette' => 'general_legend',
                'tl_class' => serialize(['w50']),
                'cssID' => serialize(['', 'title']),
                'mandatory' => isset($this->arrCatalog['titleIsMandatory']) && $this->arrCatalog['titleIsMandatory'] ? '1' : '',
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['title'][0]
            ],
            'alias' => [
                'search' => '1',
                'unique' => '1',
                'type' => 'text',
                'exclude' => '1',
                'rgxp' => 'alias',
                'invisible' => '',
                'doNotCopy' => '1',
                'maxlength' => '128',
                'statement' => 'c128',
                'fieldname' => 'alias',
                '_palette' => 'general_legend',
                'tl_class' => serialize(['w50']),
                'cssID' => serialize(['', 'alias']),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['alias'][0]
            ],
            'invisible' => [
                'exclude' => '1',
                'multiple' => '',
                'invisible' => '',
                'statement' => 'c1',
                'placeholder' => '',
                'type' => 'checkbox',
                'fieldname' => 'invisible',
                '_palette' => 'invisible_legend',
                'cssID' => serialize(['', 'invisible']),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['invisible'][0],
            ],
            'start' => [
                'flag' => 6,
                'sort' => '1',
                'type' => 'date',
                'exclude' => '1',
                'rgxp' => 'datim',
                'invisible' => '',
                'datepicker' => '1',
                'statement' => 'c16',
                'fieldname' => 'start',
                '_palette' => 'invisible_legend',
                'cssID' => serialize(['', 'start']),
                'tl_class' => serialize(['w50 wizard']),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['start'][0]
            ],
            'stop' => [
                'flag' => 6,
                'sort' => '1',
                'type' => 'date',
                'exclude' => '1',
                'rgxp' => 'datim',
                'invisible' => '',
                'datepicker' => '1',
                'statement' => 'c16',
                'fieldname' => 'stop',
                '_palette' => 'invisible_legend',
                'cssID' => serialize(['', 'stop']),
                'tl_class' => serialize(['w50 wizard']),
                'title' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0],
                'placeholder' => &$GLOBALS['TL_LANG']['catalog_manager']['fields']['stop'][0]
            ]
        ];

        $this->arrCatalog['mode'] = $this->arrCatalog['mode'] ?? '';

        if ((!isset($this->arrCatalog['pTable']) || !$this->arrCatalog['pTable']) && !in_array($this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes)) {
            unset($arrFields['pid']);
        }

        if (isset($this->arrCatalog['operations']) && is_array($this->arrCatalog['operations'])) {
            if (!in_array('invisible', $this->arrCatalog['operations'])) {
                unset($arrFields['stop']);
                unset($arrFields['start']);
                unset($arrFields['invisible']);
            }

            if (!in_array($this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes) && !in_array('cut', $this->arrCatalog['operations'])) {
                unset($arrFields['sorting']);
            }
        }

        if (isset($this->arrCatalog['type']) && $this->arrCatalog['type'] == 'modifier') {
            unset($arrFields['id']);
            unset($arrFields['pid']);
            unset($arrFields['stop']);
            unset($arrFields['start']);
            unset($arrFields['tstamp']);
            unset($arrFields['sorting']);
            unset($arrFields['invisible']);
        }

        return $arrFields;
    }


    protected function prepareDefaultFields($arrField, $strFieldname, $blnCoreTable = false)
    {

        if ($blnCoreTable) {
            return $arrField;
        }

        switch ($strFieldname) {
            case 'tstamp' :
            case 'id' :
                $arrField['_dcFormat'] = [
                    'sorting' => $arrField['_dcFormat']['sorting'] ?? false,
                    'search' => $arrField['_dcFormat']['search'] ?? false,
                    'label' => $arrField['_dcFormat']['label'] ?? [],
                    'flag' => $arrField['_dcFormat']['flag'] ?? null,
                    'sql' => $arrField['_dcFormat']['sql'] ?? ''
                ];
                return $arrField;
            case 'pid' :
                if ($this->arrCatalog['pTable']) {
                    $arrField['_dcFormat'] = [
                        'label' => ($arrField['_dcFormat']['label']??[]),
                        'sql' => "int(10) unsigned NOT NULL default '0'",
                        'foreignKey' => sprintf('%s.id', $this->arrCatalog['pTable']),
                        'relation' => [
                            'type' => 'belongsTo',
                            'load' => 'eager'
                        ]
                    ];
                    return $arrField;
                }

                break;

            case 'sorting' :
                if (in_array($this->arrCatalog['mode'], Toolkit::$arrRequireSortingModes)) {
                    $arrField['_dcFormat'] = [
                        'label' => ($arrField['_dcFormat']['label']??[]),
                        'sql' => "int(10) unsigned NOT NULL default '0'"
                    ];
                    return $arrField;
                }

                break;

            case 'alias':
                $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));
                if (!$blnIsBackend) return $arrField;
                $arrField['_dcFormat']['save_callback'] = [function ($varValue, DataContainer $dc) {
                    $objDcCallbacks = new DcCallbacks();
                    return $objDcCallbacks->generateAlias($varValue, $dc, 'title', $this->strTable);
                }];

                return $arrField;
        }

        return $arrField;
    }


    protected function getCoreFields($blnDcFormat)
    {

        Controller::loadLanguageFile($this->strTable);
        Controller::loadDataContainer($this->strTable);

        $arrReturn = [];
        $arrFields = $GLOBALS['TL_DCA'][$this->strTable]['fields'];

        if (!empty($arrFields) && is_array($arrFields)) {

            foreach ($arrFields as $strFieldname => $arrField) {

                if (!isset($arrField['eval'])) $arrField['eval'] = [];

                $arrOptions = $arrField['options'] ?? [];
                $strType = Toolkit::setCatalogConformInputType($arrField);

                $arrReturn[$strFieldname] = [
                    '_core' => true,
                    'type' => $strType,
                    'fieldname' => $strFieldname,
                    '_palette' => 'general_legend',
                    'title' => $arrField['label'][0] ?? '',
                    'rgxp' => $arrField['eval']['rgxp'] ?? '',
                    'description' => $arrField['label'][1] ?? '',
                    'exclude' => (isset($arrField['exclude']) && $arrField['exclude'] ? '1' : ''),
                    'cssID' => serialize(['', $strFieldname]),
                    '_dcFormat' => $blnDcFormat ? $arrField : null,
                    'tl_class' => $arrField['eval']['tl_class'] ?? '',
                    'multiple' => (isset($arrField['eval']['multiple']) && $arrField['eval']['multiple'] ? '1' : ''),
                    'datepicker' => (isset($arrField['eval']['datepicker']) && $arrField['eval']['datepicker'] ? '1' : '')
                ];

                if (!Toolkit::isEmpty($arrField['foreignKey'] ?? '')) {
                    $arrForeignKeys = explode('.', $arrField['foreignKey']);
                    $arrReturn[$strFieldname]['optionsType'] = 'useForeignKey';
                    $arrReturn[$strFieldname]['dbTable'] = $arrForeignKeys[0] ?: '';
                    $arrReturn[$strFieldname]['dbTableValue'] = $arrForeignKeys[1] ?: '';
                }

                if (is_array($arrOptions) && !empty($arrOptions)) {

                    $arrKeyValue = Toolkit::flatter($arrOptions);
                    $arrReturn[$strFieldname]['optionsType'] = 'useOptions';
                    $arrReturn[$strFieldname]['options'] = serialize($arrKeyValue);
                }

                if ($strType == 'upload') {

                    $strFileType = $arrField['_fileType'] ?? '';
                    $strExtensions = $arrField['eval']['extensions'] ?? '';

                    if ($strExtensions && !$strFileType) {

                        $arrExtensions = explode(',', $strExtensions);

                        if (empty(array_intersect($arrExtensions, Toolkit::$arrFileExtensions))) {
                            $strFileType = (isset($arrField['eval']['multiple']) && $arrField['eval']['multiple'] ? 'gallery' : 'image');
                        } else {
                            $strFileType = (isset($arrField['eval']['multiple']) && $arrField['eval']['multiple'] ? 'files' : 'file');
                        }
                    }

                    $arrReturn[$strFieldname]['fileType'] = $strFileType;
                    $arrReturn[$strFieldname]['extensions'] = $strExtensions;
                    $arrReturn[$strFieldname]['filesOnly'] = ($arrField['eval']['filesOnly']??'') ? '1' : '';
                }
            }
        }

        return $arrReturn;
    }
}