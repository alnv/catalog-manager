<?php

namespace CatalogManager;

class OptionsGetter extends CatalogController {

    private $arrField = [];

    public function __construct( $arrField ) {

        parent::__construct();

        $this->arrField = $arrField;

        $this->import( 'Database' );
    }

    public function isForeignKey() {

        if ( $this->arrField['optionsType'] && $this->arrField['optionsType'] == 'useForeignKey' ) {

            return true;
        }

        return false;
    }

    public function getForeignKey() {

        return $this->setForeignKey();
    }

    public function getOptions() {

        switch ( $this->arrField['optionsType'] ) {

            case 'useOptions':

                return $this->getKeyValueOptions();

                break;

            case 'useDbOptions':

                return $this->getDbOptions();

                break;
        }

        return [];
    }

    private function getDbOptions() {

        $arrOptions = [];

        if ( !$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] || !$this->arrField['dbTableValue'] ) {

            return $arrOptions;
        }

        if ( !$this->Database->fieldExists( $this->arrField['dbTableKey'], $this->arrField['dbTable'] ) || !$this->Database->fieldExists( $this->arrField['dbTableValue'], $this->arrField['dbTable'] ) ) {

            return $arrOptions;
        }

        $objDbOptions = $this->Database->prepare( sprintf( 'SELECT `%s`, `%s` FROM %s', $this->arrField['dbTableKey'], $this->arrField['dbTableValue'], $this->arrField['dbTable'] ) )->execute();

        while ( $objDbOptions->next() ) {

            $arrOptions[ $objDbOptions->{$this->arrField['dbTableKey']} ] = $objDbOptions->{$this->arrField['dbTableValue']};
        }

        return $arrOptions;
    }

    private function getKeyValueOptions() {

        $arrOptions = [];

        if ( $this->arrField['options'] ) {

            $arrFieldOptions = deserialize( $this->arrField['options'] );

            if ( !empty( $arrFieldOptions ) && is_array( $arrFieldOptions ) ) {

                foreach ( $arrFieldOptions as $arrOption ) {

                    $arrOptions[ $arrOption['key'] ] = $arrOption['value'];
                }
            }
        }

        return $arrOptions;
    }

    private function setForeignKey() {

        if ( !$this->arrField['dbTable'] || !$this->arrField['dbTableKey'] ) {

            return '';
        }

        return $this->arrField['dbTable'] . '.' . $this->arrField['dbTableKey'];
    }
}