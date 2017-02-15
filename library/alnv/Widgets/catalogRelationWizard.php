<?php

namespace CatalogManager;

class CatalogRelationWizard extends \Widget {


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

            $this->Database->prepare( "UPDATE " . $this->strTable . " SET " . $this->strField . " =? WHERE id =?" )->execute( serialize( $this->varValue ), $this->currentRecord );
            $this->redirect( preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace( '/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request') ) ) );
        }

        $blnCheckAll = true;
        $arrRelatedTables = [];

        foreach ( $this->arrOptions as $intIndex => $arrOption ) {

            $strButtons = \Image::getHtml( 'drag.gif', '', 'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '"' );

            foreach ( $arrButtons as $strButton ) {

                $strButtons .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$strButton.'&amp;cid='.$intIndex.'&amp;id='.$this->currentRecord).'" class="button-move" title="'.specialchars($GLOBALS['TL_LANG']['MSC']['move_'.$strButton][1]).'" onclick="Backend.optionsWizard(this,\''.$strButton.'\',\'ctrl_'.$this->strId.'\');return false">'.\Image::getHtml($strButton.'.gif', $GLOBALS['TL_LANG']['MSC']['move_'.$strButton][0], 'class="tl_checkbox_wizard_img"').'</a> ';
            }

            $arrRelatedTables[] = $this->generateRelatedInputField( $arrOption, $intIndex, $strButtons );
        }

        if ( empty( $arrRelatedTables ) ) {

            return '-';
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
                        '<th>'. $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['pageURL'] .'</th>'.
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


    protected function generateRelatedInputField( $arrOption, $intIndex, $strButtons ) {

        $strTemplate =
            '<tr>'.
                '<td><input type="checkbox" name="%s" id="%s" class="tl_checkbox" value="%s" %s></td>'.
                '<td><label for="%s">%s</label></td>'.
                '<td><div class="wizard"><input name="%s" id="%s" class="tl_text" value="%s">%s</div></td>'.
                '<td>'. $strButtons .'</td>'.
            '</tr>';

        return sprintf(

            $strTemplate,
            $this->strName . '['. $intIndex .'][table]',
            $this->strId . '_table_' . $intIndex,
            $arrOption['value'],
            $this->isCustomChecked( $arrOption['value'] ),
            $this->strId . '_table_' . $intIndex,
            $arrOption['label'],
            $this->strName . '[' . $intIndex . '][pageURL]',
            $this->strId . '_pageURL_' . $intIndex,
            $this->getValues( $intIndex ),
            $this->createPagePicker( $intIndex )
        );
    }


    protected function createPagePicker( $intIndex ) {

        $varValue = $this->getValues( $intIndex );
        $strID = $this->strId . '_pageURL_' . $intIndex;
        $strName = $this->strName . '['.$intIndex.'][pageURL]';

        return ' <a href="contao/page.php?do=' . \Input::get('do') . '&amp;table=tl_module&amp;field=' . $strName . '&amp;value=' . rawurlencode( str_replace( array('{{link_url::', '}}'), '', $varValue ) ) . '&amp;switch=1' . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']) . '" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA']['tl_module']['fields'][$this->strName]['label'][0])) . '\',\'url\':this.href,\'id\':\'' . $strID . '\',\'tag\':\''. $strID . (( \Input::get('act') == 'editAll') ? '_' . $strID : '') . '\',\'self\':this});return false">' . \Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="vertical-align:top;cursor:pointer"') . '</a>';
    }


    protected function getValues( $intIndex ) {

        return isset( $this->varValue[$intIndex]['pageURL'] ) ? $this->varValue[$intIndex]['pageURL'] : '';
    }


    protected function isCustomChecked( $strValue ) {

        foreach ( $this->varValue as $arrValue ) {

            if ( $arrValue['table'] && $arrValue['table'] == $strValue ) {

                return 'checked';
            }
        }

        return '';
    }
}