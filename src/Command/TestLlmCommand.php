<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Command;

use JoostGroen\Mentat\Service\Llm\LlmClientInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mentat:llm:test')]
class TestLlmCommand extends Command
{
    public function __construct(private LlmClientInterface $llm)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('path', InputArgument::REQUIRED, 'The path to the PDF file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $path   = $input->getArgument('path');
        $prompt = 'Extract the product name and the colour print speed in pages per minute from this spec sheet.';
        $schema = [
            'type' => 'object',
            'properties' => [
                'productName'         => ['type' => 'string'],
                'colourPrintSpeedPpm' => ['type' => 'string'],
            ],
        ];

        $result = $this->llm->extract($path, $prompt, $schema);
        $output->writeln(json_encode($result, JSON_PRETTY_PRINT));

        return Command::SUCCESS;
    }
}