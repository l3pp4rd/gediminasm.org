<?php

namespace Gedmo\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CommentType extends AbstractType
{
    public function getName()
    {
        return 'comment';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('author', 'text', array('required' => false));
        $builder->add('subject', 'text', array('required' => false));
        $builder->add('content', 'textarea');
    }
}