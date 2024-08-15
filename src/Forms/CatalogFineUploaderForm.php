<?php

namespace Alnv\CatalogManagerBundle\Forms;

use Alnv\CatalogManagerBundle\CatalogFineUploader;
use Contao\Config;
use Contao\Dbafs;
use Contao\Environment;
use Contao\File;
use Contao\FilesModel;
use Contao\FrontendUser;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\UploadableWidgetInterface;
use Contao\Validator;
use Contao\Files;
use Contao\Widget;

class CatalogFineUploaderForm extends Widget implements UploadableWidgetInterface
{

    public $blnStoreFile;

    public $blnUseHomeDir;

    public $bnyUploadFolder;

    public $blnDoNotOverwrite;

    protected $blnSubmitInput = true;

    protected $strTemplate = 'ctlg_form_fine_uploader';

    protected $strPrefix = 'widget widget-fine-uploader';

    public function __construct($arrAttributes = null)
    {

        $strPost = Input::post('name') ? Input::post('name') : '';

        if (Environment::get('isAjaxRequest') && ($arrAttributes['id'] === $strPost || $arrAttributes['name'] === $strPost) && !Input::get('_doNotTriggerAjax')) {
            $this->import(CatalogFineUploader::class);
            $this->CatalogFineUploader->sendAjaxResponse($arrAttributes);

            return null;
        }

        parent::__construct($arrAttributes);
    }

    public function __set($strKey, $varValue)
    {

        switch ($strKey) {
            case 'maxsize':
                $this->arrConfiguration['maxsize'] = $varValue;
                break;
            case 'mandatory':
                if ($varValue) {
                    $this->arrAttributes['required'] = 'required';
                } else {
                    unset($this->arrAttributes['required']);
                }
                parent::__set($strKey, $varValue);
                break;
            case 'fSize':
                if ($varValue > 0) {
                    $this->arrAttributes['size'] = $varValue;
                }
                break;
            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }

    public function validate()
    {

        $arrFiles = null;

        if ($this->multiple && isset($_SESSION['FILES'])) {
            $arrFiles = isset($_SESSION['FILES'][$this->strName]) && is_array($_SESSION['FILES'][$this->strName]) ? $_SESSION['FILES'][$this->strName][0] : null;
        }

        if (!$this->multiple && isset($_SESSION['FILES'])) {
            $arrFiles = isset($_SESSION['FILES'][$this->strName]) && is_array($_SESSION['FILES'][$this->strName]) ? $_SESSION['FILES'][$this->strName] : null;
        }

        if (!$arrFiles) {
            if ($this->mandatory) {
                if ($this->strLabel == '') {
                    $this->addError($GLOBALS['TL_LANG']['ERR']['mdtryNoLabel']);
                } else {
                    $this->addError(sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel));
                }
            }
        }
    }

    public function upload()
    {

        $arrReturn = ['success' => true, 'error' => '', 'preventRetry' => true,];

        if (!isset($_FILES[$this->strName]) || empty($_FILES[$this->strName]['name'])) {

            if ($this->mandatory) {

                if ($this->strLabel == '') {
                    $arrReturn['error'] = $GLOBALS['TL_LANG']['ERR']['mdtryNoLabel'];
                    $arrReturn['success'] = false;
                    return $arrReturn;

                } else {
                    $arrReturn['error'] = sprintf($GLOBALS['TL_LANG']['ERR']['mandatory'], $this->strLabel);
                    $arrReturn['success'] = false;
                    return $arrReturn;
                }
            }

            $arrReturn['success'] = false;

            return $arrReturn;
        }

        $arrFile = $_FILES[$this->strName];
        $intMaxSizeKb = $this->getReadableSize($this->maxsize);

        try {
            $arrFile['name'] = StringUtil::sanitizeFileName($arrFile['name']);
        } catch (\InvalidArgumentException $objError) {
            $arrReturn['error'] = $GLOBALS['TL_LANG']['ERR']['filename'];
            $arrReturn['success'] = false;
            return $arrReturn;
        }

        if (!Validator::isValidFileName($arrFile['name'])) {
            $arrReturn['error'] = $GLOBALS['TL_LANG']['ERR']['filename'];
            $arrReturn['success'] = false;
            return $arrReturn;
        }

        if (!is_uploaded_file($arrFile['tmp_name'])) {

            if ($arrFile['error'] == 1 || $arrFile['error'] == 2) {
                $arrReturn['error'] = sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], $intMaxSizeKb);
                $arrReturn['success'] = false;
                return $arrReturn;
            }

            if ($arrFile['error'] == 3) {
                $arrReturn['error'] = sprintf($GLOBALS['TL_LANG']['ERR']['filepartial'], $arrFile['name']);
                $arrReturn['success'] = false;
                return $arrReturn;
            }

            if ($arrFile['error'] > 0) {
                $arrReturn['error'] = sprintf($GLOBALS['TL_LANG']['ERR']['fileerror'], $arrFile['error'], $arrFile['name']);
                $arrReturn['success'] = false;
                return $arrReturn;
            }

            unset($_FILES[$this->strName]);
            $arrReturn['success'] = false;

