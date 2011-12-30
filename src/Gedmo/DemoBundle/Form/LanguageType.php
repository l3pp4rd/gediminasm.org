<?php

namespace Gedi\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class LanguageType extends AbstractType
{
    public function getName()
    {
        return 'language';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('title', 'text');
    }
}