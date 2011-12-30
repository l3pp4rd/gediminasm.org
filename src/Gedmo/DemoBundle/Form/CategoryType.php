<?php

namespace Gedmo\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class CategoryType extends AbstractType
{
    public function getName()
    {
        return 'category';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'text', array('required' => false));
        $builder->add('description', 'textarea', array('required' => false));
    }
}