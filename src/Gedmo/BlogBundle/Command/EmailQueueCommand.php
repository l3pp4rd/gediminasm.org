<?php

namespace Gedmo\BlogBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Gedmo\BlogBundle\Entity\EmailMessage;

class EmailQueueCommand extends DoctrineCommand
{
    private $em;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('gedmo:blog:email')
            ->setDescription('Process email queue.')
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
        $output->writeLn('Updating blog articles');

        $dql = <<<____SQL
            SELECT e
            FROM GedmoBlogBundle:EmailMessage e
            WHERE e.status IN ('pending', 'failed')
____SQL;
        $emails = $this->em->createQuery($dql)->getResult();
        if (count($emails)) {
            $output->writeLn('Sending: ' . count($emails) . ' emails..');
            foreach ($emails as $email) {
                $message = \Swift_Message::newInstance()
                    ->setSubject($email->getSubject())
                    ->setCharset('utf-8')
                    ->setFrom(array($email->getEmail() => $email->getSender()))
                    ->setReplyTo(array($email->getEmail() => $email->getSender()))
                    ->setTo($email->getTarget())
                ;
                $message->addPart($email->getBody(), 'text/html', 'utf-8');
                try {
                    if (!$this->getContainer()->get('mailer')->send($message)) {
                        $email->setError('Sending failure.');
                        goto fail;
                    } else {
                        $email->setStatus('sent');
                        $email->setError(null);
                    }
                } catch (\Exception $e) {
                    $email->setError($e->getMessage());
                    fail: {
                        $email->incRetry();
                        $email->setStatus('failed');
                        $this->em->persist($email);
                    }
                }
                $this->em->flush();
            }
        }
    }
}