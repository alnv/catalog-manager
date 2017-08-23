<?php

namespace CatalogManager;

class CatalogTextFieldWidget extends \Widget {


    protected $blnSubmitInput = true;
    protected $blnForAttribute = true;
    protected $strTemplate = 'be_widget';


    public function __construct( $arrAttributes = null ) {

        parent::__construct( $arrAttributes );

        if ( $this->multiple ) $this->blnForAttribute = false;

        $GLOBALS['TL_CSS']['catalogAwesomplete'] = 'system/modules/catalog-manager/assets/awesomplete/awesomplete.css';
        $GLOBALS['TL_JAVASCRIPT']['catalogAwesompleteFramework'] = $GLOBALS['TL_CONFIG']['debugMode'] ? 'system/modules/catalog-manager/assets/awesomplete/awesomplete.js' : 'system/modules/catalog-manager/assets/awesomplete/awesomplete.min.js';
        $GLOBALS['TL_JAVASCRIPT']['catalogAwesompleteWidget'] = 'system/modules/catalog-manager/assets/awesomplete/catalogAwesomplete.js';
    }


    public function __set( $strKey, $varValue ) {

        switch ( $strKey ) {

            case 'maxlength':

                if ( $varValue > 0 ) {

                    $this->arrAttributes['maxlength'] = $varValue;
                }

                break;

            case 'mandatory':

                if ($varValue) {

                    $this->arrAttributes['required'] = 'required';
                }

                else {

                    unset( $this->arrAttributes['required'] );
                }

                parent::__set( $strKey, $varValue );

                break;

            case 'placeholder':

                $this->arrAttributes['placeholder'] = $varValue;

                break;

            case 'options':

                $this->arrOptions = deserialize( $varValue );

                break;

            default:

                parent::__set( $strKey, $varValue );

                break;
        }
    }


    protected function validator( $varInput ) {

        if ( is_array( $varInput ) ) {

            return parent::validator( $varInput );
        }

        if ( !$this->multiple ) {

            if ( $this->rgxp == 'url' ) {

                $varInput = \Idna::encodeUrl( $varInput );
            }

            elseif ( $this->rgxp == 'email' || $this->rgxp == 'friendly' ) {

                $varInput = \Idna::encodeEmail( $varInput );
            }
        }

        return parent::validator( $varInput );
    }


    public function generate() {

        $strType = $this->hideInput ? 'password' : 'text';

        if ( empty( $this->arrOptions ) || !is_array( $this->arrOptions ) ) {

            $this->arrOptions = [[]];
        }

        if ( !$this->multiple ) {

            if ( $this->rgxp == 'url' ) $this->varValue = \Idna::decode( $this->varValue );
            elseif ( $this->rgxp == 'email' || $this->rgxp == 'friendly' ) $this->varValue = \Idna::decodeEmail( $this->varValue );

            return sprintf( '<input type="%s" name="%s" id="ctrl_%s" class="tl_text%s ctlg_awesomplete" value="%s"%s list="ctrl_dl_%s" data-startswith="%s" onfocus="Backend.getScrollOffset()">%s%s',

                $strType,
                $this->strName,
                $this->strId,
                ( ( $this->strClass != '' ) ? ' ' . $this->strClass : '' ),
                specialchars( $this->varValue ),
                $this->getAttributes(),
                $this->strId,
                ( $this->startswith ?: '' ),
                $this->wizard,
                $this->generateDataList());
        }

        if ( !$this->size ) return '';
        if ( !is_array( $this->varValue ) ) $this->varValue = [ $this->varValue ];

        $arrFields = [];

        for ( $i=0; $i < $this->size; $i++ ) {

            $arrFields[] = sprintf('<input type="%s" name="%s[]" id="ctrl_%s" class="tl_text_%s" value="%s"%s onfocus="Backend.getScrollOffset()">',

                $strType,
                $this->strName,
                $this->strId.'_'.$i,
                $this->size,
                specialchars( @$this->varValue[ $i ] ),
                $this->getAttributes());
        }

        return sprintf('<div id="ctrl_%s"%s>%s</div>%s',

            $this->strId,
            ( ($this->strClass != '' ) ? ' class="' . $this->strClass . '"' : ''),
            implode( ' ', $arrFields ),
            $this->wizard);
    }


    protected function generateDataList() {

        $strOptions = '';

        foreach ( $this->arrOptions as $arrOption ) {

            $strOptions .= sprintf( '<option>%s</option>', $arrOption['value'] );
        }

        return sprintf( '<datalist id="ctrl_dl_%s">%s</datalist>',

            $this->strId,
            $strOptions
        );
    }
}