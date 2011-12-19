<?php

namespace Gedmo\TestExtensionsBundle\Form;

use Symfony\Component\Form\Form;

class Language extends Form
{
    protected function configure()
    {
        $this->setDataClass('Gedmo\\TestExtensionsBundle\\Entity\\Language');
        $this->add('title');
    }
}