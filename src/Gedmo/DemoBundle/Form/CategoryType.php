<?php

namespace Gedmo\DemoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;

class CategoryType extends AbstractType
{
    private $choiceList;

    public function __construct(EntityChoiceList $choiceList = null)
    {
        $this->choiceList = $choiceList;
    }

    public function getName()
    {
        return 'category';
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('title', 'text', array('required' => false));
        $builder->add('description', 'textarea', array('required' => false));

        $options = array(
            'class' => 'GedmoDemoBundle:Category',
            'empty_value' => '---',
            'required' => false,
        );
        if ($this->choiceList) {
            $options['choice_list'] = $this->choiceList;
        }
        $builder->add('parent', 'entity', $options);
    }
}