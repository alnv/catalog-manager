<?php

namespace Alnv\CatalogManagerBundle\Classes;

use Alnv\CatalogManagerBundle\CatalogDatabaseBuilder;
use Alnv\CatalogManagerBundle\CatalogFieldBuilder;
use Alnv\CatalogManagerBundle\DcModifier;
use Alnv\CatalogManagerBundle\DcPermission;
use Alnv\CatalogManagerBundle\I18nCatalogTranslator;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\ArrayUtil;
use Contao\Backend;
use Contao\DataContainer;
use Contao\Input;
use Contao\System;


class tl_catalog_fields extends Backend
{
    protected array $arrTypes = [];

    protected string $strTable = '';

    public function __construct()
    {

        parent::__construct();

        $strId = Input::get('id') ?: '';
        if (Toolkit::isEmpty($strId)) return;

        $strQuery = 'SELECT * FROM tl_catalog WHERE id = ( SELECT pid FROM tl_catalog_fields WHERE id=? LIMIT 1 )';

        if (Input::get('act') == 'editAll') $strQuery = 'SELECT * FROM tl_catalog WHERE id=?';

        $objCatalog = $this->Database->prepare($strQuery)->limit(1)->execute($strId);
        $this->strTable = $objCatalog->tablename ?: '';

        $this->arrTypes = $GLOBALS['TL_CATALOG_MANAGER']['FIELD_TYPES'];
    }


    public function generateFieldname($varValue, DataContainer $dc)
    {

        if (Toolkit::isEmpty($varValue) && !Toolkit::isEmpty($dc->activeRecord->title)) {

            $varValue = Toolkit::slug($dc->activeRecord->title, ['delimiter' => '_']);
            $objField = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE fieldname=? AND id !=?')->limit(1)->execute($varValue, $dc->activeRecord->id);

            if ($objField->numRows) $varValue .= '_' . $objField->id;

            return $varValue;
        }

        return $varValue;
    }

    public function checkPermission()
    {
        $objDcPermission = new DcPermission();
        $objDcPermission->checkPermissionByParent('tl_catalog_fields', 'tl_catalog', 'catalog', 'catalogp');
    }

    public function changeGlobals()
    {

        if (Input::get('do') && Input::get('do') == 'catalog-manager') {
            $GLOBALS['TL_LANG']['MSC']['ow_key'] = &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['ow_key'];
            $GLOBALS['TL_LANG']['MSC']['ow_value'] = &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['ow_value'];
        }
    }

    public function validatePath($strValue)
    {

        if (Toolkit::isEmpty($strValue)) return '';

        if (substr($strValue, 0, 1) == '/') {
            $strValue = substr($strValue, 1, strlen($strValue));
        }

        if (!is_dir($strValue)) {
            throw new \Exception('directory do not exist.');
        }

        return $strValue;
    }

    public function setOrderField(DataContainer $dc)
    {

        if (Input::get('act') != 'edit') return;

        $objField = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE id = ?')->limit(1)->execute($dc->id);

        if ($objField === null) return;

        if ($objField->numRows) {

            $strOrder = $objField->sortBy;

            if ($strOrder != 'custom') {

                unset($GLOBALS['TL_DCA']['tl_catalog_fields']['fields']['orderField']);

                $GLOBALS['TL_DCA']['tl_catalog_fields']['subpalettes']['fileType_files'] = str_replace('orderField,', '', $GLOBALS['TL_DCA']['tl_catalog_fields']['subpalettes']['fileType_files']);
                $GLOBALS['TL_DCA']['tl_catalog_fields']['subpalettes']['fileType_gallery'] = str_replace('orderField,', '', $GLOBALS['TL_DCA']['tl_catalog_fields']['subpalettes']['fileType_gallery']);
            }
        }
    }

    public function createFieldOnSubmit(DataContainer $dc)
    {

        $strCatalogID = $dc->activeRecord->pid;
        $strFieldname = $dc->activeRecord->fieldname;

        if (!$strFieldname || !$strCatalogID) return null;

        $arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE `id` = ?')->limit(1)->execute($strCatalogID)->row();
        $strTablename = $arrCatalog['tablename'];


        $objDatabaseBuilder = new CatalogDatabaseBuilder();
        $objDatabaseBuilder->initialize($strTablename, $arrCatalog);
        $objDatabaseBuilder->setColumn((array) $dc->activeRecord);

        if (in_array($strFieldname, Toolkit::columnsBlacklist())) {
            throw new \Exception(sprintf('fieldname "%s" is not allowed.', $strFieldname));
        }

        if ($dc->activeRecord->tstamp) {
            $objDatabaseBuilder->columnCheck();
            return null;
        }

        if (!$this->Database->fieldExists($strFieldname, $strTablename)) {
            $objDatabaseBuilder->createColumn();
        }
    }

