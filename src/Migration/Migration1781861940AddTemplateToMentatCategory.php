<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1781861940AddTemplateToMentatCategory extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1781861940;
    }

    public function update(Connection $connection): void
    {
        if($connection->executeQuery('SHOW COLUMNS FROM `mentat_category` LIKE "template"')->fetchOne()) {
            return;
        }

        $connection->executeStatement('ALTER TABLE `mentat_category` ADD COLUMN `template` JSON NULL');
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}