<?php

namespace Gedmo\DemoBundle\Entity\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Doctrine\ORM\Query;
use Gedmo\DemoBundle\Entity\Category;

class CategoryRepository extends NestedTreeRepository
{
    public function findAllParentChoises(Category $node = null)
    {
        $dql = "SELECT c FROM {$this->_entityName} c";
        if (!is_null($node)) {
            $subSelect = $dql.' WHERE c.root = :root';
            $subSelect .= ' AND c.lft BETWEEN :left AND :right';

            $dql .= " WHERE c.id NOT IN ({$subSelect})";
        }
        $q = $this->_em->createQuery($dql);
        $q->setParameters(array(
            'root' => $node->getRoot(),
            'left' => $node->getLeft(),
            'right' => $node->getRight()
        ));
        $q->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker'
        );
        $nodes = $q->getResult();
        $indexed = array();
        foreach ($nodes as $node) {
            $indexed[$node->getId()] = $node;
        }
        return $indexed;
    }
}