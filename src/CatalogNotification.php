<?php

namespace Alnv\CatalogManagerBundle;

use Alnv\CatalogManagerBundle\Fields\DateInput;
use Alnv\CatalogManagerBundle\Fields\Textarea;
use Alnv\CatalogManagerBundle\Fields\Checkbox;
use Alnv\CatalogManagerBundle\Fields\Radio;
use Alnv\CatalogManagerBundle\Fields\Select;
use Alnv\CatalogManagerBundle\Fields\Upload;
use Alnv\CatalogManagerBundle\Fields\Number;
use Alnv\CatalogManagerBundle\Fields\DbColumn;
use Contao\Environment;
use Contao\Idna;

class CatalogNotification extends CatalogController
{

    protected array $arrCatalog = [];

    protected $strItemID = null;

    protected $objModule = null;

    protected bool $blnEnable = false;

    protected array $arrCatalogFields = [];

    public function __construct($objModule, $strID = null)
    {

        parent::__construct();

        $this->strItemID = $strID;
        $this->objModule = $objModule;
        $this->import(SQLQueryHelper::class);
        $this->import(CatalogFieldBuilder::class);

        $this->blnEnable = (class_exists('NotificationCenter\Model\Notification') && $this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists('tl_nc_notification'));

        if ($this->blnEnable && $this->objModule->catalogTablename) {
            $this->CatalogFieldBuilder->initialize($this->objModule->catalogTablename);
            $this->arrCatalog = $this->CatalogFieldBuilder->getCatalog();
            $this->arrCatalogFields = $this->CatalogFieldBuilder->getCatalogFields($this->objModule->catalogTablename, false, null);
        }
    }

    public function notifyOnDelete($intNotificationId, $arrData = [])
    {

        if (!$this->blnEnable) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk($intNotificationId);

        if ($objNotification === null) {
            // $this->log( 'The notification was not found ID ' . $intNotificationId , __METHOD__, TL_ERROR );
            return;
        }

        $arrTokens = $this->setDataTokens($arrData);
        $arrTokens = $this->getOldData($arrTokens);
        $arrTokens['domain'] = $this->getDomain();
        $arrTokens['admin_email'] = $this->getAdminEmail();

        $objNotification->send($arrTokens, $GLOBALS['TL_LANGUAGE']);
    }

    public function notifyOnUpdate($intNotificationId, $arrData = [])
    {

        if (!$this->blnEnable) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk($intNotificationId);

        if ($objNotification === null) {
            // $this->log('The notification was not found ID ' . $intNotificationId, __METHOD__, TL_ERROR);
            return;
        }

        $arrTokens = $this->setDataTokens($arrData);
        $arrTokens = $this->getOldData($arrTokens);
        $arrTokens['domain'] = $this->getDomain();
        $arrTokens['admin_email'] = $this->getAdminEmail();

        if (isset($GLOBALS['TL_HOOKS']['catalogManagerNotifyOnUpdate']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerNotifyOnUpdate'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerNotifyOnUpdate'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $blnNoSend = $this->{$arrCallback[0]}->{$arrCallback[1]}($arrData, $arrTokens, $this);
                    if ($blnNoSend) {
                        return;
                    }
                }
            }
        }