            return $arrReturn;
        }

        if ($this->maxsize > 0 && $arrFile['size'] > $this->maxsize) {

            $arrReturn['error'] = sprintf($GLOBALS['TL_LANG']['ERR']['filesize'], $intMaxSizeKb);
            unset($_FILES[$this->strName]);
            $arrReturn['success'] = false;

            return $arrReturn;
        }

        $objFile = new File($arrFile['name'], true);
        $arrUploadedTypes = StringUtil::trimsplit(',', strtolower($this->extensions));

        if (!in_array($objFile->extension, $arrUploadedTypes)) {

            $arrReturn['error'] = sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension);
            unset($_FILES[$this->strName]);
            $arrReturn['success'] = false;

            return $arrReturn;
        }

        if (isset($arrReturn['tmp_name']) && $arrReturn['tmp_name']) {
            if (($arrImageSize = @getimagesize($arrReturn['tmp_name'])) != false) {

                if ($arrImageSize[0] > Config::get('imageWidth')) {
                    $arrReturn['error'] = sprintf($GLOBALS['TL_LANG']['ERR']['filewidth'], $arrFile['name'], Config::get('imageWidth'));
                    unset($_FILES[$this->strName]);
                    $arrReturn['success'] = false;
                    return $arrReturn;
                }

                if ($arrImageSize[1] > Config::get('imageHeight')) {
                    $arrReturn['error'] = sprintf($GLOBALS['TL_LANG']['ERR']['fileheight'], $arrFile['name'], Config::get('imageHeight'));
                    unset($_FILES[$this->strName]);
                    $arrReturn['success'] = false;
                    return $arrReturn;
                }
            }
        }

        if (!isset($_SESSION['FILES'])) {
            $_SESSION['FILES'] = [];
        }

        if (!isset($_SESSION['FILES'][$this->strName])) {
            $_SESSION['FILES'][$this->strName] = [];
        }

        if (!$this->hasErrors()) {

            if (is_null($_SESSION['FILES'][$this->strName])) {
                $_SESSION['FILES'][$this->strName] = [];
            }

            if ($this->blnStoreFile) {
                $bnyUploadFolder = $this->bnyUploadFolder;
                if ($this->blnUseHomeDir && System::getContainer()->get('contao.security.token_checker')->hasFrontendUser()) {
                    $this->import(FrontendUser::class, 'User');
                    if ($this->User->assignDir && $this->User->homeDir) {
                        $bnyUploadFolder = $this->User->homeDir;
                    }
                }

                $objUploadFolder = FilesModel::findByUuid($bnyUploadFolder);

                if ($objUploadFolder === null) {

                    unset($_FILES[$this->strName]);
                    $arrReturn['success'] = false;
                    return $arrReturn;
                }

                $strRootDir = System::getContainer()->getParameter('kernel.project_dir');
                $strUploadFolder = $objUploadFolder->path;

                if ($strUploadFolder != '' && is_dir($strRootDir . '/' . $strUploadFolder)) {

                    $this->import(Files::class);

                    if ($this->blnDoNotOverwrite && file_exists($strRootDir . '/' . $strUploadFolder . '/' . $arrFile['name'])) {
                        $intOffset = 1;
                        $arrAll = scandir($strRootDir . '/' . $strUploadFolder);
                        $arrFiles = preg_grep('/^' . preg_quote($objFile->filename, '/') . '.*\.' . preg_quote($objFile->extension, '/') . '/', $arrAll);

                        foreach ($arrFiles as $strFile) {
                            if (preg_match('/__[0-9]+\.' . preg_quote($objFile->extension, '/') . '$/', $strFile)) {
                                $strFile = str_replace('.' . $objFile->extension, '', $strFile);
                                $intValue = intval(substr($strFile, (strrpos($strFile, '_') + 1)));
                                $intOffset = max($intOffset, $intValue);
                            }
                        }
                        $arrFile['name'] = str_replace($objFile->filename, $objFile->filename . '__' . ++$intOffset, $arrFile['name']);
                    }

                    $this->Files->move_uploaded_file($arrFile['tmp_name'], $strUploadFolder . '/' . $arrFile['name']);
                    $this->Files->chmod($strUploadFolder . '/' . $arrFile['name'], Config::get('defaultFileChmod'));
                    $strUuid = null;
                    $strFile = $strUploadFolder . '/' . $arrFile['name'];

                    if (Dbafs::shouldBeSynchronized($strFile)) {

                        $objModel = FilesModel::findByPath($strFile);

                        if ($objModel !== null) {

                            $objModel->tstamp = time();
                            $objModel->path = $strFile;
                            $objModel->hash = md5_file($strRootDir . '/' . $strFile);
                            $objModel->save();

                            $strUuid = StringUtil::binToUuid($objModel->uuid);
                        } else {

                            $strUuid = StringUtil::binToUuid(Dbafs::addResource($strFile)->uuid);
                        }

                        Dbafs::updateFolderHashes($strUploadFolder);
                    }

                    $_SESSION['FILES'][$this->strName][] = [
                        'name' => $arrFile['name'],
                        'type' => $arrFile['type'],
                        'tmp_name' => $strRootDir . '/' . $strFile,
                        'error' => $arrFile['error'],
                        'size' => $arrFile['size'],
                        'uploaded' => true,
                        'uuid' => $strUuid
                    ];

                    // $this->log('File "' . $strUploadFolder . '/' . $arrFile['name'] . '" has been uploaded', __METHOD__, TL_FILES);
                }
            }
        }

        unset($_FILES[$this->strName]);
        return $arrReturn;
    }

    public function parse($arrAttributes = null)
    {
        $this->multiple = json_encode($this->arrConfiguration['multiple']);
        $this->extensions = json_encode(explode(',', $this->arrConfiguration['extensions']));
        $this->maxsize = $this->arrConfiguration['maxsize'] ? $this->arrConfiguration['maxsize'] : '0';

        return parent::parse($arrAttributes);
    }


    public function generate()
    {
    }
}