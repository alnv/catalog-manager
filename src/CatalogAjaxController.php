<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Environment;
use Contao\Input;

class CatalogAjaxController extends CatalogController
{

    protected string $strType = '';

    protected array $arrData = [];

    protected string $strModuleID = '';


    public function __construct()
    {
        $this->import(Environment::class, 'Environment');
        parent::__construct();
    }

    public function setModuleID($strID): void
    {
        $this->strModuleID = $strID ?: '';
    }

    public function setData($arrData): void
    {
        if (!empty($arrData) && is_array($arrData)) {
            $this->arrData = $arrData;
        }
    }

    public function setType($strType): void
    {
        $this->strType = $strType ?: '';
    }

    public function sendJsonData()
    {
        switch ($this->strType) {
            case 'permanent':
                header('Content-Type: application/json');
                echo json_encode($this->arrData, 512);
                exit;
            case 'onAjaxCall':
                if ($this->Environment->get('isAjaxRequest') || (Input::get('ctlg_ajax') && Input::get('ctlg_ajax') == '1')) {
                    if (Input::get('ctlg_module') && $this->strModuleID && Input::get('ctlg_module') != $this->strModuleID) {
                        return null;
                    }
                    header('Content-Type: application/json');
                    echo json_encode($this->arrData, 512);
                    exit;
                }
                break;
        }
    }
}