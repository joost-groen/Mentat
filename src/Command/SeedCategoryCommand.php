<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Command;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mentat:category:seed')]
class SeedCategoryCommand extends Command
{
    // A FIXED id so re-running updates the same row instead of creating duplicates.
    private const CATEGORY_ID = 'a1b2c3d4e5f60718293a4b5c6d7e8f90';

    public function __construct(private EntityRepository $repository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();

        // TODO: build the $template array. Shape (PHP array == the JSON we designed):
        $template = [
            'sections' => [
                ['type' => 'title',       'key' => 'catchphrase', 'instruction' => 'A short German catchphrase for the ideal use/user, e.g. "Multifunktionsdrucker für große Arbeitsgruppen". No product name.'],
                ['type' => 'description', 'key' => 'description', 'instruction' => 'A 2–3 sentence general product description in German.'],
                ['type' => 'description', 'key' => 'price', 'instruction' => 'The price of the product in euros.'],
                ['type' => 'table', 'heading' => 'Printing Stats', 'rows' => [
                    ['key' => 'printSpeed',      'label' => 'Print speed (colour + b/w)'],
                    ['key' => 'printResolution', 'label' => 'Printing resolution'],
                    ['key' => 'colourPrint',     'label' => 'Colour print (yes/no)'],
                ]],
                ['type' => 'table', 'heading' => 'Copying Stats', 'rows' => [
                    ['key' => 'copySpeed',      'label' => 'Copy speed (colour + b/w)'],
                    ['key' => 'copyResolution', 'label' => 'Copying resolution'],
                    ['key' => 'adf',     'label' => 'ADF (yes/no)'],
                ]],
                ['type' => 'legal', 'content' => '<p>The product is subject to the laws of Germany.</p>'],
            ],
        ];

        // Create or update the category
        $this->repository->upsert([[
            'id'            => self::CATEGORY_ID,
            'name'          => 'Laser printers',
            'technicalName' => 'laser_printers',
            'template'      => $template,
        ]], $context);

        $output->writeln('Seeded "Laser printers" category.');

        return Command::SUCCESS;
    }
}