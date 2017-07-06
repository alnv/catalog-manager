<?php

namespace CatalogManager;

class CatalogFineUploaderForm extends \Widget implements \uploadable {


    public $blnStoreFile;
    public $blnUseHomeDir;
    public $bnyUploadFolder;
    public $blnDoNotOverwrite;

    protected $blnSubmitInput = true;
    protected $strTemplate = 'ctlg_form_fine_uploader';
    protected $strPrefix = 'widget widget-fine-uploader';


    public function __construct( $arrAttributes = null ) {

        $strPost = \Input::post('name') ? \Input::post('name') : '';

        if ( \Environment::get('isAjaxRequest') && $strPost && ( $arrAttributes['id'] === $strPost || $arrAttributes['name'] === $strPost ) && !\Input::get('_doNotTriggerAjax') ) {

            $this->import('CatalogFineUploader');
            $this->CatalogFineUploader->sendAjaxResponse( $arrAttributes );
            
            return null;
        }

        parent::__construct( $arrAttributes );
    }


    public function __set( $strKey, $varValue ) {

        switch ( $strKey ) {

            case 'maxlength':

                $this->arrConfiguration['maxlength'] = $varValue;

                break;

            case 'mandatory':

                if ( $varValue ) {

                    $this->arrAttributes['required'] = 'required';

                } else {

                    unset( $this->arrAttributes['required'] );
                }

                parent::__set( $strKey, $varValue );

                break;

            case 'fSize':

                if ( $varValue > 0 ) {

                    $this->arrAttributes['size'] = $varValue;
                }

                break;

            default:

                parent::__set( $strKey, $varValue );

                break;
        }
    }


