<?php

namespace CatalogManager;

class Upload {

    public static function generate( $arrDCAField, $arrField ) {

        $blnMultiple = Toolkit::getBooleanByValue( $arrField['multiple'] );

        $arrDCAField['eval']['files'] = true;
        $arrDCAField['eval']['multiple'] = $blnMultiple;
        $arrDCAField['eval']['filesOnly'] = Toolkit::getBooleanByValue( $arrField['filesOnly'] );

        if ( $blnMultiple ) {

            $arrDCAField['eval']['fieldType'] = 'checkbox';
        }

        else {

            $arrDCAField['eval']['fieldType'] = 'radio';
        }

        if ( $arrField['extensions'] ) {

            $arrDCAField['eval']['extensions'] = $arrField['extensions'];
        }

        if ( $arrField['path'] ) {

            $arrDCAField['eval']['path'] = $arrField['path'];
        }

        return $arrDCAField;
    }
}