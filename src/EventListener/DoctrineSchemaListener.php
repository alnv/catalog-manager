<?php

namespace Alnv\CatalogManagerBundle\EventListener;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;

class DoctrineSchemaListener
{
    public function __construct(private readonly ContaoFramework $framework, private readonly Registry $doctrine,)
    {
    }

    public function postGenerateSchema(GenerateSchemaEventArgs $event): void
    {

        if (!Database::getInstance()->tableExists('tl_catalog')) {
            return;
        }

        $objSchema = $event->getSchema();
        $objCatalogs = Database::getInstance()->prepare('SELECT * FROM tl_catalog ORDER BY `tablename`')->execute();

        while ($objCatalogs->next()) {

            if (!$objCatalogs->tablename) {
                continue;
            }

            $objTable = $objSchema->hasTable($objCatalogs->tablename) ? $objSchema->getTable($objCatalogs->tablename) : $objSchema->createTable($objCatalogs->tablename);
            $arrFields = Database::getInstance()->listFields($objCatalogs->tablename);

            foreach ($arrFields as $strIndex => $arrField) {

                $strField = $arrField['name'];

                if (in_array($strIndex, ['PRIMARY', 'alias'])) {
                    continue;
                }

                $default = $arrField['default'];
                $unsigned = ($arrField['attributes'] ?? '') == 'unsigned';
                $notnull = ($arrField['null'] ?? '') == 'NOT NULL';
                $autoincrement = ($arrField['extra'] ?? '') == 'auto_increment';

                $origin_type = strtok(strtolower($arrField['origtype']), '(), ');
                $connection = $this->doctrine->getConnection();

                try {

                    $type = $connection->getDatabasePlatform()->getDoctrineTypeMapping($origin_type);
                    $length = (int)strtok('(), ');

                    $arrOptions = [
                        'length' => $length,
                        'unsigned' => $unsigned,
                        'fixed' => $origin_type == 'char',
                        'default' => $default,
                        'notnull' => $notnull,
                        'scale' => null,
                        'precision' => null,
                        'autoincrement' => $autoincrement,
                        'comment' => null,
                    ];

                    $objTable->addColumn($strField, $type, $arrOptions);

                    if ($strField == 'id') {
                        $objTable->setPrimaryKey([$strField]);
                    }

                } catch (\Exception $objError) {}
            }
        }
    }
}