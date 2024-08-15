<?php

namespace Alnv\CatalogManagerBundle;

use Contao\Date;
use Contao\FrontendUser;
use Contao\StringUtil;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;

class FrontendEditingPermission extends CatalogController
{

    public bool $blnDisablePermissions = false;

    protected array $arrGroups = [];

    protected array $arrCatalogManagerPermissions = [];

    public function __construct()
    {
        parent::__construct();

        $this->import(SQLQueryHelper::class);
        $this->import(FrontendUser::class, 'User');
    }

    public function initialize()
    {

        $blnIsBackend = System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest() ?? Request::create(''));

        if ($blnIsBackend) return null;

        $this->arrGroups = Toolkit::deserialize($this->User->allGroups);

        if (empty($this->arrGroups) || !is_array($this->arrGroups)) return null;

        $this->setAttributes();
        $this->setCatalogManagerPermissionFields();
        $this->setMemberGroup();
    }

    public function isAdmin(): bool
    {
        return $this->isAdmin ? true : false;
    }

    public function hasPermission($strMode, $strCatalogname): bool
    {

        if ($this->isAdmin() || $this->blnDisablePermissions) {
            return true;
        }

        if (!$this->hasAccess($strCatalogname)) {
            return false;
        }

        if (!empty($this->{$strCatalogname . 'p'}) && is_array($this->{$strCatalogname . 'p'})) {
            return in_array($strMode, $this->{$strCatalogname . 'p'});
        }

        return false;
    }

    public function hasAccess($strCatalogname): bool
    {

        if ($this->isAdmin() || $this->blnDisablePermissions) {
            return true;
        }

        return $this->{$strCatalogname} ? true : false;
    }

    protected function setCatalogManagerPermissionFields(): void
    {

        $objCatalogs = $this->SQLQueryHelper->getCatalogs();
        while ($objCatalogs->next()) {
            $this->arrCatalogManagerPermissions[] = $objCatalogs->tablename;
            $this->arrCatalogManagerPermissions[] = $objCatalogs->tablename . 'p';
        }
    }

    protected function setAttributes(): void
    {

        $arrUser = $this->User->getData();

        if (!empty($arrUser) && is_array($arrUser)) {
            foreach ($arrUser as $strKey => $varValue) {
                $this->{$strKey} = $varValue;
            }
        }
    }

    protected function setMemberGroup(): void
    {

        $intTime = Date::floorToMinute();

        if (!empty($this->arrGroups) && is_array($this->arrGroups)) {

            foreach ($this->arrGroups as $strID) {

                $objGroup = $this->SQLQueryHelper->SQLQueryBuilder->Database->prepare(sprintf("SELECT * FROM tl_member_group WHERE id = ? AND disable != '1' AND (start='' OR start<='%s') AND (stop='' OR stop>'" . ($intTime + 60) . "')", $intTime))
                    ->limit(1)
                    ->execute($strID);

                if ($objGroup->numRows > 0) {
                    if ($objGroup->isAdmin) {
                        $this->{'isAdmin'} = true;
                    }

                    foreach ($this->arrCatalogManagerPermissions as $strField) {
                        if ($objGroup->{$strField}) {
                            $this->{$strField} = StringUtil::deserialize($objGroup->{$strField});
                        }
                    }
                }
            }
        }
    }
}