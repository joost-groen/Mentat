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
use JoostGroen\Mentat\Service\Listing\ProductDraftWriter;

#[AsCommand(name: 'mentat:listing:draft')]
class DraftListingCommand extends Command
{
    public function __construct(
        private EntityRepository $repository,
        private PromptBuilder $promptBuilder,
        private LlmClientInterface $llm,
        private ResultValidator $resultValidator,
        private ProductDraftWriter $productDraftWriter,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'The path to the PDF file')
            ->addArgument('technicalName', InputArgument::REQUIRED, 'The technical name of the category')       
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the product')
            ->addArgument('productNumber', InputArgument::REQUIRED, 'The product number of the product')
            ->addArgument('price', InputArgument::REQUIRED, 'The price of the product')
            ->addArgument('stock', InputArgument::REQUIRED, 'The stock of the product');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();

        $pdfPath       = $input->getArgument('path');
        $technicalName = $input->getArgument('technicalName');
        $name          = $input->getArgument('name');
        $productNumber = $input->getArgument('productNumber');
        $price         = (float) $input->getArgument('price');
        $stock         = (int) $input->getArgument('stock');

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('technicalName', $technicalName));
        $category = $this->repository->search($criteria, $context)->getEntities()->first();

        if ($category === null) {
            $output->writeln('Category not found: ' . $technicalName);
            return Command::FAILURE;
        }

        $built = $this->promptBuilder->build($category->getTemplate());

        $output->writeln($built->prompt);
        $output->writeln(json_encode($built->schema, JSON_PRETTY_PRINT));
        
        $result = $this->llm->extract($pdfPath, $built->prompt, $built->schema);


        $validation = $this->resultValidator->validate($built->schema, $result);

        $output->writeln(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if ($validation->emptyFields !== []) {
            $output->writeln('Warning: Some fields were not found in PDF (please review): ' . implode(', ', $validation->emptyFields));
        }
        if ($validation->missing !== []) {
            $output->writeln('Warning: Some fields were missing from the response (schema not honored): ' . implode(', ', $validation->missing));
        }

        $idNewProduct = $this->productDraftWriter->write($category->getTemplate(), $result, $name, $productNumber, $price, $stock, $context);

        $output->writeln('New product created with ID: ' . $idNewProduct);

        return Command::SUCCESS;
    }
}