<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Input;
use Contao\Session;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class CatalogInput extends CatalogController
{

    protected string $strFormId;

    public function __construct()
    {
        parent::__construct();

        $this->strFormId = md5('tl_filter');
        $this->import(Input::class);
    }

    protected function getPostCookie($strName)
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));
        $objSession = Session::getInstance();
        $strActiveValue = $this->Input->post($strName);
        $arrEditingMode = preg_grep('/^act(\d+)/i', array_keys($_GET));
        $arrPagination = preg_grep('/^page_e(\d+)/i', array_keys($_GET));

        if ($this->Input->post('FORM_SUBMIT') == $this->strFormId) $objSession->set($strName, $strActiveValue);

        if (!empty($arrPagination) || (Toolkit::isEmpty($strActiveValue) && !Toolkit::isEmpty($objSession->get($strName)))) {
            if (!$blnIsBackend) {
                $strActiveValue = $objSession->get($strName);
            }
        }

        if (!empty($arrEditingMode)) {
            $strActiveValue = $this->Input->post($strName);
        }

        return $strActiveValue;
    }

    public function post($strName)
    {

        $strPostCookie = $this->getPostCookie($strName);

        if (!is_null($strPostCookie) && $strPostCookie != '') return $this->parseValue($strPostCookie);

        return '';
    }

    public function get($strName)
    {

        $strGet = $this->Input->get($strName);

        if (!is_null($strGet) && $strGet != '') return $this->parseValue($strGet);

        return '';
    }

    public function getActiveValue($strName)
    {

        if ($this->get($strName) != '') return $this->get($strName);

        $strPost = $this->post($strName);

        if ($strPost != '') return $strPost;

        return '';
    }

    public function getValue($strName)
    {

        if ($this->get($strName) != '') return $this->get($strName);

        $strPost = $this->Input->post($strName);

        if (!Toolkit::isEmpty($strPost)) return $strPost;

        return '';
    }

    protected function parseValue($varValues)
    {

        if (is_array($varValues) && !empty($varValue)) {
            foreach ($varValues as $intIndex => $strValue) {
                $varValues[$intIndex] = $strValue;
            }
            return $varValues;
        }

        if (is_string($varValues) && $varValues != '') {
            return $varValues;
        }

        return $varValues;
    }
}