        $objNotification->send($arrTokens, $GLOBALS['TL_LANGUAGE']);
    }


    public function notifyOnInsert($intNotificationId, $arrData = [])
    {

        if (!$this->blnEnable) return;

        $objNotification = \NotificationCenter\Model\Notification::findByPk($intNotificationId);

        if ($objNotification === null) {
            // $this->log('The notification was not found ID ' . $intNotificationId, __METHOD__, TL_ERROR);
            return;
        }

        $arrTokens = $this->setDataTokens($arrData);
        $arrTokens['domain'] = $this->getDomain();
        $arrTokens['admin_email'] = $this->getAdminEmail();

        if (isset($GLOBALS['TL_HOOKS']['catalogManagerNotifyOnInsert']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerNotifyOnInsert'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerNotifyOnInsert'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $blnNoSend = $this->{$arrCallback[0]}->{$arrCallback[1]}($arrData, $arrTokens, $this);
                    if ($blnNoSend) {
                        return;
                    }
                }
            }
        }

        $objNotification->send($arrTokens, $GLOBALS['TL_LANGUAGE']);
    }


    protected function getOldData($arrTokens = [])
    {

        if ($this->strItemID && $this->SQLQueryHelper->SQLQueryBuilder->Database->tableExists($this->objModule->catalogTablename)) {
            $objData = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare(sprintf('SELECT * FROM %s WHERE id = ?', $this->objModule->catalogTablename))->limit(1)->execute($this->strItemID);
            if ($objData->numRows) {
                $arrData = $objData->row();
                if (!empty($arrData) && is_array($arrData)) {
                    foreach ($arrData as $strKey => $strValue) {
                        $arrTokens['rawOld_' . $strKey] = $strValue;
                        if (is_array($this->arrCatalogFields[$strKey])) {
                            $varValue = $this->parseCatalogValues($strValue, $this->arrCatalogFields[$strKey], $arrData);
                            if (is_array($varValue)) $varValue = implode(',', $varValue);
                            $arrTokens['cleanOld_' . $strKey] = $varValue;
                        }
                    }
                }
            }
        }

        return $arrTokens;
    }

    protected function setDataTokens($arrData)
    {

        $arrTokens = [];

        if (!empty($arrData) && is_array($arrData)) {
            foreach ($arrData as $strKey => $strValue) {
                if (is_array($this->arrCatalogFields[$strKey])) {
                    $varValue = $this->parseCatalogValues($strValue, $this->arrCatalogFields[$strKey], $arrData);
                    if (is_array($varValue)) $varValue = implode(',', $varValue);
                    $arrTokens['clean_' . $strKey] = $varValue;
                    foreach ($this->arrCatalogFields[$strKey] as $strOptionName => $strOptionValue) {
                        $arrTokens['field_' . $strKey . '_' . $strOptionName] = $strOptionValue;
                    }
                }
                $arrTokens['raw_' . $strKey] = $strValue;
            }
        }

        if (!empty($this->arrCatalog) && is_array($this->arrCatalog)) {
            foreach ($this->arrCatalog as $strOptionName => $strOptionValue) {
                $arrTokens['table_' . $strOptionName] = $strOptionValue;
            }
        }

        if (isset($GLOBALS['TL_HOOKS']['catalogManagerSetCustomNotificationTokens']) && is_array($GLOBALS['TL_HOOKS']['catalogManagerSetCustomNotificationTokens'])) {
            foreach ($GLOBALS['TL_HOOKS']['catalogManagerSetCustomNotificationTokens'] as $arrCallback) {
                if (is_array($arrCallback)) {
                    $this->import($arrCallback[0]);
                    $arrTokens = $this->{$arrCallback[0]}->{$arrCallback[1]}($arrTokens, $arrData, $this->objModule);
                }
            }
        }

        return $arrTokens;
    }

    protected function getAdminEmail()
    {
        return ($GLOBALS['TL_ADMIN_EMAIL'] ?? '');
    }

    protected function getDomain()
    {
        return Idna::decode(Environment::get('host'));
    }

    protected function parseCatalogValues($varValue, $arrField, &$arrCatalog)
    {

        switch ($arrField['type']) {
            case 'upload':
                if (is_null($varValue)) return '';
                return Upload::parseAttachment($varValue, $arrField, $arrCatalog);
            case 'select':
                return Select::parseValue($varValue, $arrField, $arrCatalog);
            case 'checkbox':
                return Checkbox::parseValue($varValue, $arrField, $arrCatalog);
            case 'radio':
                return Radio::parseValue($varValue, $arrField, $arrCatalog);
            case 'date':
                return DateInput::parseValue($varValue, $arrField, $arrCatalog);
            case 'number':
                return Number::parseValue($varValue, $arrField, $arrCatalog);
            case 'textarea':
                return Textarea::parseValue($varValue, $arrField, $arrCatalog);
            case 'dbColumn':
                return DbColumn::parseValue($varValue, $arrField, $arrCatalog);
        }

        return $varValue;
    }
}