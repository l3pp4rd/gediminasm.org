<?php

namespace Gedmo\BlogBundle\Form;

use Symfony\Component\Form\Form;

class Comment extends Form
{
    protected function configure()
    {
        $this->setDataClass('Gedmo\\BlogBundle\\Entity\\Comment');
        $this->add('author');
        $this->add('subject');
        $this->add('content');
    }
}
