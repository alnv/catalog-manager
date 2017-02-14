<?php

namespace CatalogManager;

class CatalogOrderByWizard extends \Widget {


    protected $blnSubmitInput = true;
    protected $strTemplate = 'be_widget';


    public function __set( $strKey, $varValue ) {

        switch ($strKey) {

            case 'maxlength':

                if ($varValue > 0) {

                    $this->arrAttributes['maxlength'] = $varValue;
                }

                break;

            default:

                parent::__set($strKey, $varValue);

                break;
        }
    }


    public function validate() {

        $mandatory = $this->mandatory;
        $options = $this->getPost($this->strName);

        if ( is_array( $options ) ) {

            foreach ( $options as $key => $option ) {

                if ( $option['key'] == '' ) {

                    unset( $options[$key] );

                    continue;
                }

                $options[$key]['key'] = trim( $option['key'] );
                $options[$key]['value'] = trim( $option['value'] );

                if ($options[$key]['key'] != '') {

                    $this->mandatory = false;
                }
            }
        }
        $options = array_values( $options );
        $varInput = $this->validator( $options );

        if (!$this->hasErrors()) {

            $this->varValue = $varInput;
        }

        if ( $mandatory ) {

            $this->mandatory = true;
        }
    }


    public function generate() {

        $this->import('Database');
        $strCommand = 'cmd_' . $this->strField;
        $arrButtons = array('copy', 'drag', 'up', 'down', 'delete');

        if (\Input::get($strCommand) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord) {

            switch ( \Input::get( $strCommand ) ) {

                case 'copy':

                    array_insert($this->varValue, \Input::get('cid'), array($this->varValue[\Input::get('cid')]));

                    break;

                case 'up':

                    $this->varValue = array_move_up($this->varValue, \Input::get('cid'));

                    break;

                case 'down':

                    $this->varValue = array_move_down($this->varValue, \Input::get('cid'));

                    break;

                case 'delete':

                    $this->varValue = array_delete($this->varValue, \Input::get('cid'));

                    break;
            }

            $this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")->execute(serialize($this->varValue), $this->currentRecord);
            $this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));
        }

        if (!is_array($this->varValue) || !$this->varValue[0]) {

            $this->varValue = [['']];
        }

        if ( !\Cache::has('tabindex') ) {

            \Cache::set('tabindex', 1);
        }
        
        if ( !$this->orderByTablename ) {

            $objModule = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $this->strTable ) )->limit(1)->execute( $this->currentRecord );

            if ( $objModule->numRows && $objModule->catalogTablename ) {

                $this->orderByTablename = $objModule->catalogTablename;
            }
        }

        $arrFieldOptions = [];
        $arrOrderByOptions = [];

        if ( !empty( $this->fieldOptionsCallback ) && is_array( $this->fieldOptionsCallback ) ) {

            $this->import( $this->fieldOptionsCallback[0] );

            $arrFieldOptions = $this->{$this->fieldOptionsCallback[0]}->{$this->fieldOptionsCallback[1]}( $this->orderByTablename );
        }

        if ( !empty( $this->orderByOptionsCallback ) && is_array( $this->orderByOptionsCallback ) ) {

            $this->import( $this->orderByOptionsCallback[0] );

            $arrOrderByOptions = $this->{$this->orderByOptionsCallback[0]}->{$this->orderByOptionsCallback[1]}( $this->orderByTablename );
        }

        $tabindex = \Cache::get('tabindex');
        $return = '<table class="tl_optionwizard" id="ctrl_'.$this->strId.'">
              <thead>
                <tr>
                  <th>'.$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['catalogManagerFields'].'</th>
                  <th>'.$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['catalogManagerOrder'].'</th>
                  <th>&nbsp;</th>
                </tr>
              </thead>
              <tbody class="sortable" data-tabindex="'.$tabindex.'">';

                    for ($i=0, $c=count($this->varValue); $i<$c; $i++) {
                        $return .= '
                        <tr>
                          <td><select name="'.$this->strId.'['.$i.'][key]" id="'.$this->strId.'_key_'.$i.'" class="tl_select tl_chosen tl_catalog_widget">' . $this->generateSelectOptions( $arrFieldOptions, 'key', $i ) . '</select></td>
                          <td><select name="'.$this->strId.'['.$i.'][value]" id="'.$this->strId.'_value_'.$i.'" class="tl_select tl_chosen tl_catalog_widget">' . $this->generateSelectOptions( $arrOrderByOptions, 'value', $i ) . '</select></td>';

                                $return .= '
                          <td style="white-space:nowrap;padding-left:3px">';

                                foreach ( $arrButtons as $button ) {

                                    $class = ( $button == 'up' || $button == 'down' ) ? ' class="button-move"' : '';

                                    if ( $button == 'drag' ) {

                                        $return .= \Image::getHtml('drag.gif', '', 'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '"');
                                    }

                                    else {

                                        $return .= '<a href="'.$this->addToUrl('&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord).'"' . $class . ' title="'.specialchars($GLOBALS['TL_LANG']['MSC']['ow_'.$button]).'" onclick="CatalogManager.CatalogOrderByWizard(this,\''.$button.'\',\'ctrl_'.$this->strId.'\');return false">'.\Image::getHtml($button.'.gif', $GLOBALS['TL_LANG']['MSC']['ow_'.$button]).'</a> ';
                                    }
                                }
                                $return .= '</td>
                        </tr>';
                    }
                    \Cache::set('tabindex', $tabindex);
                    return $return.'
              </tbody>
              </table>';
    }


    private function generateSelectOptions( $arrData, $strPrefix, $intIndex ) {

        $strSelectOptions = $this->includeBlankOption ? '<option value>'. ( $this->blankOptionLabel ? $this->blankOptionLabel : '' ) .'</option>' : '';

        foreach ( $arrData as $strKey => $strLabel ) {

            $strSelectOptions .= sprintf( '<option value="%s" %s>%s</option>', $strKey, $this->isCustomSelected( $strKey, $strPrefix, $intIndex ), $strLabel );
        }

        return $strSelectOptions;
    }

    
    private function isCustomSelected( $strKey, $strPrefix, $intIndex ) {

        if ( $this->varValue[$intIndex][$strPrefix] == $strKey ) {

            return 'selected';
        }

        return '';
    }
}