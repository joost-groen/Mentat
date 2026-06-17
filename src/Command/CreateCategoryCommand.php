<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mentat:category:create')]
class CreateCategoryCommand extends Command
{
    // The repository is injected — it's your tool for reading/writing categories.
    public function __construct(private EntityRepository $repository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Context = "who is doing this and in what language." For a CLI script, the default is fine.
        $context = Context::createDefaultContext();

        // WRITE: save one new category.
        // Note: an array OF arrays (each inner array = one row), and camelCase keys.
        $this->repository->create([
            [
                'id' => Uuid::randomHex(),
                'name' => 'Laser printers',
                'technicalName' => 'laser_printers',
            ],
        ], $context);

        // READ: fetch every category back. Empty Criteria = "give me all of them."
        $result = $this->repository->search(new Criteria(), $context);

        $output->writeln('Categories in DB: ' . $result->count());

        // Print the name of each one we got back.
        foreach ($result->getEntities() as $category) {
            $output->writeln(' - ' . $category->getName());
        }

        return Command::SUCCESS;
    }
}