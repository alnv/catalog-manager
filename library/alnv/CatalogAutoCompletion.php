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
}