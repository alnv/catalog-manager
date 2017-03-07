<?php

namespace CatalogManager;

class Select {

    
    public static function generate( $arrDCAField, $arrField, $arrCatalog = [] ) {

        $arrDCAField['eval']['chosen'] =  Toolkit::getBooleanByValue( $arrField['chosen'] );
        $arrDCAField['eval']['disabled'] = Toolkit::getBooleanByValue( $arrField['disabled'] );
        $arrDCAField['eval']['multiple'] =  Toolkit::getBooleanByValue( $arrField['multiple'] );
        $arrDCAField['eval']['includeBlankOption'] =  Toolkit::getBooleanByValue( $arrField['includeBlankOption'] );

        if ( $arrField['blankOptionLabel'] && is_string( $arrField['blankOptionLabel'] ) ) {

            $arrDCAField['eval']['blankOptionLabel'] = $arrField['blankOptionLabel'];
        }

        $objOptionGetter = new OptionsGetter( $arrField );
        
        if ( $objOptionGetter->isForeignKey() ) {

            $strForeignKey = $objOptionGetter->getForeignKey();

            if ( $strForeignKey ) {

                $arrDCAField['foreignKey'] = $strForeignKey;
            }
        }

        else {

            $arrDCAField['options'] = $objOptionGetter->getOptions();
        }

        if ( $arrDCAField['eval']['multiple'] ) {

            $arrDCAField['eval']['csv'] = ',';
        }

        if ( $arrField['addRelationWizard'] && in_array( $arrField['optionsType'], [ 'useDbOptions', 'useForeignKey' ] ) && !$arrDCAField['eval']['multiple'] ) {

            if ( $arrField['dbTable'] ) $arrDCAField['wizard'] = [ [ 'CatalogManager\DCACallbacks', 'generateRelationWizard' ] ];

            $arrDCAField['eval']['chosen'] = true;
            $arrDCAField['eval']['submitOnChange'] = true;
            $arrDCAField['eval']['tl_class'] .= $arrDCAField['eval']['tl_class'] ? ' wizard' : 'wizard';
         }

        return $arrDCAField;
    }
}