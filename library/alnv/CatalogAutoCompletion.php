<?php

namespace CatalogManager;

class CatalogAutoCompletion extends OptionsGetter {


    public function getOptions() {

        switch ( $this->arrField['autoCompletionType'] ) {

            case 'useDbOptions':

                return $this->getDbOptions();

                break;
        }

        return [];
    }


    public function getTableEntities() {

        switch ( $this->arrField['autoCompletionType'] ) {

            case 'useDbOptions':

                if ( !$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists( $this->arrField['dbTable'] ) ) {

                    return null;
                }

                if ( !$this->SQLQueryHelper->SQLQueryBuilder->Database->fieldExists( $this->arrField['dbTableKey'], $this->arrField['dbTable'] ) ) {

                    return null;
                }

                return $this->getResults( true );

                break;
        }

        return null;
    }


    public function getAutoCompletionByQuery( $strWord ) {

        $arrWords = [];

        $strQuery = '';
        $strValueColumn = $this->arrField['dbTableValue'] ? $this->arrField['dbTableValue'] : 'word';
        $strTable = $this->arrField['dbTable'] ? $this->arrField['dbTable'] : 'tl_search_index';
        $strKeyColumn = $this->arrField['dbTableKey'] ? $this->arrField['dbTableKey'] : 'word';

        if ( $strTable == 'tl_search_index' ) $strQuery .= ' AND language = "' . $GLOBALS['TL_LANGUAGE'] . '"';

        $strQuery .= " ORDER BY "
            . "CASE "
            . "WHEN (LOCATE(?, " . $strValueColumn . ") = 0) THEN 10 "
            . "WHEN " . $strValueColumn . " = ? THEN 1 "
            . "WHEN " . $strValueColumn . " LIKE ? THEN 2 "
            . "WHEN " . $strValueColumn . " LIKE ? THEN 3 "
            . "WHEN " . $strValueColumn . " LIKE ? THEN 4 "
            . "WHEN " . $strValueColumn . " LIKE ? THEN 5 "
            . "ELSE 6 "
            . "END "
            . "LIMIT 10";

        $objSuggests = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf(

            'SELECT * FROM %s WHERE LOWER(CAST(%s.`%s` AS CHAR)) REGEXP LOWER(?) OR LOWER(CAST(%s.`%s` AS CHAR)) REGEXP LOWER(?)' . $strQuery,
            $strTable,
            $strTable,
            $strKeyColumn,
            $strTable,
            $strValueColumn

        ) )->execute( $strWord, $strWord, $strWord, $strWord, $strWord, $strWord, $strWord, $strWord );

        if ( $objSuggests->numRows ) {

            while ( $objSuggests->next() ) {

                $arrWords[] = $objSuggests->{$strValueColumn};
            }
        }

        header('Content-Type: application/json');

        echo json_encode( [

            'word' => $strQuery,
            'words' => $arrWords

        ], 128 );
    }
}