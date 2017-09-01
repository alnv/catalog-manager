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

        $this->import( 'CatalogFieldBuilder' );
        $strCommand = 'cmd_' . $this->strField;

        if ( !$this->varValue ) $this->varValue = [];

        if ( \Input::get( $strCommand ) && is_numeric( \Input::get('cid') ) && \Input::get('id') == $this->currentRecord ) {

            $this->import('Database');

            $strCID = \Input::get('cid');
            $strSID = \Input::get('subId');

            switch ( \Input::get( $strCommand ) ) {

                case 'addQuery':

                    if ( !$this->varValue['field'] ) break;

                    $this->varValue['query'][] = [

                        'value' => '',
                        'operator' => 'equal',
                        'field' => $this->varValue['field']
                    ];

                    unset( $this->varValue['field'] );

                    break;

                case 'addOrQuery':

                    $arrOrQuery = [

                        'value' => '',
                        'field' => 'id',
                        'operator' => 'equal'
                    ];

                    $arrOrQuery['field'] = $this->varValue['query'][$strCID]['field'];
                    $this->varValue['query'][$strCID]['subQueries'][] = $arrOrQuery;

                    break;

                case 'deleteQuery':

                    if ( ( $strCID != '' && is_numeric( $strCID ) ) && ( $strSID == '' || is_null( $strSID ) ) ) {

                        unset( $this->varValue['query'][$strCID] );
                    }

                    if ( ( $strCID != '' && is_numeric( $strCID ) ) && ( $strSID != '' && is_numeric( $strSID ) ) ) {

                        unset( $this->varValue['query'][$strCID]['subQueries'][$strSID] );
                    }

                    break;
            }

            $this->Database->prepare( sprintf( 'UPDATE %s SET `%s`=? WHERE id=? ', \Input::get('table'), $this->strField ) )->execute( serialize( $this->varValue ), $this->currentRecord );
            $strRedirectUrl = \Environment::get('request');
            $strRedirectUrl = preg_replace( '/&(amp;)?cid=[^&]*/i', '', preg_replace( '/&(amp;)?' . preg_quote( $strCommand, '/' ) . '=[^&]*/i', '', $strRedirectUrl ));
            $strRedirectUrl = preg_replace( '/&(amp;)?subId=[^&]*/i', '', preg_replace( '/&(amp;)?' . preg_quote( $strCommand, '/' ) . '=[^&]*/i', '', $strRedirectUrl ));
            $this->redirect( $strRedirectUrl );
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

        $strRowTemplate = '';
        $strHeadTemplate =
            '<table border="0" cellspacing="0" cellpadding="0" style="margin-top: 5px; margin-bottom: 5px;" class="ctlg_taxonomies_field_selector_table">'.
                '<thead>'.
                    '<tr>'.
                        '<td>' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['field'] . '</td>'.
                        '<td>&nbsp;</td>'.
                    '</tr>'.
                '</thead>'.
                '<tbody>'.
                    '<tr>'.
                        '<td><div class="ctlg_taxonomy_main_selector">' . $this->getFieldSelector() . '</div></td>'.
                        '<td><div class="ctlg_taxonomy_add_button">' . $this->getAddButton() . '</div></td>'.
                    '</tr>'.
                '</tbody>'.
            '</table>';

        if ( !empty( $this->varValue['query'] ) && is_array( $this->varValue['query'] ) ) {

            foreach ( $this->varValue['query'] as $intIndex => $arrQuery ) {

                $blnHasSubs = ( !empty( $arrQuery['subQueries'] ) && is_array( $arrQuery['subQueries'] ) );
                $strRowTemplate .= $this->generateQueryInputFields( $arrQuery, $blnHasSubs ? 'or' : 'and', $intIndex, '' );

                if ( $blnHasSubs ) {

                    foreach ( $arrQuery['subQueries'] as $intSubIndex => $arrSubQuery ) {

                        $strRowTemplate .= $this->generateQueryInputFields( $arrSubQuery, 'or', $intIndex, $intSubIndex );
                    }
                }
            }
        }

        $strTemplate =
            '<table width="100%" border="0" cellspacing="0" cellpadding="0">'.
                '<thead>'.
                    '<tr>'.
                        '<td>' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['field'] . '</td>'.
                        '<td>' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['operator'] . '</td>'.
                        '<td>' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['value'] . '</td>'.
                        '<td>&nbsp;</td>'.
                    '</tr>'.
                '</thead>'.
                '<tbody>'.
                    $strRowTemplate.
                '</tbody>'.
            '</table>';


        if ( !$strRowTemplate ) $strTemplate = '';

        return $strHeadTemplate.$strTemplate;
    }


    protected function generateQueryInputFields( $arrQuery, $strType, $intIndex, $intSubIndex ) {

        $strID = $this->strId . '_query_'. $intIndex;
        $strName = $this->strId . '[query]['. $intIndex .']';
        $strPaddingStyle = $strType == 'or' ? 'style="white-space:nowrap; padding-left:10px"' : '';
        $strBackgroundStyle = $intIndex % 2 != 0 ? 'style="background:#f9f9f9"' : 'style="background:#f2f2f2"';

        if ( is_numeric( $intSubIndex ) ) {

            $strID .= '_' . $intSubIndex;
            $strName .= '[subQueries]['.$intSubIndex.']';
        }

        switch ( $arrQuery['operator'] ) {

            case 'between':

                if ( !$arrQuery['value'] || is_string( $arrQuery['value'] ) ) {

                    $arrQuery['value'] = [ '', '' ];
                }

                $strFieldTemplate =
                    '<tr '. $strBackgroundStyle .'>'.
                        '<td '. $strPaddingStyle .' class="ctlg_select_field"><select name="%s" id="%s" class="ctlg_select tl_select tl_chosen">%s</select></td>'.
                        '<td '. $strPaddingStyle .' class="ctlg_select_operator"><select name="%s" id="%s" class="ctlg_select tl_select tl_chosen" onchange="Backend.autoSubmit(\''. $this->dcTable .'\');">%s</select></td>'.
                        '<td '. $strPaddingStyle .' class="ctlg_text_value"><input type="text" name="%s" id="%s" value="%s" class="ctlg_text_w50 tl_text"><input type="text" name="%s" id="%s" value="%s" class="ctlg_text_w50 last tl_text"></td>'.
                        '<td  class="ctlg_button">'. $this->getOrButton( $intIndex, $intSubIndex ) . ' ' . $this->getDeleteButton( $intIndex, $intSubIndex ) . '</td>'.
                    '</tr>';

                return sprintf(

                    $strFieldTemplate,
                    $strName . '[field]',
                    $strID,
                    $this->getFieldOptions( $arrQuery['field'], false ),
                    $strName . '[operator]',
                    $strID,
                    $this->getOperatorOptions( $arrQuery ),
                    $strName . '[value][0]',
                    $strID . '_2',
                    isset( $arrQuery['value'][0] ) ? $arrQuery['value'][0] : '',
                    $strName . '[value][1]',
                    $strID . '_1',
                    isset( $arrQuery['value'][1] ) ? $arrQuery['value'][1] : ''
                );

                break;

            default:

                $blnReadOnly = false;

                if ( is_array( $arrQuery['value'] ) ) {

                    $arrQuery['value'] = $arrQuery['value'][0] ? $arrQuery['value'][0] : '';
                }

                if ( in_array( $arrQuery['operator'], [ 'isEmpty', 'isNotEmpty' ] ) ) {

                    $blnReadOnly = true;
                }

                $strFieldTemplate =
                    '<tr '. $strBackgroundStyle .'>'.
                        '<td '. $strPaddingStyle .' class="ctlg_select_field"><select name="%s" id="%s" class="ctlg_select tl_select tl_chosen">%s</select></td>'.
                        '<td '. $strPaddingStyle .' class="ctlg_select_operator"><select name="%s" id="%s" class="ctlg_select tl_select tl_chosen" onchange="Backend.autoSubmit(\''. $this->dcTable .'\');">%s</select></td>'.
                        '<td '. $strPaddingStyle .' class="ctlg_text_value"><input type="text" name="%s" id="%s" value="%s" class="ctlg_text tl_text" '. ( $blnReadOnly ? 'readonly' : '' ) .' ></td>'.
                        '<td  class="ctlg_button" style="white-space:nowrap;padding-left:3px">'. $this->getOrButton( $intIndex, $intSubIndex ) . ' ' . $this->getDeleteButton( $intIndex, $intSubIndex ) . '</td>'.
                    '</tr>';

                return sprintf(

                    $strFieldTemplate,
                    $strName . '[field]',
                    $strID,
                    $this->getFieldOptions( $arrQuery['field'], false ),
                    $strName . '[operator]',
                    $strID,
                    $this->getOperatorOptions( $arrQuery ),
                    $strName . '[value]',
                    $strID,
                    $arrQuery['value']
                );

                break;
        }
    }


    protected function getOperatorOptions( $arrQuery ) {

        $strOperatorsOptions = '';
        $arrOperators = [

            'equal',
            'not',
            'regexp',
            'findInSet',
            'gt',
            'gte',
            'lt',
            'lte',
            'contain',
            'between',
            'isEmpty',
            'isNotEmpty'
        ];

        foreach ( $arrOperators as $strOperator ) {

            $strText = $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][ $strOperator ] ? $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][ $strOperator ] : $strOperator;
            $strOperatorsOptions .= sprintf( '<option value="%s" %s>%s</option>', $strOperator, ( $arrQuery['operator'] == $strOperator ? 'selected' : '' ), $strText );
        }

        return $strOperatorsOptions;
    }


    protected function getFieldSelector() {

        return sprintf(
            '<select name="%s" id="%s" class="tl_select tl_chosen" onchange="Backend.autoSubmit(\''. $this->dcTable .'\');">%s</select>',
            $this->strId . '[field]',
            $this->strId . '_field',
            $this->getFieldOptions( $this->varValue['field'], true )
        );
    }


    protected function getFieldOptions( $strValue, $blnEmptyOption = true ) {

        $strOptions = $blnEmptyOption ? '<option value="">-</option>' : '';

        foreach (  $this->arrFields as $strField ) {

            $strOptions .= sprintf(

                '<option value="%s" %s>%s</option>',
                $strField,
                ( $strValue == $strField ? 'selected' : '' ),
                Toolkit::getLabelValue( $this->arrTaxonomies[ $strField ]['label'], $strField ) . ' <span style="color:#333;font-size:12px;display:inline;">['. $strField .']</span>'
            );
        }

        return $strOptions;
    }


    protected function getDeleteButton( $intIndex, $intSubIndex ) {

        return '<a href="'. \Environment::get('indexFreeRequest') .'&amp;cid='. $intIndex .'&amp;subId='. $intSubIndex .'&amp;cmd_'. $this->strId .'=deleteQuery" title="'. sprintf( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['deleteQuery'], $intIndex ) .'">' . \Image::getHtml('delete.gif', 'delete query') .'</a>';
    }


    protected function getAddButton() {

        return '<a href="'. \Environment::get('indexFreeRequest') .'&amp;cid=0&amp;cmd_'. $this->strId .'=addQuery" title="'. $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['addQuery'] .'">' . \Image::getHtml('copy.gif', 'add query') .'</a>';
    }


    protected function getOrButton( $intIndex, $intSubIndex ) {

        return '<a href="'. \Environment::get('indexFreeRequest') .'&amp;cid='. $intIndex .'&amp;subId='. $intSubIndex .'&amp;cmd_'. $this->strId .'=addOrQuery" title="'. $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['orQuery'] .'">' . \Image::getHtml('system/modules/catalog-manager/assets/icons/or.svg', 'add or Query') .'</a>';
    }
}