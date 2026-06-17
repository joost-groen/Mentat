<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1781706976AddUniqueTechnicalName extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1781706976;   // same number, as an integer (no quotes)
    }

    public function update(Connection $connection): void
    {
        $sql = <<<SQL
            ALTER TABLE `mentat_category` ADD CONSTRAINT `uniq.mentat_category.technical_name` UNIQUE (`technical_name`);
        SQL;

        $connection->executeStatement($sql);
    }

    public function updateDestructive(Connection $connection): void
    {
    }
}