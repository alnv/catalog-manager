<?php

namespace CatalogManager;

class Textarea {

    public static function generate( $arrDCAField, $arrField ) {

        if ( $arrField['rte'] ) {

            $arrDCAField['eval']['rte'] = $arrField['rte'];
        }

        if ( $arrField['cols'] ) {

            $arrDCAField['eval']['cols'] = $arrField['cols'];
        }

        if ( $arrField['rows'] ) {

            $arrDCAField['eval']['rows'] = $arrField['rows'];
        }

        if ( $arrField['readonly'] ) {

            $arrDCAField['eval']['readonly'] = $arrField['readonly'] ? true : false;
        }

        return $arrDCAField;
    }
}