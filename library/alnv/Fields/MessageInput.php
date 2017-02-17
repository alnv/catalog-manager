<?php

namespace CatalogManager;

class MessageInput {

    
    public static function generate( $arrDCAField, $arrField ) {

        unset( $arrDCAField['label'][1] );
        unset( $arrDCAField['exclude'] );
        unset( $arrDCAField['sorting'] );
        unset( $arrDCAField['filter'] );
        unset( $arrDCAField['search'] );
        unset( $arrDCAField['sql'] );

        $arrDCAField['eval']['ctlgMessage'] = $arrField['message'] ? $arrField['message'] : '';

        return $arrDCAField;
    }


    public static function parseValue( $varValue, $arrField, $arrCatalog = [] ) {

        return $arrField['message'] ? $arrField['message'] : '';
    }
}