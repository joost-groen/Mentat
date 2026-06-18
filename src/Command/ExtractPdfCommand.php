<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Command;

use JoostGroen\Mentat\Service\PdfTextExtractor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mentat:pdf:extract')]
class ExtractPdfCommand extends Command
{
    public function __construct(private PdfTextExtractor $extractor)
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
        $path = $input->getArgument('path');
        $text = $this->extractor->extract($path);
        $output->writeln(substr($text, 0, 50000));
        return Command::SUCCESS;
    }
}