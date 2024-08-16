<?php

namespace Alnv\CatalogManagerBundle\Widgets;

use Alnv\CatalogManagerBundle\Cache;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\Database;
use Contao\Widget;

class CatalogFilterFieldSelectWizard extends Widget
{

    protected bool $blnEmpty = true;

    protected array $arrCatalogFields = [];

    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    public function __set($strKey, $varValue)
    {

        switch ($strKey) {
            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    public function validate()
    {
        parent::validate();
    }

    public function generate()
    {

        $this->import(Database::class, 'Database');

        if (!$this->varValue) $this->varValue = [];
        if (!Cache::has('tabindex')) Cache::set('tabindex', 1);

        $intTabindex = Cache::get('tabindex');
        $arrActiveFilterFields = Toolkit::deserialize($this->objDca->activeRecord->catalogActiveFilterFields);

        if (empty($arrActiveFilterFields) || !is_array($arrActiveFilterFields)) {
            return '-';
        }

        $objCatalogFields = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE id IN ( ' . implode(',', $arrActiveFilterFields) . ' )')->execute();

        while ($objCatalogFields->next()) {
            $this->arrCatalogFields[] = $objCatalogFields->row();
        }

        $strTemplate =
            '<table class="tl_optionwizard" id="ctrl_' . $this->strId . '">' .
            '<thead>' .
            '<tr>' .
            '<th>Name</th>' .
            '<th></th>' .
            '</tr>' .
            '</thead>' .
            '<tbody data-tabindex="' . $intTabindex . '">' .
            $this->generateRows($intTabindex) .
            '</tbody>' .
            '</table>';


        return $this->blnEmpty ? '-' : $strTemplate;
    }

    protected function generateRows($intTabindex): string
    {

        $strRows = '';
        $intIndex = 0;

        foreach ($this->arrCatalogFields as $arrField) {
            $strRows .= sprintf(
                '<tr><td style="white-space:nowrap; padding-right:3px"><label for="id_%s">%s:</label></td><td><select name="%s" id="id_%s" tabindex="%s" class="tl_select tl_chosen tl_select min-width">%s</select><input type="hidden" name="%s" value="%s"></td></tr>',
                $this->strId . '_' . $intIndex . '_' . $arrField['fieldname'],
                $arrField['title'] ? $arrField['title'] : $arrField['fieldname'],
                $this->strId . '[' . $arrField['fieldname'] . '][value]',
                $this->strId . '_' . $intIndex . '_' . $arrField['fieldname'],
                $intTabindex++,
                $this->getSelectOptions($arrField, $arrField['fieldname']),
                $this->strId . '[' . $arrField['fieldname'] . '][fieldname]',
                $arrField['fieldname']
            );

            $intIndex++;
        }

        if ($intIndex) $this->blnEmpty = false;

        return $strRows;
    }

    protected function getSelectOptions($arrField, $intIndex): string
    {

        switch ($this->selectType) {
            case 'dependencies':
                return $this->getDependenciesOptions($arrField, $intIndex);
            case 'templates':
                return $this->getTemplateOptions($arrField, $intIndex);
        }

        return '<option value="">-</option>';
    }

    protected function getDependenciesOptions($arrField, $intIndex): string
    {

        $strOptions = '<option value="">-</option>';

        foreach ($this->arrCatalogFields as $arrCatalogField) {

            if ($arrCatalogField['id'] == $arrField['id']) continue;

            $strOptions .= sprintf(

                '<option value="%s" %s>%s</option>',
                $arrCatalogField['fieldname'],
                $this->varValue[$intIndex]['value'] == $arrCatalogField['fieldname'] ? 'selected' : '',
                $arrCatalogField['title'] ?: $arrCatalogField['fieldname']
            );
        }

        return $strOptions;
    }

    protected function getTemplateOptions($arrField, $intIndex): string
    {

        $strOptions = '<option value="">-</option>';
        $strType = Toolkit::convertCatalogTypeToFormType($arrField['type']);
        $arrTemplates = $this->getTemplateGroup('form_' . $strType);

        foreach ($arrTemplates as $strValue => $strName) {

            $strOptions .= sprintf(

                '<option value="%s" %s>%s</option>',
                $strValue,
                $this->varValue[$intIndex]['value'] == $strValue ? 'selected' : '',
                $strName
            );
        }

        return $strOptions;
    }
}