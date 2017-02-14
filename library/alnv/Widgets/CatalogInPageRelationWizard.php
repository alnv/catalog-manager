<?php

namespace CatalogManager;

class CatalogInPageRelationWizard extends \Widget {


    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';


    public function __set($strKey, $varValue) {

        switch ($strKey) {

            case 'options':

                $this->arrOptions = deserialize( $varValue );

                break;

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
        $arrButtons = [ 'drag', 'up', 'down' ];
        $strCommand = 'cmd_' . $this->strField;

        if ( !is_array( $this->varValue ) ) {

            $this->varValue = [['']];
        }

        if ( \Input::get( $strCommand ) && is_numeric( \Input::get('cid') ) && \Input::get('id') == $this->currentRecord ) {

            switch ( \Input::get( $strCommand ) ) {

                case 'up':

                    $this->varValue = array_move_up( $this->varValue, \Input::get('cid') );

                    break;

                case 'down':

                    $this->varValue = array_move_down( $this->varValue, \Input::get('cid') );

                    break;
            }

            $this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")->execute( serialize( $this->varValue ), $this->currentRecord );
            $this->redirect( preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace( '/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request') ) ) );
        }

        /*
        if ( is_array( $this->varValue ) ) {

            $arrOptions = [];
            $arrTemp = $this->arrOptions;

            foreach ( $this->arrOptions as $i => $arrOption ) {

                foreach ( $this->varValue as $intPos => $arrValue ) {

                    if ( ( array_search( $arrOption['value'], $arrValue ) ) !== false ) {

                        $arrOptions[$intPos] = $arrOption;

                        unset( $arrTemp[$i] );
                    }
                }
            }

            var_dump($arrOptions);

            ksort($arrOptions);

            $this->arrOptions = array_merge( $arrOptions, $arrTemp );
        }
        */

        $blnCheckAll = true;
        $arrCatalogViews = [];
        $arrRelatedTables = [];
        $arrCatalogChildTables = [];

        if ( !$this->orderByTablename ) {

            $objModule = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $this->strTable ) )->limit(1)->execute( $this->currentRecord );

            if ( $objModule->numRows && $objModule->catalogTablename ) {

                $this->orderByTablename = $objModule->catalogTablename;
            }
        }

        if ( !empty( $this->CatalogViews ) && is_array( $this->CatalogViews ) ) {

            $this->import( $this->CatalogViews[0] );

            $arrCatalogViews = $this->{$this->CatalogViews[0]}->{$this->CatalogViews[1]}();
        }

        if ( !empty( $this->CatalogChildTables ) && is_array( $this->CatalogChildTables ) ) {

            $this->import( $this->CatalogChildTables[0] );
            $arrCatalogChildTables = $this->{$this->CatalogChildTables[0]}->{$this->CatalogChildTables[1]}( $this->orderByTablename );
        }

        foreach ( $arrCatalogChildTables as $intIndex => $strTable ) {

            $strButtons = \Image::getHtml('drag.gif', '', 'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '"');

            foreach ($arrButtons as $strButton) {

                $strButtons .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$strButton.'&amp;cid='.$intIndex.'&amp;id='.$this->currentRecord).'" class="button-move" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['move_'.$strButton][1]).'" onclick="Backend.optionsWizard(this,\''.$strButton.'\',\'ctrl_'.$this->strId.'\');return false">'.\Image::getHtml($strButton.'.gif', $GLOBALS['TL_LANG']['MSC']['move_'.$strButton][0], 'class="tl_checkbox_wizard_img"').'</a> ';
            }

            $arrRelatedTables[] = $this->generateRelatedInputField( $strTable, $arrCatalogViews, $intIndex, $strButtons );
        }

        if ( empty( $arrRelatedTables ) ) {

            $blnCheckAll = false;
        }

        if ( !\Cache::has( 'tabindex' ) ) {

            \Cache::set( 'tabindex', 1 );
        }

        $tabindex = \Cache::get( 'tabindex' );

        $strTemplate =
            '<table class="tl_optionwizard" id="ctrl_'.$this->strId.'">'.
                '<thead>'.
                    '<tr>'.
                        '<th>'.( $blnCheckAll ? '<span class="fixed"><input type="checkbox" id="check_all_' . $this->strId . '" class="tl_checkbox" onclick="Backend.toggleCheckboxGroup(this,\'ctrl_' . $this->strId . '\')"></span>' : '').'</th>'.
                        '<th>'. $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['table'] .'</th>'.
                        '<th>'. $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['catalogView'] .'</th>'.
                        '<th>&nbsp;</th>'.
                    '</tr>'.
                '</thead>'.
                '<tbody class="sortable" data-tabindex="'.$tabindex.'">'.
                    implode('', $arrRelatedTables ) .
                '</tbody>'.
            '</table>';

        \Cache::set('tabindex', $tabindex);

        return $strTemplate;
    }


    protected function generateRelatedInputField( $strTable, $arrCatalogViews, $intIndex, $strButtons ) {

        $strTemplate =
            '<tr>'.
                '<td><input type="checkbox" name="%s" id="%s" class="tl_checkbox" value="%s" %s></td>'.
                '<td><label for="%s">%s</label></td>'.
                '<td><select name="%s" id="%s" class="tl_select tl_chosen">%s</select></td>'.
                '<td>'. $strButtons .'</td>'.
            '</tr>';

        return sprintf(

            $strTemplate,
            $this->strName . '['. $intIndex .'][table]',
            $this->strId . '_table_' . $intIndex,
            $strTable,
            $this->isCustomChecked( $strTable ),
            $this->strId . '_table_' . $intIndex,
            $strTable,
            $this->strName . '[' . $intIndex . '][view]',
            $this->strId . '_view_' . $intIndex,
            $this->generateSelectOptions( $arrCatalogViews, $intIndex )
        );
    }


    protected function generateSelectOptions( $arrCatalogViews, $intIndex ) {

        $strSelectOptions = $this->includeBlankOption ? '<option value>'. ( $this->blankOptionLabel ? $this->blankOptionLabel : '' ) .'</option>' : '';

        if ( !empty( $arrCatalogViews ) && is_array( $arrCatalogViews ) ) {

            foreach ( $arrCatalogViews as $strKey => $strTitle ) {

                $strSelectOptions .= sprintf( '<option value="%s" %s>%s</option>', $strKey, $this->isCustomSelected( $strKey, $intIndex ), $strTitle );
            }
        }

        return $strSelectOptions;
    }


    private function isCustomChecked( $strValue ) {

        foreach ( $this->varValue as $arrValue ) {

            if ( $arrValue['table'] && $arrValue['table'] == $strValue ) {

                return 'checked';
            }
        }

        return '';
    }


    private function isCustomSelected( $strValue, $intIndex ) {

        if ( isset( $this->varValue[$intIndex] ) ) {

            if ( isset( $this->varValue[$intIndex]['view'] ) && $this->varValue[$intIndex]['view'] == $strValue ) {

                return 'selected';
            }
        }

        return '';
    }
}