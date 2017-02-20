<?php

namespace CatalogManager;

class CatalogFilterFieldTemplateWizard extends \Widget {


    private $blnEmpty = true;


    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';


    public function __set( $strKey, $varValue ) {

        switch ($strKey) {

            default:

                parent::__set( $strKey, $varValue );

                break;
        }
    }


    public function validate() {

        parent::validate();
    }


    public function generate() {

        $this->import('Database');
        $this->import('DCABuilderHelper');

        if ( !$this->varValue ) $this->varValue = [];

        if (!\Cache::has('tabindex')) {

            \Cache::set('tabindex', 1);
        }

        $intTabindex = \Cache::get('tabindex');

        $strTemplate =
            '<table class="tl_optionwizard" id="ctrl_'.$this->strId.'">'.
                '<thead>'.
                    '<tr>'.
                        '<th>Name</th>'.
                        '<th>Template</th>'.
                    '</tr>'.
                '</thead>'.
                '<tbody data-tabindex="'.$intTabindex.'">'.
                    $this->generateRows( $intTabindex ).
                '</tbody>'.
            '</table>';


        return $this->blnEmpty ? '-' : $strTemplate;
    }


    protected function generateRows( $intTabindex ) {

        $strRows = '';
        $intIndex = 0;
        $arrActiveFilterFields = Toolkit::deserialize( $this->objDca->activeRecord->catalogActiveFilterFields );

        foreach ( $arrActiveFilterFields as $strFieldID ) {

            $objCatalogField = $this->Database->prepare('SELECT * FROM tl_catalog_fields WHERE id = ?')->limit(1)->execute( $strFieldID );

            if ( !$objCatalogField->numRows ) continue;

            $strRows .= sprintf(

                '<tr><td style="white-space:nowrap; padding-right:3px"><label for="id_%s">%s:</label></td><td><select name="%s" id="id_%s" tabindex="%s" class="tl_select tl_chosen">%s</select><input type="hidden" name="%s" value="%s"></td></tr>',
                $this->strId . '_' . $intIndex . '_' . $objCatalogField->fieldname,
                $objCatalogField->title ? $objCatalogField->title : $objCatalogField->fieldname,
                $this->strId . '[' . $intIndex . '][template]',
                $this->strId . '_' . $intIndex . '_' . $objCatalogField->fieldname,
                $intTabindex++,
                $this->getTemplateOptions( $objCatalogField, $intIndex ),
                $this->strId . '[' . $intIndex . '][fieldname]',
                $objCatalogField->fieldname
            );

            $intIndex++;
        }

        if ( $intIndex ) $this->blnEmpty = false;

        return $strRows;
    }


    protected function getTemplateOptions( $objCatalogField, $intIndex ) {

        $strOptions = '<option value="">-</option>';
        $strType = $this->DCABuilderHelper->setInputType( $objCatalogField->type );
        $arrTemplates = $this->getTemplateGroup( 'form_' . $strType );

        foreach ( $arrTemplates as $strTemplate ) {

            $strOptions .= sprintf(

                '<option value="%s" %s>%s</option>',
                $strTemplate,
                $this->varValue[$intIndex]['template'] == $strTemplate ? 'selected' : '',
                $strTemplate
            );
        }

        return $strOptions;
    }
}