    public function upload() {

        $arrReturn = [ 'success' => true ];

        if ( !isset( $_FILES[$this->strName] ) || empty( $_FILES[$this->strName]['name'] ) ) {

            if ( $this->mandatory ) {

                if ( $this->strLabel == '' ) {

                    $arrReturn['success'] = false;
                    return $arrReturn;

                } else {

                    $arrReturn['success'] = false;
                    return $arrReturn;
                }
            }

            $arrReturn['success'] = false;
            return $arrReturn;
        }

        $arrFile = $_FILES[ $this->strName ];
        $intMaxSizeKb = $this->getReadableSize( $this->maxlength );

        try {

            $arrFile['name'] = \StringUtil::sanitizeFileName( $arrFile['name'] );
        }

        catch ( \InvalidArgumentException $objError ) {

            // @todo message

            $arrReturn['success'] = false;
            return $arrReturn;
        }

        if ( !\Validator::isValidFileName( $arrFile['name'] ) ) {

            // @todo message

            $arrReturn['success'] = false;
            return $arrReturn;
        }

        if ( !is_uploaded_file( $arrFile['tmp_name'] ) ) {

            if ( $arrFile['error'] == 1 || $arrFile['error'] == 2 ) {

                // @todo message
            }

            if ( $arrFile['error'] == 3 ) {

                // @todo message
            }

            if ( $arrFile['error'] > 0 ) {

                // @todo message
            }

            unset( $_FILES[ $this->strName ] );
            $arrReturn['success'] = false;
            return $arrReturn;
        }

        if ( $this->maxlength > 0 && $arrFile['size'] > $this->maxlength ) {

            // @todo message

            unset( $_FILES[ $this->strName ] );
            $arrReturn['success'] = false;
            return $arrReturn;
        }

        $objFile = new \File( $arrFile['name'], true );
        $arrUploadedTypes = trimsplit( ',', strtolower( $this->extensions ) );

        if ( !in_array( $objFile->extension, $arrUploadedTypes ) ) {

            // @todo message

            unset( $_FILES[ $this->strName ] );
            $arrReturn['success'] = false;
            return $arrReturn;
        }

        if ( ( $arrImageSize = @getimagesize( $arrReturn['tmp_name'] ) ) != false ) {

            if ( $arrImageSize[0] > \Config::get('imageWidth') ) {

                // @todo message

                unset( $_FILES[ $this->strName ] );
                $arrReturn['success'] = false;
                return $arrReturn;
            }

            if ( $arrImageSize[1] > \Config::get('imageHeight') ) {

                // @todo message

                unset( $_FILES[ $this->strName ] );
                $arrReturn['success'] = false;
                return $arrReturn;
            }
        }

        if ( !$this->hasErrors() ) {

            if ( is_null( $_SESSION['FILES'][ $this->strName ] ) ) {

                $_SESSION['FILES'][ $this->strName ] = [];
            }

            if ( $this->blnStoreFile ) {

                $bnyUploadFolder = $this->bnyUploadFolder;

                if ( $this->blnUseHomeDir && FE_USER_LOGGED_IN ) {

                    $this->import('FrontendUser', 'User');

                    if ($this->User->assignDir && $this->User->homeDir) {

                        $bnyUploadFolder = $this->User->homeDir;
                    }
                }

                $objUploadFolder = \FilesModel::findByUuid( $bnyUploadFolder );

                if ( $objUploadFolder === null ) {

                    unset( $_FILES[ $this->strName ] );
                    $arrReturn['success'] = false;
                    return $arrReturn;
                }

                $strUploadFolder = $objUploadFolder->path;

                if ( $strUploadFolder != '' && is_dir( TL_ROOT . '/' . $strUploadFolder ) ) {

                    $this->import('Files');

                    if ( $this->blnDoNotOverwrite && file_exists( TL_ROOT . '/' . $strUploadFolder . '/' . $arrFile['name'] ) ) {

                        $intOffset = 1;
                        $arrAll = scan( TL_ROOT . '/' . $strUploadFolder );
                        $arrFiles = preg_grep( '/^' . preg_quote( $objFile->filename, '/' ) . '.*\.' . preg_quote( $objFile->extension, '/' ) . '/', $arrAll );

                        foreach ( $arrFiles as $strFile ) {

                            if ( preg_match( '/__[0-9]+\.' . preg_quote( $objFile->extension, '/' ) . '$/', $strFile ) ) {

                                $strFile = str_replace( '.' . $objFile->extension, '', $strFile );
                                $intValue = intval( substr( $strFile, ( strrpos( $strFile, '_' ) + 1 ) ) );
                                $intOffset = max( $intOffset, $intValue );
                            }
                        }

                        $arrFile['name'] = str_replace( $objFile->filename, $objFile->filename . '__' . ++$intOffset, $arrFile['name'] );
                    }

                    $this->Files->move_uploaded_file( $arrFile['tmp_name'], $strUploadFolder . '/' . $arrFile['name'] );
                    $this->Files->chmod( $strUploadFolder . '/' . $arrFile['name'], \Config::get('defaultFileChmod') );
                    $strUuid = null;
                    $strFile = $strUploadFolder . '/' . $arrFile['name'];

                    if ( \Dbafs::shouldBeSynchronized( $strFile ) ) {

                        $objModel = \FilesModel::findByPath( $strFile );

                        if ( $objModel !== null ) {

                            $objModel->tstamp = time();
                            $objModel->path = $strFile;
                            $objModel->hash = md5_file( TL_ROOT . '/' . $strFile );
                            $objModel->save();

                            $strUuid = \StringUtil::binToUuid( $objModel->uuid );
                        }

                        else {

                            $strUuid = \StringUtil::binToUuid( \Dbafs::addResource( $strFile )->uuid );
                        }

                        \Dbafs::updateFolderHashes( $strUploadFolder );
                    }

                    $_SESSION['FILES'][ $this->strName ][] = [

                        'name' => $arrFile['name'],
                        'type' => $arrFile['type'],
                        'tmp_name' => TL_ROOT . '/' . $strFile,
                        'error' => $arrFile['error'],
                        'size' => $arrFile['size'],
                        'uploaded' => true,
                        'uuid' => $strUuid
                    ];

                    $this->log( 'File "' . $strUploadFolder . '/' . $arrFile['name'] . '" has been uploaded', __METHOD__, TL_FILES );
                }
            }
        }

        unset( $_FILES[ $this->strName ] );
        return $arrReturn;
    }


    public function parse( $arrAttributes = null ) {

        $this->multiple = json_encode( $this->arrConfiguration['multiple'] );
        $this->extensions = json_encode( explode( ',', $this->arrConfiguration['extensions'] ) );
        $this->maxlength = $this->arrConfiguration['maxlength'] ? $this->arrConfiguration['maxlength'] : '0';

        return parent::parse( $arrAttributes );
    }


    public function generate() {}
}