    public function checkUniqueValue($varValue, DataContainer $dc)
    {

        $objFieldname = $this->Database->prepare('SELECT pid, id FROM tl_catalog_fields WHERE fieldname = ? AND id != ?')->limit(1)->execute($varValue, $dc->activeRecord->id);

        if ($objFieldname->numRows && $objFieldname->pid == $dc->activeRecord->pid) {
            throw new \Exception('this fieldname already exist.');
        }

        return $varValue;
    }

    public function getFilesTypes(): array
    {
        return ['image', 'gallery', 'file', 'files'];
    }

    public function getTextFieldsByParentID(): array
    {

        $objI18nCatalogTranslator = new I18nCatalogTranslator();
        $objI18nCatalogTranslator->initialize();

        $arrReturn = [
            'title' => $objI18nCatalogTranslator->get('field', 'title', ['titleOnly' => true])
        ];

        $objCatalogFields = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT pid FROM tl_catalog_fields WHERE id = ? )')->execute(Input::get('id'));

        while ($objCatalogFields->next()) {
            if ($objCatalogFields->type !== 'text') {
                continue;
            }
            $arrReturn[$objCatalogFields->fieldname] = $objI18nCatalogTranslator->get('field', $objCatalogFields->fieldname, ['title' => $objCatalogFields->title, 'description' => $objCatalogFields->description, 'titleOnly' => true]);
        }

