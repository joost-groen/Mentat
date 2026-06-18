<?php declare(strict_types=1);

namespace JoostGroen\Mentat;

use Shopware\Core\Framework\Plugin;

class JoostGroenMentat extends Plugin
{
    public function executeComposerCommands(): bool
    {
        return true;
    }
}