<?php

namespace Gedmo\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;

class CategoryType extends AbstractType
{
    private $node;

    public function __construct($node = null)
    {
        $this->node = $node;
    }

    public function getName()
    {
        return 'category';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'text', array('required' => false));
        $builder->add('description', 'textarea', array('required' => false));

        $node = &$this->node;
        // @todo: not possible to set translatable hint??
        $builder->add('parent', 'entity', array(
            'class' => 'GedmoDemoBundle:Category',
            'empty_value' => '---',
            'required' => false,
            'query_builder' => function(EntityRepository $repo) use ($node) {
                $qb = $repo->createQueryBuilder('c');
                if (!is_null($node)) {
                    $qb->where($qb->expr()->notIn(
                        'c.id',
                        $repo
                            ->createQueryBuilder('n')
                            ->where('n.root = '.$node->getRoot())
                            ->andWhere($qb->expr()->between('n.lft', $node->getLeft(), $node->getRight()))
                            ->getDQL()
                    ));
                }
                return $qb;
            },
        ));
    }
}