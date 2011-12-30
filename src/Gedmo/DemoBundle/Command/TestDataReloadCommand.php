<?php

namespace Gedmo\DemoBundle\Command;

use Symfony\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Gedmo\DemoBundle\Entity\Category;
use Gedmo\DemoBundle\Entity\Language;

class TestDataReloadCommand extends DoctrineCommand
{
    protected function configure()
    {
        parent::configure();
        $this->setName('gedmo:demo:reload')
            ->setDescription('Reloads test data.')
            ->setDefinition(array(
                new InputOption(
                    'em', null, InputOption::VALUE_OPTIONAL,
                    'Set the default database collation.',
                    'default'
                )
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emName = $input->getOption('em');
        $em = $this->getEntityManager($emName);

        // deletions
        $conn = $em->getConnection();
        $statement = $conn->prepare('SET FOREIGN_KEY_CHECKS=0'); $statement->execute();

        $statement = $conn->prepare('TRUNCATE TABLE demo_languages'); $statement->execute();
        $statement = $conn->prepare('TRUNCATE TABLE demo_categories'); $statement->execute();
        $statement = $conn->prepare('TRUNCATE TABLE ext_translations'); $statement->execute();

        $statement = $conn->prepare('SET FOREIGN_KEY_CHECKS=1'); $statement->execute();

        $lang0 = new Language;
        $lang0->setTitle('En');

        $lang1 = new Language;
        $lang1->setTitle('De');

        $em->persist($lang0);
        $em->persist($lang1);

        $translatable = $this->getContainer()->get(
            'stof_doctrine_extensions.listener.translatable'
        );
        $translatable->setTranslatableLocale('en');

        $food = new Category;
        $food->setTitle('Food');
        $food->setDescription('Food');

        $em->persist($food);
        $cars = new Category;
        $cars->setTitle('Cars');
        $cars->setDescription('Cars');

        $em->persist($cars);

        $sportCars = new Category;
        $sportCars->setTitle('Sport Cars');
        $sportCars->setDescription('Cars->Sport Cars');
        $sportCars->setParent($cars);

        $em->persist($sportCars);

        $electricCars = new Category;
        $electricCars->setTitle('Electric Cars');
        $electricCars->setDescription('Cars->Electric Cars');
        $electricCars->setParent($cars);

        $em->persist($electricCars);

        $fruits = new Category;
        $fruits->setTitle('Fruits');
        $fruits->setDescription('Food->Fruits');
        $fruits->setParent($food);

        $em->persist($fruits);

        $milk = new Category;
        $milk->setTitle('Milk');
        $milk->setDescription('Food->Milk');
        $milk->setParent($food);

        $em->persist($milk);

        $vegetables = new Category;
        $vegetables->setTitle('Vegetables');
        $vegetables->setDescription('Food->Vegetables');
        $vegetables->setParent($food);

        $em->persist($vegetables);

        $onions = new Category;
        $onions->setTitle('Onions');
        $onions->setDescription('Food->Vegetables->Onions');
        $onions->setParent($vegetables);

        $em->persist($onions);

        $carrots = new Category;
        $carrots->setTitle('Carrots');
        $carrots->setDescription('Food->Vegetables->Carrots');
        $carrots->setParent($vegetables);

        $em->persist($carrots);

        $cabbages = new Category;
        $cabbages->setTitle('Cabbages');
        $cabbages->setDescription('Food->Vegetables->Cabbages');
        $cabbages->setParent($vegetables);

        $em->persist($cabbages);

        $potatoes = new Category;
        $potatoes->setTitle('Potatoes');
        $potatoes->setDescription('Food->Vegetables->Potatoes');
        $potatoes->setParent($vegetables);

        $em->persist($potatoes);
        $em->flush();

        // de language
        $translatable->setTranslatableLocale('de');

        $food->setTitle('Lebensmittel');
        $food->setDescription('Lebensmittel');
        $em->persist($food);

        $cars->setTitle('Autos');
        $cars->setDescription('Autos');
        $em->persist($cars);

        $vegetables->setTitle('Gemüse');
        $vegetables->setDescription('Lebensmittel->Gemüse');
        $em->persist($vegetables);

        $carrots->setTitle('Möhren');
        $carrots->setDescription('Lebensmittel->Gemüse->Möhren');
        $em->persist($carrots);

        $em->flush();
        $output->writeLn('Reload Done..');
    }
}
