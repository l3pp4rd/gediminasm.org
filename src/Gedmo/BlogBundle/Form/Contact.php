<?php

namespace Gedmo\BlogBundle\Form;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextareaField;

class Contact extends Form
{
    protected function configure()
    {
        $this->setDataClass('Gedmo\\BlogBundle\\Model\\ContactMessage');
        $this->add(new TextareaField('message'));
        $this->add('sender');
        $this->add('email');
    }
}
