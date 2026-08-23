<?php

namespace VNS\Ups\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VNS\Ups\Cron\TrackPackage;

class RunTrackPackageCron extends Command
{
    private $trackPackage;

    public function __construct(
        TrackPackage $trackPackage,
        string $name = null
    ) {
        parent::__construct($name);
        $this->trackPackage = $trackPackage;
    }

    protected function configure()
    {
        $this->setName('vns:ups:run-track-package')
            ->setDescription('Run Track Package Cron Job');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Starting Track Package Cron Job...");
        $this->trackPackage->execute();
        $output->writeln("Track Package Cron Job finished.");

        return 0; // Indicate success
    }
}
