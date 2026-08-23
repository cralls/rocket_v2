<?php
namespace Vectornetworksolutionsllc\Commercebug\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Testbed extends Command
{
    protected function configure()
    {
        $this->setName("ps:cb:testbed");
        $this->setDescription("A sandbox to test Magento code in.");
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    
        $output->writeln("Hello World");  
    }
} 