<?php declare(strict_types=1);

namespace JoostGroen\Mentat;

use Shopware\Core\Framework\Api\Acl\Role\AclRoleDefinition;
use Shopware\Core\Framework\Plugin;

class JoostGroenMentat extends Plugin
{
    public function executeComposerCommands(): bool
    {
        return true;
    }

    public function enrichPrivileges(): array
    {
        return [
            AclRoleDefinition::ALL_ROLE_KEY => [
                'mentat_category:read',
                'mentat_category:create',
                'mentat_category:update',
                'mentat_category:delete',
            ],
        ];
    }
}