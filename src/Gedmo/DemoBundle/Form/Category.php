<?php

namespace Gedmo\TestExtensionsBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextareaField;
use Gedmo\TestExtensionsBundle\Form\Transformer\Choise;
use Symfony\Component\Form\ChoiceField;

class Category extends Form
{
    protected function configure()
    {
        $this->addRequiredOption('repo');
        
        $this->setDataClass('Gedmo\\TestExtensionsBundle\\Entity\\Category');
        $this->add('title');
        $this->add(new TextareaField('description'));
        
        $this->add(
            new ChoiceField('parent', array(
                'choices' => $this->getOption('repo')->findAllParentChoises(null),
                'value_transformer' => new Choise($this->getOption('repo')),
                'empty_value' => '---',
                'required' => false,
            ))
        );
    }
}