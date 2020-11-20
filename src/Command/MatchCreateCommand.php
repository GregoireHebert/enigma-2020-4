<?php

namespace App\Command;

use App\MatchMaking\Lobby;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MatchCreateCommand extends Command
{
    protected static $defaultName = 'app:match:create';

    private $lobby;
    private $logger;

    public function __construct(Lobby $lobby, LoggerInterface $logger)
    {
        parent::__construct();

        $this->lobby = $lobby;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Create a match for every players in the lobby')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->lobby->createMatches();
        } catch (\Exception $e) {
            $this->logger->warning('Attempt to remove a non queued player.');
        }
        $io->success('Matches (if any player available) created.');

        return Command::SUCCESS;
    }
}
