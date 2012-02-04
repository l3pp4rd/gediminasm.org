<?php

namespace Gedmo\BlogBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Finder\Finder;
use Gedmo\BlogBundle\Entity\Article;

class UpdateArticlesCommand extends DoctrineCommand
{
    private $em;

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('gedmo:blog:update')
            ->setDescription('Updates blog articles.')
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
        //
        $this->updateExtensionArticles($output);
        $this->updateBlogArticles($output);
        $output->writeLn('Done');
    }

    private function updateBlogArticles(OutputInterface $output)
    {
        $output->writeLn('Updating blog articles');
        $refl = new \ReflectionClass('Gedmo\DoctrineExtensions');
        $finder = new Finder;
        $finder
            ->files()
            ->name('*.md')
            ->in(__DIR__.'/../Resources/articles')
        ;
        foreach ($finder as $fileInfo) {
            $fileContent = file_get_contents($fileInfo->getRealPath());
            $titleEnd = strpos($fileContent, "\n");
            $title = trim(substr($fileContent, 0, $titleEnd), ' #');
            $summaryEnd = strpos($fileContent, '[blog_reference]');

            $article = $this->em
                ->getRepository('Gedmo\BlogBundle\Entity\Article')
                ->findOneByTitle($title)
            ;
            if (!$article) {
                $article = new Article;
                $article->setTitle($title);
            }
            $article->setType(Article::TYPE_BLOG);
            $link = substr($fileContent, $summaryEnd, strpos($fileContent, "\n", $summaryEnd) - $summaryEnd);
            if (preg_match('@article/([^\s]+)\s"([^"]+)@smi', $link, $m)) {
                $article->setMeta($m[2]);
            }
            $article->setSummary($this->getContainer()->get('markdown.parser')->transform(
                trim(substr($fileContent, $titleEnd + 1, $summaryEnd - $titleEnd - 1))
            ));
            $content = trim(substr($fileContent, $summaryEnd));
            $article->setContent($this->readContent($content));

            $this->em->persist($article);
        }
        $this->em->flush();
    }

    private function readContent($githubFlaworedMarkdown)
    {
        $types = array();
        $markdown = preg_replace_callback("@```[ ]*([^\n]*)(.+?)```@smi", function ($m) use (&$types) {
            $types[] = trim($m[1]);
            return '    '.str_replace("\n", "\n    ", trim($m[2], "\r\n"));
        }, $githubFlaworedMarkdown);
        // if need to know a block type, theres a list $types

        return $this->getContainer()->get('markdown.parser')->transform($markdown);
    }

    private function updateExtensionArticles(OutputInterface $output)
    {
        $output->writeLn('Updating extension articles');
        $refl = new \ReflectionClass('Gedmo\DoctrineExtensions');
        $docDir = dirname($refl->getFileName()).'/../../doc';
        $finder = new Finder;
        $finder
            ->files()
            ->name('*.md')
            ->in($docDir)
        ;
        foreach ($finder as $fileInfo) {
            $fileContent = file_get_contents($fileInfo->getRealPath());
            $titleEnd = strpos($fileContent, "\n");
            $title = trim(substr($fileContent, 0, $titleEnd), ' #');
            $summaryEnd = strpos($fileContent, '[blog_reference]');

            $article = $this->em
                ->getRepository('Gedmo\BlogBundle\Entity\Article')
                ->findOneByTitle($title)
            ;
            if (!$article) {
                $article = new Article;
                $article->setTitle($title);
            }
            $article->setType(Article::TYPE_EXTENSION);
            $link = substr($fileContent, $summaryEnd, strpos($fileContent, "\n", $summaryEnd) - $summaryEnd);
            if (preg_match('@article/([^\s]+)\s"([^"]+)@smi', $link, $m)) {
                $article->setMeta($m[2]);
            }
            $article->setSummary($this->getContainer()->get('markdown.parser')->transform(
                trim(substr($fileContent, $titleEnd + 1, $summaryEnd - $titleEnd - 1))
            ));
            $content = trim(substr($fileContent, $summaryEnd));
            $article->setContent($this->readContent($content));
            $this->em->persist($article);
        }
        $this->em->flush();
    }
}