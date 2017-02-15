<?php

namespace CatalogManager;

class CatalogTaxonomyWizard extends \Widget {


    private $strTable = '';
    private $arrFields = [];
    private $arrTaxonomies = [];

    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';


    public function __set($strKey, $varValue) {

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

        $strTemplate = '';
        $this->import( 'DCABuilderHelper' );

        if ( !$this->varValue ) {

            $this->varValue = [];
        }

        if ( !empty( $this->taxonomyTable ) && is_array( $this->taxonomyTable ) ) {

            $this->import( $this->taxonomyTable[0] );
            $this->strTable = $this->{$this->taxonomyTable[0]}->{$this->taxonomyTable[1]}( $this->objDca );
        }

        if ( !empty( $this->taxonomyEntities ) && is_array( $this->taxonomyEntities ) ) {

            $this->import( $this->taxonomyEntities[0] );
            $this->arrTaxonomies = $this->{$this->taxonomyEntities[0]}->{$this->taxonomyEntities[1]}( $this->objDca, $this->strTable );
        }

        $this->arrFields = array_keys( $this->arrTaxonomies );
        $this->arrTaxonomies = $this->DCABuilderHelper->convertCatalogFields2DCA( $this->arrTaxonomies );

        $strSelectedTable = $this->varValue['table'] ? $this->varValue['table'] : '';

        $this->cleanUpValues();

        $strHeadTemplate =

            '<table>'.
                '<thead>'.
                    '<tr>'.
                        '<td>Feld auswählen</td>'.
                        '<td>&nbsp;</td>'.
                    '</tr>'.
                '</thead>'.
                '<tbody>'.
                    '<tr>'.
                        '<td>' . $this->getFieldSelector() . '</td>'.
                        '<td style="white-space:nowrap;padding-left:3px">' . $this->getAddButton() . '</td>'.
                    '</tr>'.
                '</tbody>'.
            '</table>';

        if ( !empty( $this->varValue ) && is_array( $this->varValue ) ) {

            foreach ( $this->varValue as $arrQuery ) {

                // todo
            }
        }

        $strTemplate =
            '<input type="text" name="'.$this->strId.'[query][0][field]" value="name">'.
            '<input type="text" name="'.$this->strId.'[query][0][operator]" value="equal">'.
            '<input type="text" name="'.$this->strId.'[query][0][value]" value="x">'.
            '<input type="text" name="'.$this->strId.'[query][1][field]" value="lastname">'.
            '<input type="text" name="'.$this->strId.'[query][1][operator]" value="equal">'.
            '<input type="text" name="'.$this->strId.'[query][1][value]" value="y">'.
            '<input type="text" name="'.$this->strId.'[query][2][field]" value="age">'.
            '<input type="text" name="'.$this->strId.'[query][2][operator]" value="equal">'.
            '<input type="text" name="'.$this->strId.'[query][2][value]" value="18">'.
            '<input type="text" name="'.$this->strId.'[query][2][0][field]" value="age">'.
            '<input type="text" name="'.$this->strId.'[query][2][0][operator]" value="equal">'.
            '<input type="text" name="'.$this->strId.'[query][2][0][value]" value="21">'.
            '<input type="text" name="'.$this->strId.'[query][2][1][field]" value="age">'.
            '<input type="text" name="'.$this->strId.'[query][2][1][operator]" value="equal">'.
            '<input type="text" name="'.$this->strId.'[query][2][1][value]" value="50">';

        return $strHeadTemplate.$strTemplate;
    }


    protected function cleanUpValues() {

        $arrValues = [];

        if ( !empty( $this->varValue['query'] ) && is_array( $this->varValue['query'] ) ) {

            foreach ( $this->varValue['query'] as $intIndex => $arrValue ) {
                
                $arrQuery = [

                    'field' => $arrValue['field'],
                    'value' => $arrValue['value'],
                    'operator' => $arrValue['operator']
                ];

                if ( count( $arrValue ) > 3 ) {

                    $arrValues[ $intIndex ][] = $arrQuery;

                    foreach ( $arrValue as $strKey => $strValue ) {

                        if ( is_array( $strValue ) ) {

                            $arrValues[ $intIndex ][] = $strValue;
                        }
                    }
                }

                else {

                    $arrValues[ $intIndex ] = $arrQuery;
                }
            }
        }

        $this->varValue = $arrValues;
    }


    protected function getFieldSelector() {

        return sprintf(
            '<select name="%s" id="%s" class="tl_select tl_chosen">%s</select>',
            $this->strId . '[query]',
            $this->strId . '_query',
            $this->getFieldOptions()
        );
    }


    protected function getFieldOptions() {

        $strOptions = '<option value="">-</option>';

        foreach (  $this->arrFields as $strField ) {

            $strOptions .= sprintf(
                '<option value="%s">%s</option>',
                $strField,
                $this->arrTaxonomies[$strField]['label'][0] ? $this->arrTaxonomies[$strField]['label'][0] : $strField
            );
        }

        return $strOptions;
    }

    
    protected function getAddButton() {

       return ' <a href="" title="" onclick="return false">' . \Image::getHtml('new.gif', 'Taxonomy hinzufügen') .'</a>';
    }
}