        return $arrReturn;
    }

    public function getCatalogFieldsByParentID(): array
    {

        $arrReturn = [];
        $objCatalogFields = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT pid FROM tl_catalog_fields WHERE id = ? )')->execute(Input::get('id'));

        $objI18nCatalogTranslator = new I18nCatalogTranslator();
        $objI18nCatalogTranslator->initialize();

        while ($objCatalogFields->next()) {
            if (!$objCatalogFields->fieldname) continue;
            $arrReturn[$objCatalogFields->fieldname] = $objI18nCatalogTranslator->get('field', $objCatalogFields->fieldname, ['title' => $objCatalogFields->title, 'description' => $objCatalogFields->description, 'titleOnly' => true]);
        }

        return $arrReturn;
    }

    public function renameFieldname($varValue, DataContainer $dc)
    {

        $strFieldname = $dc->activeRecord->fieldname;

        if (Toolkit::isEmpty($varValue) || Toolkit::isEmpty($strFieldname) || $strFieldname == $varValue) {
            return $varValue;
        }

        $strCatalogID = $dc->activeRecord->pid;
        $arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE `id` = ?')->limit(1)->execute($strCatalogID)->row();

        if ($this->Database->fieldExists($varValue, $arrCatalog['tablename'])) {
            throw new \Exception(sprintf('fieldname "%s" already exist', $varValue));
        }

        $objDatabaseBuilder = new CatalogDatabaseBuilder();
        $objDatabaseBuilder->initialize($arrCatalog['tablename'], $arrCatalog);
        $objDatabaseBuilder->setColumn($dc->activeRecord->row());

        if ($this->Database->fieldExists($strFieldname, $arrCatalog['tablename'])) {
            $objDatabaseBuilder->renameColumn($varValue);
        }

        return $varValue;
    }

    public function checkFieldname($varValue)
    {

        $varValue = Toolkit::parseConformSQLValue($varValue);
        $strValidname = Toolkit::slug($varValue, ['delimiter' => '_']);
        if ($strValidname != $varValue && Toolkit::strictMode()) throw new \Exception(sprintf('invalid fieldname. Please try with "%s"', $strValidname));

        return $varValue;
    }

    public function checkBlacklist($varValue)
    {

        if ($varValue && in_array($varValue, Toolkit::columnsBlacklist())) {
            throw new \Exception(sprintf('fieldname "%s" is forbidden.', $varValue));
        }

        return $varValue;
    }

    public function dropFieldOnDelete(DataContainer $dc)
    {

        $strCatalogID = $dc->activeRecord->pid;
        $arrCatalog = $this->Database->prepare('SELECT * FROM tl_catalog WHERE `id` = ?')->limit(1)->execute($strCatalogID)->row();

        $objDatabaseBuilder = new CatalogDatabaseBuilder();
        $objDatabaseBuilder->initialize($arrCatalog['tablename'], $arrCatalog);

        $objDatabaseBuilder->setColumn($dc->activeRecord->row());
        $objDatabaseBuilder->dropColumn();
    }

    public function getTables()
    {
        return $this->Database->listTables(null);
    }

    public function getColumnsByDbTable(DataContainer $dc)
    {

        $arrReturn = [];
        $strTablename = $dc->activeRecord->dbTable;

        if ($strTablename && $this->Database->tableExists($strTablename)) {

            $objCatalogFieldBuilder = new CatalogFieldBuilder();
            $objCatalogFieldBuilder->initialize($strTablename);
            $arrFields = $objCatalogFieldBuilder->getCatalogFields(true, null);

            foreach ($arrFields as $strFieldname => $arrField) {
                if (!$this->Database->fieldExists($strFieldname, $strTablename)) continue;
                $arrReturn[$strFieldname] = Toolkit::getLabelValue($arrField['_dcFormat']['label'], $strFieldname);
            }
        }

        return $arrReturn;
    }

    public function getFieldTypes(): array
    {
        return array_keys($this->arrTypes);
    }

    public function getIndexes(): array
    {
        return ['index', 'unique'];
    }

    public function getRGXPTypes(DataContainer $dc)
    {

        if ($dc->activeRecord->type && $dc->activeRecord->type == 'number') {
            return Toolkit::$arrDigitRgxp;
        }

        if ($dc->activeRecord->type && $dc->activeRecord->type == 'date') {
            return Toolkit::$arrDateRgxp;
        }

        return ['url', 'time', 'date', 'alias', 'alnum', 'alpha', 'datim', 'digit', 'email', 'extnd', 'phone', 'prcnt', 'locale', 'emails', 'natural', 'friendly', 'language', 'folderalias'];
    }


    public function getRichTextEditor(): array
    {

        $arrReturn = ['tinyMCE', 'tinyFlash'];

        $arrCustomTinyMce = $this->getTemplateGroup('be_tiny');
        if (!empty($arrCustomTinyMce) && is_array($arrCustomTinyMce)) {
            foreach ($arrCustomTinyMce as $strTinyMCE => $strTinyMCEName) {

                $strTinyMCE = $strTinyMCE ? str_replace('be_', '', $strTinyMCE) : '';

                if (!$strTinyMCE) continue;

                if (!in_array($strTinyMCE, $arrReturn)) {
                    $arrReturn[] = $strTinyMCE;
                }
            }
        }

        return $arrReturn;
    }

    public function getTLClasses(): array
    {
        return ['clr', 'w50', 'long', 'm12'];
    }

    public function getFieldFlags(): array
    {
        return ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12'];
    }

    public function getSQLStatements(DataContainer $dc)
    {

        $arrSQLStatements = Toolkit::$arrSqlTypes;

        if ($dc->activeRecord->type == 'upload') {

            if ($dc->activeRecord->useFineUploader) {

                return [
                    'blob' => $arrSQLStatements['blob']
                ];
            }

            return [
                'blob' => $arrSQLStatements['blob'],
                'binary' => $arrSQLStatements['binary']
            ];
        }

        if ($dc->activeRecord->type == 'textarea' || $dc->activeRecord->multiple) {

            $arrText = $arrSQLStatements['text'];

            unset($arrSQLStatements['i5']);
            unset($arrSQLStatements['c1']);
            unset($arrSQLStatements['i10']);
            unset($arrSQLStatements['c16']);
            unset($arrSQLStatements['c32']);
            unset($arrSQLStatements['c64']);
            unset($arrSQLStatements['c128']);
            unset($arrSQLStatements['c256']);
            unset($arrSQLStatements['text']);

            ArrayUtil::arrayInsert($arrSQLStatements, 0, ['text' => $arrText]);
        }

        if ($dc->activeRecord->type == 'date' || $dc->activeRecord->type == 'number') {

            $arrI10 = $arrSQLStatements['i10'];
            unset($arrSQLStatements['i10']);
            ArrayUtil::arrayInsert($arrSQLStatements, 0, ['i10' => $arrI10]);
        }

        return $arrSQLStatements;
    }

    public function getCatalogFieldList($arrRow)
    {
        return $arrRow['title'] . ($arrRow['fieldname'] ? ' ' . '<span style="color:#ccc;">[' . $arrRow['fieldname'] . ']</span>' : '');
    }

    public function getMapTemplates()
    {
        return $this->getTemplateGroup('ctlg_field_');
    }

    public function getTaxonomyTable(DataContainer $dc)
    {
        return $dc->activeRecord->dbTable ? $dc->activeRecord->dbTable : '';
    }

    public function getTaxonomyFields(DataContainer $dc, $strTablename)
    {

        $arrReturn = [];
        $arrForbiddenTypes = ['upload', 'textarea'];

        if (!$strTablename) return $arrReturn;

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize($strTablename);
        $arrFields = $objCatalogFieldBuilder->getCatalogFields(true, null);

        foreach ($arrFields as $strFieldname => $arrField) {

            if (!$this->Database->fieldExists($strFieldname, $strTablename)) continue;
            if (in_array($arrField['type'], Toolkit::excludeFromDc())) continue;
            if (in_array($arrField['type'], $arrForbiddenTypes)) continue;

            $arrReturn[$strFieldname] = $arrField['_dcFormat'];
        }

        return $arrReturn;
    }

    public function parseTitle($varValue, DataContainer $dc)
    {

        if (!Toolkit::isEmpty($varValue) && in_array($dc->activeRecord->type, ['fieldsetStart', 'fieldsetStop'])) {
            $varValue = Toolkit::slug($varValue, ['delimiter' => '_']);
        }

        return $varValue;
    }

    public function getImageTemplates()
    {
        return $this->getTemplateGroup('ce_image');
    }

    public function getGalleryTemplates()
    {
        return $this->getTemplateGroup('gallery_default');
    }

    public function getFileTemplates()
    {
        return $this->getTemplateGroup('ce_download');
    }

    public function getFilesTemplates()
    {
        return $this->getTemplateGroup('ce_downloads');
    }

    public function getOrderFields(DataContainer $dc)
    {

        $arrReturn = [];

        if ($dc->activeRecord) {

            $objFields = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE pid = ? AND statement = ?')->execute($dc->activeRecord->pid, 'blob');
            $objI18nCatalogTranslator = new I18nCatalogTranslator();
            $objI18nCatalogTranslator->initialize();

            while ($objFields->next()) {
                if ($objFields->fieldname && $objFields->type == 'dbColumn') {
                    $arrReturn[$objFields->fieldname] = $objI18nCatalogTranslator->get('field', $objFields->fieldname, ['title' => $objFields->title, 'description' => $objFields->description, 'titleOnly' => true]);
                }
            }
        }

        return $arrReturn;
    }

    public function addPalettePicker()
    {

        if (!$this->strTable) return null;
        if (!Toolkit::isCoreTable($this->strTable)) return null;

        foreach ($this->arrTypes as $strType => $arrType) {
            if (!empty($arrType) && is_array($arrType)) {
                $GLOBALS['TL_DCA']['tl_catalog_fields']['palettes'][$strType] = str_replace($arrType['dcPicker'], $arrType['dcPicker'] . '{palettes_legend},' . $arrType['dcType'] . ';', $GLOBALS['TL_DCA']['tl_catalog_fields']['palettes'][$strType]);
            }
        }
    }

    public function getDcPalettes()
    {

        $objDcModifier = new DcModifier();
        $objDcModifier->initialize($this->strTable);

        return $objDcModifier->getPalettes();
    }

    public function getDcLegends($strCurrentPalette)
    {

        $objDcModifier = new DcModifier();
        $objDcModifier->initialize($this->strTable);

        return $objDcModifier->getLegends($strCurrentPalette);
    }

    public function getDcFields($strCurrentPalette): array
    {

        $objDcModifier = new DcModifier();
        $objDcModifier->initialize($this->strTable);

        return $objDcModifier->getFields($strCurrentPalette);
    }


    public function getFieldTemplates(DataContainer $dc): array
    {

        if (!$dc->activeRecord->type) return [];

        $strType = Toolkit::$arrFormTemplates[$dc->activeRecord->type];

        if ($dc->activeRecord->type == 'upload' && $dc->activeRecord->useFineUploader) {
            $strType = 'ctlg_form_fine_uploader';
        }

        return $this->getTemplateGroup($strType);
    }


    public function getImagesSizes(): array
    {

        if ($this->Database->tableExists('tl_image_size')) {
            return System::getContainer()->get('contao.image.sizes')->getAllOptions();
        }

        return [];
    }
}