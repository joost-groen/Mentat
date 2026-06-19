<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Core\Content\MentatCategory;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class MentatCategoryEntity extends Entity
{
    use EntityIdTrait; # This trait is used to automatically generate the id field

    protected string $name;
    protected string $technicalName;
    protected ?array $template = null; //array or null

    //Getters and setters for the properties

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getTechnicalName(): string
    {
        return $this->technicalName;
    }

    public function setTechnicalName(string $technicalName): void
    {
        $this->technicalName = $technicalName;
    }

    public function getTemplate(): ?array
    {
        return $this->template;
    }

    public function setTemplate(?array $template): void
    {
        $this->template = $template;
    }
}