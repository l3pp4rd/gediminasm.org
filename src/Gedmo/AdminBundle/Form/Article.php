<?php

namespace Gedmo\AdminBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextareaField;

class Article extends Form
{
    protected function configure()
    {
        $this->setDataClass('Gedmo\\BlogBundle\\Entity\\Article');
        $this->add(new TextareaField('content'));
        $this->add(new TextareaField('header'));
        $this->add('title');
        $this->add('id');
        $this->add('metaKeys');
        $this->add('metaDescription');
    }
}