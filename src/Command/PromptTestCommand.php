<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Command;

use JoostGroen\Mentat\Service\Llm\PromptBuilder;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mentat:prompt:test')]
class PromptTestCommand extends Command
{
    public function __construct(
        private EntityRepository $repository,
        private PromptBuilder $promptBuilder,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();

        // Look up the category by its technicalName — this is a DAL *filter*.
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', 'laser_printers'));
        $category = $this->repository->search($criteria, $context)->getEntities()->first();

        if ($category === null) {
            $output->writeln('Category not found. Did you run mentat:category:seed?');
            return Command::FAILURE;
        }

        $built = $this->promptBuilder->build($category->getTemplate());
        $output->writeln($built->prompt);
        $output->writeln(json_encode($built->schema, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}