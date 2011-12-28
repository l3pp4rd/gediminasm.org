<?php

namespace Gedmo\DemoBundle\Form;

use Symfony\Component\Form\Form;

class Language extends Form
{
    protected function configure()
    {
        $this->setDataClass('Gedmo\\DemoBundle\\Entity\\Language');
        $this->add('title');
    }
}