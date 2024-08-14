<?php

namespace Alnv\CatalogManagerBundle;

class CSVBuilder
{

    private array $arrRows = [];

    private string $strDelimiter = '/*';

    private bool $blnShowColumns = false;

    private string $strCSVDestinationPath = TL_ROOT . '/files/';

    public function showColumns()
    {
        $this->blnShowColumns = true;
    }

    public function readRows($arrRow)
    {
        if (!empty($arrRow) && is_array($arrRow)) {
            if ($this->blnShowColumns && !count($this->arrRows)) {
                $this->arrRows[] = array_keys($arrRow);
            }
            $this->arrRows[] = $arrRow;
        }
    }

    public function create($strCSVName)
    {

        $objCSVHandle = fopen($this->strCSVDestinationPath . $strCSVName . '.csv', 'w');
        foreach ($this->arrRows as $arrRow) {
            fputcsv($objCSVHandle, $arrRow, $this->strDelimiter);
        }

        fclose($objCSVHandle);
    }

    public function readCSV($strCSVFile)
    {

        $intRow = 1;

        if (($objCSVHandle = fopen($strCSVFile, 'r')) !== false) {

            while (($arrData = fgetcsv($objCSVHandle, 1000, $this->strDelimiter)) !== FALSE) {
                $iniCount = count($arrData);
                $intRow++;

                for ($c = 0; $c < $iniCount; $c++) {
                    // @todo
                }
            }
            fclose($objCSVHandle);
        }
    }
}