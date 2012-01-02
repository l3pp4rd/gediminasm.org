<?php

namespace Gedmo\BlogBundle\Command;

use Symfony\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Gedmo\BlogBundle\Entity\EmailMessage;

class MigrateCommand extends DoctrineCommand
{
    private $em;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('gedmo:blog:migrate')
            ->setDescription('[INTERNAL] Migrate data from old blog.')
            ->setDefinition(array(
                new InputOption(
                    'em', null, InputOption::VALUE_OPTIONAL,
                    'Used entity manager.',
                    'default'
                )
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emName = $input->getOption('em');
        $this->em = $this->getEntityManager($emName);

        $this->process($output);
        $output->writeLn('Done');
    }

    private function process(OutputInterface $output)
    {
        //
    }
}