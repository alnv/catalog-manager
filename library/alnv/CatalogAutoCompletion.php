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


    public function getAutoCompletionByQuery( $strKeyword ) {

        $arrWords = [];
        $strQuery = '';
        $arrValues = [];
        $strTable = $this->arrField['dbTable'] ? $this->arrField['dbTable'] : 'tl_search_index';
        $strKeyColumn = $this->arrField['dbTableKey'] ? $this->arrField['dbTableKey'] : 'word';

        $arrKeywords = explode( ' ', $strKeyword );

        if ( is_array( $arrKeywords ) && !empty( $arrKeywords ) ) {

            foreach ( $arrKeywords as $intIndex => $strWord ) {

                $strQuery .= ( $intIndex ? ' OR ' : 'WHERE ' ) .
                    sprintf(
                        'LOWER(CAST(%s.`%s` AS CHAR)) REGEXP LOWER(?)',
                        $strTable,
                        $strKeyColumn
                    );

                $arrValues[] = $strWord;
            }
        }

        if ( $strTable == 'tl_search_index' ) $strQuery .= ' AND language = "' . $GLOBALS['TL_LANGUAGE'] . '"';

        $strQuery .= " ORDER BY "
            . "CASE "
            . "WHEN (LOCATE(?, " . $strKeyColumn . ") = 0) THEN 10 "
            . "WHEN " . $strKeyColumn . " = ? THEN 1 "
            . "WHEN " . $strKeyColumn . " LIKE ? THEN 2 "
            . "WHEN " . $strKeyColumn . " LIKE ? THEN 3 "
            . "WHEN " . $strKeyColumn . " LIKE ? THEN 4 "
            . "WHEN " . $strKeyColumn . " LIKE ? THEN 5 "
            . "ELSE 6 "
            . "END "
            . "LIMIT 10";

        $arrValues[] = $strKeyword;
        $arrValues[] = $strKeyword;
        $arrValues[] = "$strKeyword %";
        $arrValues[] = "$strKeyword%";
        $arrValues[] = "%$strKeyword";
        $arrValues[] = "%$strKeyword%";

        $objSuggests = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare( sprintf( 'SELECT * FROM %s ', $strTable ) . $strQuery )->execute( $arrValues );

        if ( $objSuggests->numRows ) {

            while ( $objSuggests->next() ) {

                $arrWords[] = $objSuggests->{$strKeyColumn};
            }
        }

        header('Content-Type: application/json');

        echo json_encode( [

            'word' => $strKeyword,
            'words' => $arrWords

        ], 128 );

        exit;
    }
}