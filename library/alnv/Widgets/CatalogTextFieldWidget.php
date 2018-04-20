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
        $GLOBALS['TL_JAVASCRIPT']['catalogAwesompleteWidget'] = 'system/modules/catalog-manager/assets/awesomplete/awesomplete.setup.backend.js';
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

        if ( $this->rgxp == 'url' ) $this->varValue = \Idna::decode( $this->varValue );
        elseif ( $this->rgxp == 'email' || $this->rgxp == 'friendly' ) $this->varValue = \Idna::decodeEmail( $this->varValue );

        return sprintf( '<input type="%s" name="%s" id="ctrl_%s" class="tl_text%s %s ctlg_awesomplete" value="%s"%s list="ctrl_dl_%s" data-startswith="%s"%s onfocus="Backend.getScrollOffset()">%s%s',

            $strType,
            $this->strName,
            $this->strId,
            ( ( $this->strClass != '' ) ? ' ' . $this->strClass : '' ),
            ( version_compare(VERSION, '4.0', '>=') ? '_contao4' : '_contao3' ),
            specialchars( $this->varValue ),
            $this->getAttributes(),
            $this->strId,
            ( $this->startswith ?: '' ),
            ( $this->multiple ? ' data-multiple="1"' : '' ),
            $this->wizard,
            $this->generateDataList());
    }


    protected function flatOptions() {

        $arrOptions = [];

        foreach ( $this->arrOptions as $arrOption ) {

            if ( !is_array( $arrOption ) ) continue;

            if ( Toolkit::isEmpty( $arrOption['value'] ) || !is_string( $arrOption['value'] ) ) continue;

            $arrValues = explode( ',', $arrOption['value'] );

            if ( is_array( $arrValues ) ) {

                foreach ( $arrValues as $strValue ) {

                    if ( !in_array( $strValue, $arrOptions ) ) $arrOptions[] = $strValue;
                }
            }
        }

        return $arrOptions;
    }


    protected function generateDataList() {

        $strOptions = '';
        $arrOptions = $this->flatOptions();

        foreach ( $arrOptions as $strOption ) {

            $strOptions .= sprintf( '<option value="%s">%s</option>', $strOption, $strOption );
        }

        return sprintf( '<datalist id="ctrl_dl_%s">%s</datalist>',

            $this->strId,
            $strOptions
        );
    }
}