<?php

namespace Gedmo\TestExtensionsBundle\Command;

use Symfony\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Bundle\DoctrineAbstractBundle\Common\DataFixtures\Loader as DataFixturesLoader;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;

class TestDataReloadCommand extends DoctrineCommand
{   
    protected function configure()
    {
        parent::configure();
        $this->setName('test:data:reload')
            ->setDescription('Reloads test data.')
            ->setDefinition(array(
                new InputOption(
                    'em', null, InputOption::VALUE_OPTIONAL,
                    'Set the default database collation.',
                    'default'
                )
            ));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emName = $input->getOption('em');
        $em = self::getEntityManager($this->container, $emName);
        
        $bundle = $this->getApplication()->getKernel()->getBundle('GedmoTestExtensionsBundle');
        $path = $bundle->getPath() . '/DataFixtures/ORM';
        
        $loader = new DataFixturesLoader($this->container);
        $loader->loadFromDirectory($path);
        
        $fixtures = $loader->getFixtures();
        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->setLogger(function($message) use ($output) {
            $output->writeln(sprintf('  <comment>></comment> <info>%s</info>', $message));
        });
        $executor->execute($fixtures, false);
    }
}
