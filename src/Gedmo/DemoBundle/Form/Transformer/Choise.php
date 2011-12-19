<?php

namespace Gedmo\TestExtensionsBundle\Form\Transformer;

use Symfony\Component\Form\ValueTransformer\ValueTransformerInterface;
use Doctrine\ORM\EntityRepository;

class Choise implements ValueTransformerInterface
{
    /**
     * Entity repository
     *
     * @var EntityRepository
     */
    private $repository = null;

    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }
    
    /**
     * Transforms an object into an id
     *
     * @param object $value
     * @return string
     */
    public function transform($value)
    {
        \Doctrine\Common\Util\Debug::dump($value);
        if (is_null($value)) {
            return 'NULL';
        }

        if (!is_object($value)) {
            throw new \InvalidArgumentException(sprintf('Expected argument of type object but got %s.', gettype($value)));
        }

        return $this->repository->getIdentifier($value);
    }
    
    public function reverseTransform($value)
    {
        return $this->repository->find($value);
    }
}