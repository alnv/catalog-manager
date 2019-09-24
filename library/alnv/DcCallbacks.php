<?php

namespace CatalogManager;

class DcCallbacks extends \Backend {


    public function __construct() {

        parent::__construct();

        $this->import( 'CatalogEvents' );
    }


    public function removeDcFormOperations( &$arrButtons ) {

        if ( \Input::get('ctlg_table') ) {

            unset( $arrButtons['saveNcreate'] );
            unset( $arrButtons['saveNduplicate'] );
        }

        return $arrButtons;
    }


    public function getCoreTableLoaderButton() {

        return '<button type="submit" id="tl_loadDataContainer" name="tl_loadDataContainer" value="1" class="ctlg_loadWizard" title="'. $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['coreTableLoader'] .'"></button>';
    }

    
    public function checkForDynValues( \DataContainer $objDc ) {

        $strId = \Input::get('id') ? \Input::get('id') : $objDc->id;

        if ( !$strId ) return null;
        if ( is_null( $objDc ) ) return null;
        if ( $_POST['SUBMIT_TYPE'] == 'auto' ) return null;
        if ( is_null( $objDc->activeRecord ) ) return null;
        if ( !$objDc->table || !$this->Database->tableExists( $objDc->table ) ) return null;

        $arrValues = [];
        $objFields = new CatalogFieldBuilder();
        $objFields->initialize(  $objDc->table );
        $arrFields = $objFields->getCatalogFields( false );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !Toolkit::isEmpty( $arrField['dynValue'] ) ) {

                $arrActiveRecords = Toolkit::prepareValues4Db( $objDc->activeRecord->row() );
                $arrValues[ $strFieldname ] = Toolkit::generateDynValue( $arrField['dynValue'], $arrActiveRecords );
                if ( $strFieldname == 'title' && Toolkit::hasDynAlias() ) $arrValues['alias'] = $this->generateFEAlias( '', $arrValues[ $strFieldname ], $objDc->table, $strId );
            }
        }

        if ( is_array( $arrValues ) && count( $arrValues ) > 0 ) $this->Database->prepare( 'UPDATE '. $objDc->table .' %s WHERE id = ?' )->set( $arrValues )->execute( $strId );
    }


    function groupCallback ( $strTemplate, $arrCatalogField = [], $strGroup, $strMode, $strField, $arrRow, $dc ) {
        
        $arrRow = Toolkit::parseCatalogValues( $arrRow, $arrCatalogField, true );

        $arrRow['_mode'] = $strMode;
        $arrRow['_group'] = $strGroup;
        $arrRow['_field'] = $strField;

        return \StringUtil::parseSimpleTokens( $strTemplate, $arrRow );
    }


    function labelCallback ( $arrCatalog, $arrCatalogField = [], $arrRow, $strLabel, $arrLabel ) {

        $arrRow = Toolkit::parseCatalogValues( $arrRow, $arrCatalogField, true );

        if ( $arrCatalog['labelFormat'] ) {

            $arrRow['_label'] = $strLabel;
            return \StringUtil::parseSimpleTokens( $arrCatalog['labelFormat'], $arrRow );
        }

        if ( $arrCatalog['showColumns'] ) {

            $strTemplate = '<div class="cm_table"><div class="cm_table_tr">';
            $intColumns = count( $arrLabel['fields'] );
            $strClass = 'cols_' . $intColumns .' cm_table_td';
            $intWidth = 100 / $intColumns;
            foreach ( $arrLabel['fields'] as $intIndex => $strField ) {
                $strTemplate .= '<div class="'.$strClass.' '. $strField .'" style="width: '.$intWidth.'%">' . ( $arrRow[ $strField ] ?: '-' ) . '</div>';
            }
            $strTemplate .= '</div></div>';
            return $strTemplate;

        }

        $strField = isset( $arrLabel['fields'][0] ) ? $arrLabel['fields'][0] : 'title';

        return $arrRow[ $strField ];
    }


    public function childRecordCallback( $strTemplate, $arrCatalogField = [], $arrRow, $strField ) {

        $arrRow = Toolkit::parseCatalogValues( $arrRow, $arrCatalogField, true );

        $arrRow['_field'] = $strField;
        $arrRow['_label'] = !Toolkit::isEmpty( $arrRow[ $strField ] ) ? $arrRow[ $strField ] : '';

        return \StringUtil::parseSimpleTokens( $strTemplate, $arrRow );
    }


    public function pagePicker( \DataContainer $dc ) {
        
        return ' <a href="' . ( ($dc->value == '' || strpos($dc->value, '{{link_url::') !== false) ? 'contao/page.php' : 'contao/file.php') . '?do=' . \Input::get('do') . '&amp;table=' . $dc->table . '&amp;field=' . $dc->field . '&amp;value=' . rawurlencode(str_replace(array('{{link_url::', '}}'), '', $dc->value)) . '&amp;switch=1' . '" title="' . specialchars($GLOBALS['TL_LANG']['MSC']['pagepicker']) . '" onclick="Backend.getScrollOffset();Backend.openModalSelector({\'width\':768,\'title\':\'' . specialchars(str_replace("'", "\\'", $GLOBALS['TL_DCA'][$dc->table]['fields'][$dc->field]['label'][0])) . '\',\'url\':this.href,\'id\':\'' . $dc->field . '\',\'tag\':\'ctrl_'. $dc->field . (( \Input::get('act') == 'editAll') ? '_' . $dc->id : '') . '\',\'self\':this});return false">' . \Image::getHtml('pickpage.gif', $GLOBALS['TL_LANG']['MSC']['pagepicker'], 'style="cursor:pointer"') . '</a>';
    }


    public function setMultiSrcFlags( $varValue, \DataContainer $dc ) {

        if ( $dc->table && $dc->field ) {

            $objField = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE pid = ( SELECT id FROM tl_catalog WHERE tablename = ? LIMIT 1) AND fieldname = ?')->limit(1)->execute( $dc->table, $dc->field );

            switch ( $objField->fileType ) {

                case 'gallery':

                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ]['eval']['isGallery'] = true;
                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ]['eval']['extensions'] = \Config::get('validImageTypes');

                    break;

                case 'files':

                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ]['eval']['isDownloads'] = true;
                    $GLOBALS['TL_DCA'][ $dc->table ]['fields'][ $dc->field ]['eval']['extensions'] = \Config::get('allowedDownload');

                    break;
            }
        }

        return $varValue;
    }


    public function toggleIcon( $arrRow, $strHref, $strLabel, $strTitle, $strIcon, $strAttributes ) {

        parse_str( $strHref, $arrHrefAttributes );

        $arrOptions = [

            'icon' => 'invisible.gif',
            'fieldname' => 'invisible'
        ];

        $strTable = \Input::get('catalogTable') ? \Input::get('catalogTable') : $arrHrefAttributes['catalogTable'];
        $strCustomFieldname = \Input::get('fieldname') ? \Input::get('fieldname') : $arrHrefAttributes['fieldname'];
        $strIconInVisible = \Input::get('iconVisible') ? \Input::get('iconVisible') : $arrHrefAttributes['iconVisible'];

        if ( $strIconInVisible ) $arrOptions['icon'] = $strIconInVisible;
        if ( $strCustomFieldname ) $arrOptions['fieldname'] = $strCustomFieldname;

        if ( strlen( \Input::get('tid') ) ) {

            $this->toggleVisibility( \Input::get('tid'), ( \Input::get('state') == 1 ), $strTable, $arrOptions, ( @func_get_arg( 12 ) ?: null ) );
            $this->redirect( $this->getReferer() );
        }

        $strHref .= '&amp;tid='. $arrRow['id'] .'&amp;state='. $arrRow[ $arrOptions['fieldname'] ];

        if ( $arrRow[ $arrOptions['fieldname'] ] ) {

            $strIcon = $arrOptions['icon'];
        }

        return '<a href="' . $this->addToUrl( $strHref ) . '" title="' . specialchars( $strTitle ) . '"' . $strAttributes . '>' . \Image::getHtml( $strIcon, $strLabel, 'data-state="' . ( $arrRow[ $arrOptions['fieldname'] ] ? 0 : 1 ) . '"' ) . '</a> ';
    }


    public function toggleVisibility( $intId, $blnVisible, $strTable, $arrOptions, \DataContainer $dc = null ) {

        \Input::setGet( 'id', $intId );
        \Input::setGet( 'act', 'toggle' );

        if ( $dc ) $dc->id = $intId;

        if ( is_array( $GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'] ) ) {

            foreach ( $GLOBALS['TL_DCA'][ $strTable ]['config']['onload_callback'] as $callback ) {

                if ( is_array( $callback ) ) {

                    $this->import($callback[0]);
                    $this->{$callback[0]}->{$callback[1]}(($dc ?: $this));

                } elseif ( is_callable( $callback ) ) {

                    $callback( ( $dc ?: $this ) );
                }
            }
        }

        $strTstamp = time();

        $arrData = [

            'id' => $intId,
            'table' => $strTable,
            'row' => [

                'tstamp' => $strTstamp
            ]
        ];

        $arrData['row'][ $arrOptions[ 'fieldname' ] ] = ( $blnVisible ? '' : 1 );

        $this->CatalogEvents->addEventListener( 'update', $arrData );
        $this->Database->prepare( sprintf( "UPDATE %s SET `tstamp` = %s, `%s` = ? WHERE `id` = ?", $strTable, $strTstamp, $arrOptions[ 'fieldname' ] ) )->execute( ( $blnVisible ? '' : 1 ), $intId );
    }


    public function generateAlias( $varValue, \DataContainer $dc, $strField = 'title', $strTable = '' ) {

        if ( $_POST['SUBMIT_TYPE'] == 'auto' ) return $varValue;

        $blnAutoAlias = false;
        $strTable = \Input::get( 'table' ) ? \Input::get( 'table' ) : $strTable;

        if ( !$strTable ) return $varValue . uniqid( '_' );

        if ( !$varValue ) {

            $blnAutoAlias = true;
            $varValue = str_replace(',', '-', $dc->activeRecord->{$strField} );
            $varValue = Toolkit::slug( $varValue );
        }

        $arrValues = [ $varValue, $dc->activeRecord->id ];
        $strQuery = ' WHERE `alias` = ? AND `id` != ?';

        if ( $dc->activeRecord->pid ) {

            $strQuery .= ' AND pid = ?';
            $arrValues[] = $dc->activeRecord->pid;
        }

        $objCatalogs = $this->Database->prepare( sprintf( 'SELECT * FROM %s ' . $strQuery, $strTable ) )->execute( $arrValues );
        
        if ( $objCatalogs->numRows && !$blnAutoAlias ) {

            throw new \Exception( sprintf( $GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue ) );
        }

        if ( $objCatalogs->numRows && $blnAutoAlias ) {

            $varValue .= '_' . $dc->activeRecord->id;
        }

        return $varValue;
    }


    public function generateFEAlias( $varValue, $strTitle, $strTablename, $strID, $strModuleID = '' ) {

        if ( !$varValue && $strTitle ) {

            $strTitle = str_replace(',', '-', $strTitle );
            $varValue = Toolkit::slug( $strTitle );
        }

        $arrValues = [ $varValue ];
        $strSQLStatement = sprintf( 'SELECT * FROM %s WHERE `alias` = ?', $strTablename );

        if ( !is_null( $strID ) ) {

            $strSQLStatement = $strSQLStatement . ' ' . 'AND id != ?';
            $arrValues[] = $strID;

            $objEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE `id` = ?', $strTablename ) )->limit(1)->execute( $strID );

            if ( $objEntity->numRows ) {

                if ( $objEntity->pid ) {

                    $strSQLStatement = $strSQLStatement . ' AND pid = ?';
                    $arrValues[] = $objEntity->pid;
                }
            }
        }

        $objCatalogs = $this->Database->prepare( $strSQLStatement )->execute( $arrValues );
        
        if ( $objCatalogs->numRows && \Input::get( 'id' . $strModuleID ) ) {

            $varValue .= '_' . \Input::get( 'id' . $strModuleID );
        }

        if ( $objCatalogs->numRows && !\Input::get( 'id' . $strModuleID ) ) {

            $varValue .= '_' . md5( time() . uniqid() );
        }

        if ( !$varValue ) {

            $varValue .= md5( $objCatalogs->numRows . time() . uniqid() );
        }

        return $varValue;
    }


    public function generateGeoCords( $dc ) {

        $arrCatalog = [];
        $strTable = \Input::get('table');

        if ( TL_MODE == 'FE') {

            $strTable = $dc->getTable();
            $dc->activeRecord = $dc;
        }

        if ( !$dc->activeRecord ) {

            return null;
        }

        if ( $dc->activeRecord->suspend_geocoding ) {

            return null;
        }

        if ( !Toolkit::isEmpty( $strTable ) ) {

            $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ];
        }

        else {

            $strDo = \Input::get( 'do' );
            $arrTables = Toolkit::getBackendModuleTablesByDoAttribute( $strDo );

            if ( is_array( $arrTables ) && isset( $arrTables[0] ) ) {

                $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $arrTables[0] ];
            }
        }

        if ( empty( $arrCatalog ) || !is_array( $arrCatalog ) ) return null;

        $arrCords = [];
        $objGeoCoding = new GeoCoding();
        $strGeoInputType = $arrCatalog['addressInputType'];

        switch ( $strGeoInputType ) {

            case 'useSingleField':

                $arrCords = $objGeoCoding->getCords( $dc->activeRecord->{$arrCatalog['geoAddress']}, 'en', true );

                break;

            case 'useMultipleFields':

                $objGeoCoding->setCity( $dc->activeRecord->{$arrCatalog['geoCity']} );
                $objGeoCoding->setStreet( $dc->activeRecord->{$arrCatalog['geoStreet']} );
                $objGeoCoding->setPostal( $dc->activeRecord->{$arrCatalog['geoPostal']} );
                $objGeoCoding->setCountry( $dc->activeRecord->{$arrCatalog['geoCountry']} );
                $objGeoCoding->setStreetNumber( $dc->activeRecord->{$arrCatalog['geoStreetNumber']} );

                $arrCords = $objGeoCoding->getCords( '', 'en', true );

                break;
        }

        if ( ( $arrCords['lat'] || $arrCords['lng'] ) && ( $arrCatalog['lngField'] && $arrCatalog['latField'] ) ) {

            $arrSet = [];
            $arrSet[ $arrCatalog['lngField'] ] = $arrCords['lng'];
            $arrSet[ $arrCatalog['latField'] ] = $arrCords['lat'];

            $this->Database->prepare( 'UPDATE '. $arrCatalog['tablename'] .' %s WHERE id = ?' )->set($arrSet)->execute( $dc->id );
        }
    }


    public function generateRelationWizard( \DataContainer $dc ) {

        $strTable = \Input::get( 'table' ) ? \Input::get( 'table' ) : \Input::get( 'do' );

        if ( !$dc->value || !$strTable || !$dc->field ) return '';

        $objCatalogField = $this->Database->prepare( 'SELECT * FROM tl_catalog_fields WHERE fieldname = ? AND pid = ( SELECT id FROM tl_catalog WHERE tablename = ? )' )->limit(1)->execute( $dc->field, $strTable );

        if ( !$objCatalogField->numRows ) return '';

        $arrField = $objCatalogField->row();
        $strTable = $arrField['dbTable'];

        if ( !$strTable ) return '';

        \Controller::loadDataContainer( $strTable );
        \Controller::loadLanguageFile( $strTable, $GLOBALS['TL_LANGUAGE'] );

        $objI18nTranslator = new I18nCatalogTranslator();
        $objI18nTranslator->initialize();

        $strTableAttribute = '';
        $strTitle = $objI18nTranslator->get( 'field', 'title', [ 'titleOnly' => true ] );
        $strModalTitle = sprintf( $GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['modalIFrameTitle'], $dc->value );
        $strModalIFrameOptions = sprintf( "{'width':768,'title':'%s','url':this.href}", $strModalTitle );
        $strDoAttribute = $this->getDoAttributeByTable( $strTable );

        if ( $GLOBALS['TL_DCA'][ $strTable ] && is_array( $GLOBALS['TL_DCA'][ $strTable ]['config'] ) && $GLOBALS['TL_DCA'][ $strTable ]['config']['ptable'] ) {

            $strTableAttribute = sprintf( '&amp;table=%s', $strTable );
            $strDoAttribute = sprintf( 'do=%s', $strDoAttribute );
        }

        else {

            $strDoAttribute = sprintf( 'do=%s', $strDoAttribute );
        }

        return '<a href="/contao?' . $strDoAttribute . $strTableAttribute . '&amp;act=edit&amp;id=' . $dc->value . '&amp;popup=1&amp;nb=1&amp;rt=' . REQUEST_TOKEN . '" title="'. $strTitle .'" onclick="Backend.openModalIframe(' . $strModalIFrameOptions . ');return false" style="padding-left:3px">' . \Image::getHtml('alias.gif', $GLOBALS['TL_LANG']['tl_content']['editalias'][0], 'style="vertical-align:middle"') . '</a>';
    }


    public function onSubmitCallback( \DataContainer $dc ) {

        if ( is_null( $dc->activeRecord ) ) return;

        $strEvent = $this->entityExist( $dc->table, $dc->id ) ? 'update' : 'create';

        $arrData = [

            'id' => $dc->id,
            'table' => $dc->table,
            'row' => $this->getActiveRecordRow( $dc->table, $dc->id )
        ];

        $this->CatalogEvents->addEventListener( $strEvent, $arrData );
    }


    public function onDeleteCallback( \DataContainer $dc, $strID ) {

        if ( is_null( $dc->activeRecord ) || !$strID ) return;

        $arrData = [

            'id' => $strID,
            'table' => $dc->table,
            'row' => $this->getActiveRecordRow( $dc->table, $strID )
        ];

        $this->CatalogEvents->addEventListener( 'delete' , $arrData );
    }


    public function onCutCallback( \DataContainer $dc ) {

        $arrData = [

            'id' => $dc->id,
            'table' => $dc->table,
            'row' => $this->getActiveRecordRow( $dc->table, $dc->id )
        ];

        $this->CatalogEvents->addEventListener( 'update' , $arrData );
    }


    protected function getActiveRecordRow( $strTable, $strID ) {

        if ( $this->Database->tableExists( $strTable ) && $strID ) {

            $objEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $strTable ) )->limit(1)->execute( $strID );

            if ( $objEntity->numRows ) {

               return method_exists( $objEntity, 'row' ) ? $objEntity->row() : [];
            }
        }

        return [];
    }


    protected function entityExist( $strTable, $strID ) {

        if ( $this->Database->tableExists( $strTable ) && $strID ) {

            $objEntity = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ? AND tstamp != 0', $strTable ) )->limit(1)->execute( $strID );

            if ( $objEntity->numRows ) return true;
        }

        return false;
    }


    protected function getDoAttributeByTable( $strTablename ) {

        if ( is_array( $GLOBALS['BE_MOD'] ) ) {

            foreach ( $GLOBALS['BE_MOD'] as $arrModules ) {

                if ( is_array( $arrModules ) ) {

                    foreach ( $arrModules as $strModule => $arrModule ) {

                        if ( isset( $arrModule['tables'] ) && is_array( $arrModule['tables'] ) ) {

                            foreach ( $arrModule['tables'] as $strTable ) {

                                if ( $strTable === $strTablename ) {

                                    return $strModule;
                                }
                            }
                        }
                    }
                }
            }
        }

        return '';
    }


    public function setFallbackAndLanguage( \DataContainer $dc ) {

        if ( !$dc->activeRecord ) {

            return null;
        }

        $arrCatalog = $this->getCatalog();

        if ( empty( $arrCatalog ) ) {

            return null;
        }

        $strId = \Input::get('ctlg_fallback') ?: \Input::get('id');
        $strFallbackLanguage = $arrCatalog['fallbackLanguage'];

        if ( !$strFallbackLanguage ) {

            return null;
        }

        if ( \Input::get( 'act' ) == 'editAll' ) {

            $strId = $dc->id;
        }

        $arrSet = [];
        $arrSet[ $arrCatalog['languageEntityColumn'] ] = \Input::get('ctlg_language') ?: $strFallbackLanguage;
        $arrSet[ $arrCatalog['linkEntityColumn'] ] = $strId;

        $this->Database->prepare( 'UPDATE '. $dc->table . ' %s WHERE id=?' )->set( $arrSet )->execute( $dc->activeRecord->id );
    }


    public function setGlobalTranslateButton( \DataContainer $dc ) {

        $strLanguage = \Input::get('ctlg_language');

        if ( \Input::get('ctlg_return') ) {

            $strUrl = str_replace( '&ctlg_language=' . $strLanguage , '', \Environment::get('uri') );
            $strUrl = str_replace( '&ctlg_return=1', '', $strUrl );

            \Controller::redirect( $strUrl );
        }

        if ( !$strLanguage || !\Input::get('id') ) {

            return null;
        }

        if ( !$dc->table ) {

            return null;
        }

        $arrCatalog = $this->getCatalog();

        if ( empty( $arrCatalog ) ) {

            return null;
        }

        $strLangColumn = $arrCatalog['languageEntityColumn'];
        $strFallbackColumn = $arrCatalog['linkEntityColumn'];

        $objEntity = $this->Database->prepare( 'SELECT * FROM '. $dc->table . ' WHERE `'. $strLangColumn .'`=? AND `'. $strFallbackColumn .'`=?' )->execute( \Input::get('ctlg_language'), \Input::get('ctlg_fallback') );

        if ( $objEntity->numRows ) {

            $GLOBALS['TL_DCA'][ $dc->table ]['config']['closed'] = true;
            unset( $GLOBALS['TL_DCA'][ $dc->table ]['list']['global_operations']['new'] );
        }
    }


    protected function getCatalog() {

        $arrCatalog = [];
        $strTable = \Input::get('table');

        if ( !Toolkit::isEmpty( $strTable ) ) {

            $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ];
        }

        else {

            $strDo = \Input::get( 'do' );
            $arrTables = Toolkit::getBackendModuleTablesByDoAttribute( $strDo );

            if ( is_array( $arrTables ) && isset( $arrTables[0] ) ) {

                $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $arrTables[0] ];
            }
        }

        return $arrCatalog;
    }


    public function generateLanguageButton( $arrRow, $strHref, $strLabel, $strTitle, $strIcon ) {

        $strId = $arrRow['id'];

        if ( \Input::get('table') ) {

            $strId = \Input::get( 'id' );
        }

        $arrParameters = [

            'do' => \Input::get('do'),
            'table' => \Input::get('table'),
            'ctlg_table' => \Input::get('ctlg_table'),
            'ctlg_fallback' => $arrRow['id'],
            'id' => $strId,
            'popup' => '1',
            'rt' => REQUEST_TOKEN
        ];

        foreach ( $arrParameters as $strField => $strValue ) {

            if ( !$strValue ) {

                unset( $arrParameters[ $strField ] );
            }
        }

        $strUri = '?'. http_build_query( $arrParameters ) . '&' . $strHref;

        return '<a href="contao'.$strUri.'" title="'.\StringUtil::specialchars( $strTitle ).'" onclick="Backend.openModalIframe({\'title\':\''.$arrRow['title'].'\',\'url\':this.href});return false">'.\Image::getHtml( $strIcon, $strLabel ).'</a> ';
    }


    public function generateNewTranslationButton( $arrRow, $strLabel, $strTitle, $strIcon ) {

        $arrParameters = [

            'do' => \Input::get('do'),
            'act' => 'create',
            'popup' => '1',
            'rt' => \Input::get('rt'),
            'ctlg_table' => \Input::get('ctlg_table'),
            'ctlg_fallback' => \Input::get('ctlg_fallback'),
            'ctlg_language' => \Input::get('ctlg_language')
        ];

        if ( \Input::get('table') ) {

            $arrParameters['table'] = \Input::get('table');
            $arrParameters['mode'] = '2';
            $arrParameters['pid'] =  \Input::get('id');
            $arrParameters['id'] =  \Input::get('id');
        }

        foreach ( $arrParameters as $strField => $strValue ) {

            if ( !$strValue ) {

                unset( $arrParameters[ $strField ] );
            }
        }

        $strUri = '?'. http_build_query( $arrParameters );

        return '<a href="contao'.$strUri.'" class="'.$strIcon.'" title="'.\StringUtil::specialchars( $strTitle ).'">'. $strLabel .'</a> ';
    }


    public function deleteTranslations( \DataContainer $dc ) {

        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->table ];
        $strFallbackColumn = $arrCatalog['linkEntityColumn'];
        $this->Database->prepare( 'DELETE FROM ' . $dc->table .' WHERE `'. $strFallbackColumn .'`=?' )->execute( $dc->id );
    }


    public function copyTranslations( $strNewId, \DataContainer $dc ) {

        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->table ];
        $strFallbackColumn = $arrCatalog['linkEntityColumn'];
        $strLanguageColumn = $arrCatalog['languageEntityColumn'];

        $arrMainSet = [];
        $arrMainSet[ $strFallbackColumn ] = $strNewId;
        $this->Database->prepare( 'UPDATE ' . $dc->table . ' %s WHERE id=?' )->set( $arrMainSet )->execute( $strNewId );

        $objEntities = $this->Database->prepare( 'SELECT * FROM '. $dc->table .' WHERE `'. $strFallbackColumn .'`=? AND `'. $strLanguageColumn .'`!=?' )->execute( $dc->id, $arrCatalog['fallbackLanguage'] );

        if ( $objEntities->numRows ) {

            while ( $objEntities->next() ) {

                $arrEntity = $objEntities->row();
                unset( $arrEntity['id'] );
                // @todo generate new alias
                // timestamp
                $arrEntity[ $strFallbackColumn ] = $strNewId;

                $this->Database->prepare( 'INSERT INTO '. $dc->table .' %s' )->set( $arrEntity )->execute();
            }
        }
    }


    public function cutTranslations( \DataContainer $dc ) {

        $arrCatalog = $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $dc->table ];
        $strFallbackColumn = $arrCatalog['linkEntityColumn'];
        $strLanguageColumn = $arrCatalog['languageEntityColumn'];
        $this->Database->prepare( 'UPDATE '. $dc->table .' %s WHERE `'. $strFallbackColumn .'`=? AND `'. $strLanguageColumn .'`!=?' )->set([ 'pid' => \Input::get('pid'), 'tstamp' => time() ])->execute( $dc->id, $arrCatalog['fallbackLanguage'] );
    }


    public function pasteItem( \DataContainer $dc, $row, $table, $cr, $arrClipboard=null ) {

        $imagePasteAfter = \Image::getHtml( 'pasteafter.gif', sprintf($GLOBALS['TL_LANG'][ $dc->table ]['pasteafter'][1], $row['id']) );
        $imagePasteInto = \Image::getHtml( 'pasteinto.gif', sprintf($GLOBALS['TL_LANG'][ $dc->table ]['pasteinto'][1], $row['id']) );

        if ($row['id'] == 0) {

            return $cr ? \Image::getHtml('pasteinto_.gif').' ' : '<a href="'.\Backend::addToUrl('act='.$arrClipboard['mode'].'&mode=2&pid='.$row['id'].'&id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
        }

        return ($arrClipboard['mode'] == 'cut' && ($arrClipboard['id'] == $row['id'] || $cr)) ? \Image::getHtml('pasteafter_.gif').' ' : '<a href="'.\Backend::addToUrl('act='.$arrClipboard['mode'].'&mode=1&pid='.$row['id'].'&id='.$arrClipboard['id']).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ';
    }
}