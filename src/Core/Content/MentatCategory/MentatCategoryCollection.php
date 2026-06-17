<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Core\Content\MentatCategory;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class MentatCategoryCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return MentatCategoryEntity::class; # No use statement needed here because same namespace
    }
}