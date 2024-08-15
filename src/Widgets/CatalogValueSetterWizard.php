<?php

namespace Alnv\CatalogManagerBundle\Widgets;

use Alnv\CatalogManagerBundle\Cache;
use Contao\ArrayUtil;
use Contao\Input;
use Contao\StringUtil;
use Contao\Widget;
use Contao\System;
use Contao\Image;

class CatalogValueSetterWizard extends Widget
{

    protected array $arrKeys = [];
    protected string $strCommand = '';
    protected array $arrButtons = [];
    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    public function __set($strKey, $varValue)
    {

        switch ($strKey) {
            case 'options':
                $this->arrOptions = StringUtil::deserialize($varValue);
                break;
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

        $this->arrButtons = ['copy', 'delete'];
        $this->strCommand = 'cmd_' . $this->strField;

        if (Input::get($this->strCommand) && is_numeric(Input::get('cid')) && Input::get('id') == $this->currentRecord) {

            $this->importDatabase::class;

            switch (Input::get($this->strCommand)) {
                case 'copy':
                    ArrayUtil::arrayInsert($this->varValue, Input::get('cid'), [$this->varValue[Input::get('cid')]]);
                    break;

                case 'delete':
                    $this->varValue = array_delete($this->varValue, Input::get('cid'));
                    break;
            }

            $this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")->execute(serialize($this->varValue), $this->currentRecord);
            $this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($this->strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));
        }

        if (!is_array($this->varValue) || !$this->varValue[0]) {
            $this->varValue = [['']];
        }

        if (!empty($this->getKeys) && is_array($this->getKeys)) {
            $this->import($this->getKeys[0]);
            $this->arrKeys = $this->{$this->getKeys[0]}->{$this->getKeys[1]}($this);
        }

        if (!Cache::has('tabindex')) Cache::set('tabindex', 1);

        $strTabindex = Cache::get('tabindex');

        $strReturn =
            '<table class="tl_optionwizard" id="ctrl_' . $this->strId . '">' .
            '<thead>' .
            '<tr>' .
            '<th>' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['catalogValueSetterWizard'][0] . '</th>' .
            '<th>' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['catalogValueSetterWizard'][1] . '</th>' .
            '<th>&nbsp;</th>' .
            '</tr>' .
            '</thead>' .
            sprintf('<tbody data-tabindex="%s">%s</tbody>', $strTabindex, $this->parseInputs()) .
            '</table>';

        Cache::set('tabindex', $strTabindex);

        return $strReturn;
    }

    protected function parseInputs(): string
    {

        $strReturn = '';
        foreach ($this->varValue as $intIndex => $arrValue) {
            $strReturn .= '<tr>';
            $strReturn .= sprintf('<td><select name="%s" id="%s" class="tl_select tl_chosen tl_catalog_widget min-width">%s</select></td>', $this->strId . '[' . $intIndex . '][key]', $this->strId . '_key_' . $intIndex, $this->getSelectOptions($arrValue['key']));
            $strReturn .= sprintf('<td><input type="text" name="%s" id="%s" value="%s" class="tl_text"/></td>', $this->strId . '[' . $intIndex . '][value]', $this->strId . '_value_' . $intIndex, $arrValue['value']);
            $strReturn .= sprintf('<td style="white-space:nowrap;padding-left:3px">%s</td>', $this->parseButtons($intIndex));
            $strReturn .= '</tr>';
        }

        return $strReturn;
    }

    protected function getSelectOptions($strValue): string
    {

        $strReturn = $this->includeBlankOption ? '<option value >' . ($this->blankOptionLabel ? $this->blankOptionLabel : '') . '</option>' : '';
        if (!empty($this->arrKeys)) {
            foreach ($this->arrKeys as $strColumn => $strLabel) {
                $strReturn .= sprintf('<option value="%s" %s>%s</option>', $strColumn, ($strValue == $strColumn ? 'selected' : ''), $strLabel);
            }
        }

        return $strReturn;
    }

    protected function parseButtons($intIndex): string
    {

        $strRequestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        $strReturn = '';

        foreach ($this->arrButtons as $strButton) {
            $strClass = '';
            $strReturn .= '<a href="' . $this->addToUrl('&amp;' . $this->strCommand . '=' . $strButton . '&amp;cid=' . $intIndex . '&amp;rt=' . $strRequestToken . '&amp;id=' . $this->currentRecord) . '"' . $strClass . ' title="' . StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['ow_' . $strButton]) . '" onclick="CatalogManager.CatalogOrderByWizard(this,\'' . $strButton . '\',\'ctrl_' . $this->strId . '\');return false">' . Image::getHtml($strButton . '.gif', $GLOBALS['TL_LANG']['MSC']['ow_' . $strButton]) . '</a> ';
        }

        return $strReturn;
    }
}