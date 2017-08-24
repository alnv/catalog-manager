<?php

namespace CatalogManager;

class CatalogSelectWizard extends \Widget {


    protected $strTablename;
    protected $arrMainOptions = [];
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
        $options = $this->getPost( $this->strName );

        if ( is_array( $options ) ) {

            foreach ( $options as $strKey => $option ) {

                if ( $option['key'] == '' ) {

                    unset( $options[ $strKey ] );

                    continue;
                }

                $options[ $strKey ]['key'] = trim( $option['key'] );
                $options[ $strKey ]['value'] = trim( $option['value'] );

                if ($options[ $strKey ]['key'] != '') $this->mandatory = false;
            }
        }

        $options = array_values( $options );
        $varInput = $this->validator( $options );

        if ( !$this->hasErrors() ) {

            $this->varValue = $varInput;
        }

        if ( $mandatory ) {

            $this->mandatory = true;
        }
    }


    public function generate() {

        $this->import('Database');
        $strCommand = 'cmd_' . $this->strField;
        $arrButtons = [ 'copy', 'up', 'down', 'delete' ];

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

        if ( !is_array( $this->varValue ) || !$this->varValue[0] ) {

            $this->varValue = [['']];
        }

        if ( !\Cache::has('tabindex') ) \Cache::set('tabindex', 1);

        $tabindex = \Cache::get('tabindex');

        if ( !empty( $this->mainOptions ) && is_array( $this->mainOptions ) ) {

            $this->import( $this->mainOptions[0] );
            $this->arrMainOptions = $this->{$this->mainOptions[0]}->{$this->mainOptions[1]}( $this );
        }

        $return = '<table class="tl_optionwizard" id="ctrl_'.$this->strId.'">
              <thead>
                <tr>
                  <th>' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][ $this->mainLabel ] . '</th>
                  <th>' . $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER'][ $this->dependedLabel ] . '</th>
                  <th>&nbsp;</th>
                </tr>
              </thead>
              <tbody class="sortable" data-tabindex="'.$tabindex.'">';

                    for ($i=0, $c=count( $this->varValue ); $i<$c; $i++) {
                        $return .= '
                        <tr>
                          <td><select name="'.$this->strId.'['.$i.'][key]" id="'.$this->strId.'_key_'.$i.'" onchange="Backend.autoSubmit(\''. \Input::get('table') .'\')" class="tl_select tl_chosen tl_catalog_widget min-width">' . $this->generateMainOptions( $i ) . '</select></td>
                          <td><select name="'.$this->strId.'['.$i.'][value]" id="'.$this->strId.'_value_'.$i.'" class="tl_select tl_chosen tl_catalog_widget min-width">' . $this->generateDependedOptions( $i ) . '</select></td>';

                                $return .= '
                          <td style="white-space:nowrap;padding-left:3px">';

                                foreach ( $arrButtons as $button ) {

                                    $class = ( $button == 'up' || $button == 'down' ) ? ' class="button-move"' : '';
                                    $return .= '<a href="'.$this->addToUrl( '&amp;'.$strCommand.'='.$button.'&amp;cid='.$i.'&amp;id='.$this->currentRecord ).'"' . $class . ' title="'.specialchars($GLOBALS['TL_LANG']['MSC']['ow_'.$button]).'" onclick="CatalogManager.CatalogOrderByWizard(this,\''.$button.'\',\'ctrl_'.$this->strId.'\');return false">'.\Image::getHtml($button.'.gif', $GLOBALS['TL_LANG']['MSC']['ow_'.$button]).'</a> ';
                                }
                                $return .= '</td>
                        </tr>';
                    }
                    \Cache::set('tabindex', $tabindex);
                    return $return.'
              </tbody>
              </table>';
    }


    protected function generateMainOptions( $intIndex ) {

        $strOptions = $this->includeBlankOption ? '<option value>'. ( $this->blankOptionLabel ? $this->blankOptionLabel : '' ) .'</option>' : '';

        foreach ( $this->arrMainOptions as $strKey => $strLabel ) {

            $strOptions .= sprintf( '<option value="%s" %s>%s</option>', $strKey, $this->isCustomSelected( $strKey, 'key', $intIndex ), $strLabel );
        }

        return $strOptions;
    }


    protected function generateDependedOptions( $intIndex ) {

        $arrOptions = [];
        $strMainOption = $this->varValue[ $intIndex ]['key'];
        $strOptions = $this->includeBlankOption ? '<option value>'. ( $this->blankOptionLabel ? $this->blankOptionLabel : '' ) .'</option>' : '';

        if ( !Toolkit::isEmpty( $strMainOption ) ) {

            if ( !empty( $this->dependedOptions ) && is_array( $this->dependedOptions ) ) {

                $this->import( $this->dependedOptions[0] );
                $arrOptions = $this->{$this->dependedOptions[0]}->{$this->dependedOptions[1]}( $strMainOption, $this );
            }
        }

        if ( is_array( $arrOptions ) && !empty( $arrOptions ) ) {

            foreach ( $arrOptions as $strKey => $strLabel ) {

                $strOptions .= sprintf( '<option value="%s" %s>%s</option>', $strKey, $this->isCustomSelected( $strKey, 'value', $intIndex ), $strLabel );
            }
        }

        return $strOptions;
    }


    protected function isCustomSelected( $strValue, $strPrefix, $intIndex ) {

        if ( $this->varValue[ $intIndex ][ $strPrefix ] == $strValue ) return 'selected';

        return '';
    }
}