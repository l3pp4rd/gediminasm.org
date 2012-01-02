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
    private $old;

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
                ),
                new InputOption(
                    'old', null, InputOption::VALUE_OPTIONAL,
                    'Old database name.',
                    'blog'
                )
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emName = $input->getOption('em');
        $this->em = $this->getEntityManager($emName);
        $this->old = \Doctrine\DBAL\DriverManager::getConnection(
            array_merge($this->em->getConnection()->getParams(), array('dbname' => $input->getOption('old'))),
            null,
            $this->em->getEventManager()
        );

        $this->process($output);
        $output->writeLn('Done');
    }

    private function process(OutputInterface $output)
    {
        $new = $this->em->getConnection();
        $new->beginTransaction();
        try {
            // article map
            $map = array();
            foreach ($this->old->fetchAll('select * from articles') as $o) {
                $n = $new->fetchAssoc('select * from articles where slug = ? limit 1', array($o['slug']));
                if ($n) {
                    $map[intval($o['id'])] = intval($n['id']);
                }
            }
            // update article creation dates and comments
            foreach ($map as $oId => $nId) {
                $o = $this->old->fetchAssoc('select * from articles where id = ? limit 1', array($oId));
                $new->executeQuery('update articles set created = ? where id = ?', array($o['created'], $nId));

                $cs = $this->old->fetchAll('select * from comments where article_id = ?', array($oId));
                foreach ($cs as $c) {
                    $nc = array(
                        'created' => $c['created'],
                        'article_id' => $nId,
                        'author' => $c['author'],
                        'subject' => $c['subject'],
                        'content' => $c['content'] // ? into markdown?
                    );
                    $new->insert('comments', $nc);
                }
            }
            $new->commit();
        } catch(\Exception $e) {
            $new->rollback();
            throw $e;
        }
    }
}