<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Database;
use Contao\Environment;
use Contao\PageModel;

class CatalogFilter extends CatalogController
{

    public $strTable;

    public array $arrFields = [];

    public array $arrCatalog = [];

    public array $arrOptions = [];
    public array $arrDependencies = [];
    public array $arrActiveFields = [];

    private array $arrForbiddenFilterTypes = [
        'map',
        'upload',
        'message',
        'fieldsetStop',
        'fieldsetStart'
    ];

    public function __construct()
    {
        $this->import(Database::class, 'Database');
        $this->import(CatalogInput::class, 'CatalogInput');
        $this->import(CatalogFieldBuilder::class, 'CatalogFieldBuilder');

        parent::__construct();
    }

    public function initialize()
    {

        $this->setOptions();

        $this->CatalogFieldBuilder->initialize($this->strTable);

        $this->arrCatalog = $this->CatalogFieldBuilder->getCatalog();
        $arrFields = $this->CatalogFieldBuilder->getCatalogFields(true, null, true);

        foreach ($arrFields as $strFieldname => $arrField) {
            if (in_array($arrField['type'], $this->arrForbiddenFilterTypes)) continue;
            $this->arrFields[$arrField['id']] = $arrField['_dcFormat'];
        }

        $this->setActiveFields();
    }

    public function generateForm()
    {

        $strFields = '';

        if (!empty($this->arrActiveFields) && is_array($this->arrActiveFields)) {

            $arrFieldTemplates = Toolkit::deserialize($this->catalogFilterFieldTemplates);
            $arrFieldDependencies = Toolkit::deserialize($this->catalogFilterFieldDependencies);
            $arrFieldsChangeOnSubmit = Toolkit::deserialize($this->catalogFieldsChangeOnSubmit);

            foreach ($this->arrActiveFields as $arrField) {

                $arrField = $this->convertWidgetToField($arrField);
                $strClass = $this->fieldClassExist($arrField['inputType']);

                if ($strClass === false) return null;

                $objWidget = new $strClass($strClass::getAttributesFromDca($arrField, $arrField['_fieldname'], $arrField['default'], '', ''));

                $objWidget->mandatory = false;
                $objWidget->id = 'id_' . $arrField['_fieldname'];
                $objWidget->value = $this->getValue($arrField['_fieldname']);
                $objWidget->placeholder = $arrField['_placeholder'] ?: '';

                if ($objWidget->value) {
                    $this->arrDependencies[] = $arrField['_fieldname'];
                }

                if (is_array($arrField['_cssID']) && ($arrField['_cssID'][0] || $arrField['_cssID'][1])) {

                    if ($arrField['_cssID'][0]) {
                        $objWidget->id = 'id_' . $arrField['_cssID'][0];
                    }

                    if ($arrField['_cssID'][1]) {
                        $objWidget->class = ' ' . $arrField['_cssID'][1];
                    }
                }

                if (!empty($arrFieldsChangeOnSubmit) && is_array($arrFieldsChangeOnSubmit)) {

                    if (in_array($arrField['_fieldname'], $arrFieldsChangeOnSubmit)) {
                        $objWidget->addAttributes(['onchange' => 'this.form.submit()']);
                    }
                }

                if (!empty($arrFieldTemplates) && is_array($arrFieldTemplates)) {

                    $arrTemplate = $arrFieldTemplates[$arrField['_fieldname']];

                    if ($arrTemplate && $arrTemplate['value']) {
                        $objWidget->template = $arrTemplate['value'];
                    }
                }

                if (!empty($arrFieldDependencies) && is_array($arrFieldDependencies)) {

                    $arrDependencies = $arrFieldDependencies[$arrField['_fieldname']];
                    if ($arrDependencies && $arrDependencies['value'] && !in_array($arrDependencies['value'], $this->arrDependencies)) {
                        continue;
                    }
                }

                if (!$objWidget->value && $arrField['default']) {
                    $objWidget->value = $arrField['default'];
                }

                $strFields .= $objWidget->parse();
            }
        }

        return $strFields;
    }

    public function setActionAttribute()
    {

        if ($this->catalogRedirectType && $this->catalogRedirectType == 'internal') {

            $objPage = new PageModel();
            $arrPage = $objPage->findPublishedById($this->catalogInternalFormRedirect);

            if ($arrPage != null) {
                return $arrPage->getFrontendUrl();
            }
        }

        if ($this->catalogRedirectType && $this->catalogRedirectType == 'external') {

            return $this->catalogExternalFormRedirect;
        }

        return Environment::get('indexFreeRequest');
    }

    public function setResetLink()
    {

        if (!$this->catalogResetFilterForm || $this->catalogFormMethod == 'POST') return '';

        return sprintf('<p class="reset"><a href="%s" id="id_reset_%s">' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['resetForm'] . '</a></p>',

            str_replace(Environment::get('queryString'), '', Environment::get('requestUri')),
            $this->id
        );
    }

    protected function getValue($strFieldname)
    {

        if (!$strFieldname) return '';

        return $this->CatalogInput->getActiveValue($strFieldname);
    }

    protected function setOptions()
    {

        if (!empty($this->arrOptions) && is_array($this->arrOptions)) {

            foreach ($this->arrOptions as $strKey => $varValue) {

                $this->{$strKey} = $varValue;
            }
        }
    }

    protected function setActiveFields()
    {

        $this->catalogActiveFilterFields = Toolkit::deserialize($this->catalogActiveFilterFields);

        if (!empty($this->catalogActiveFilterFields) && is_array($this->catalogActiveFilterFields)) {

            foreach ($this->catalogActiveFilterFields as $strID) {

                if (!$this->arrFields[$strID]) continue;
                $this->arrActiveFields[$strID] = $this->arrFields[$strID];
            }
        }
    }

    protected function convertWidgetToField($arrField)
    {

        if ($arrField['inputType'] == 'checkboxWizard') {
            $arrField['inputType'] = 'checkbox';
        }

        $arrField['eval']['tableless'] = '1';
        $arrField['eval']['required'] = false;

        return $arrField;
    }


    protected function fieldClassExist($strInputType)
    {

        $strClass = $GLOBALS['TL_FFL'][$strInputType];

        if (!class_exists($strClass)) {
            return false;
        }

        return $strClass;
    }
}