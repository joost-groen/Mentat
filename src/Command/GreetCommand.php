<?php declare(strict_types=1);

namespace JoostGroen\Mentat\Command;

use JoostGroen\Mentat\Service\Greeter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

# Register the command "mentat:greet" in the console via Symfony Console
#[AsCommand(name: 'mentat:greet')]

class GreetCommand extends Command
{
    public function __construct(private Greeter $greeter)
    {
        # $greeter is injected via dependency injection and automatically stored in the property $this->greeter
        parent::__construct(); # Call the parent constructor
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->greeter->greet('World')); # Write the greeting to the terminal
        return Command::SUCCESS; # Return the success code
    }
}