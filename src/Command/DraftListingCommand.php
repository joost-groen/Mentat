<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Command;

use JoostGroen\Mentat\Service\Llm\LlmClientInterface;
use JoostGroen\Mentat\Service\Llm\PromptBuilder;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use JoostGroen\Mentat\Service\Llm\ResultValidator;

#[AsCommand(name: 'mentat:listing:draft')]
class DraftListingCommand extends Command
{
    public function __construct(
        private EntityRepository $repository,
        private PromptBuilder $promptBuilder,
        private LlmClientInterface $llm,
        private ResultValidator $resultValidator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'The path to the PDF file')
            ->addArgument('technicalName', InputArgument::REQUIRED, 'The technical name of the category');              
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();

        $pdfPath       = $input->getArgument('path');
        $technicalName = $input->getArgument('technicalName');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $technicalName));
        $category = $this->repository->search($criteria, $context)->getEntities()->first();

        if ($category === null) {
            $output->writeln('Category not found: ' . $technicalName);
            return Command::FAILURE;
        }

        $built = $this->promptBuilder->build($category->getTemplate());
        $result = $this->llm->extract($pdfPath, $built->prompt, $built->schema);


        $validation = $this->resultValidator->validate($built->schema, $result);

        $output->writeln(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($validation->emptyFields !== []) {
            $output->writeln('Warning: Some fields were not found in PDF (please review): ' . implode(', ', $validation->emptyFields));
        }
        if ($validation->missing !== []) {
            $output->writeln('Warning: Some fields were missing from the response (schema not honored): ' . implode(', ', $validation->missing));
        }

        return Command::SUCCESS;
    }
}