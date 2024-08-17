<?php

namespace Alnv\CatalogManagerBundle\NotificationType;

use Terminal42\NotificationCenterBundle\NotificationType\NotificationTypeInterface;
use Terminal42\NotificationCenterBundle\Token\Definition\AnythingTokenDefinition;
use Terminal42\NotificationCenterBundle\Token\Definition\Factory\TokenDefinitionFactoryInterface;

class EntityUpdateNotificationType implements NotificationTypeInterface
{
    public const NAME = 'ctlg_entity_status_update';

    public function __construct(private TokenDefinitionFactoryInterface $factory)
    {
        //
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getTokenDefinitions(): array
    {
        return [
            $this->factory->create(AnythingTokenDefinition::class, 'admin_email*', 'ctlg.admin_email'),
            $this->factory->create(AnythingTokenDefinition::class, 'rawOld_*', 'ctlg.rawOld_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'cleanOld_*', 'ctlg.cleanOld_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'field_*', 'ctlg.field_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'table_*', 'ctlg.table_'),
            $this->factory->create(AnythingTokenDefinition::class, 'domain', 'ctlg.domain'),
            $this->factory->create(AnythingTokenDefinition::class, 'raw_*', 'ctlg.raw_*'),
            $this->factory->create(AnythingTokenDefinition::class, 'clean_*', 'ctlg.clean_*')
        ];
    }
}