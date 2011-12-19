<?php

namespace Gedmo\TestExtensionsBundle\Entity\Repository;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

class CategoryRepository extends NestedTreeRepository
{
    public function getRevisionListQuery($entity)
    {
        $objectClass = get_class($entity);
        $objectMeta = $this->_em->getClassMetadata($objectClass);
        $logEntryMeta = $this->_em->getClassMetadata('Stof\\DoctrineExtensionsBundle\\Entity\\LogEntry');
        $dql = "SELECT log FROM {$logEntryMeta->name} log";
        $dql .= " WHERE log.objectId = :objectId";
        $dql .= " AND log.objectClass = :objectClass";
        $dql .= " ORDER BY log.version DESC";
        
        $identifierField = $objectMeta->getSingleIdentifierFieldName();
        $objectId = $objectMeta->getReflectionProperty($identifierField)->getValue($entity);
        $q = $this->_em->createQuery($dql);
        $q->setParameters(compact('objectId', 'objectClass', 'order'));
        
        return $q;
    }
    
    public function getIdentifier($object)
    {
        $meta = $this->_em->getClassMetadata(get_class($object));
        $id = $meta->getSingleIdentifierFieldName();
        return $meta->getReflectionProperty($id)->getValue($object);
    }
    
    public function findAllParentChoises($node)
    {
        $dql = "SELECT c FROM {$this->_entityName} c";
        if (!is_null($node)) {
            $subSelect = $dql.' WHERE c.root = '.$node->getRoot();
            $subSelect .= ' AND c.lft BETWEEN '.$node->getLeft().' AND '.$node->getRight();
            
            $dql .= " WHERE c.id NOT IN ($subSelect)";
        }
        $q = $this->_em->createQuery($dql);
        $nodes = $q->getResult();
        $indexed = array();
        foreach ($nodes as $node) {
            $indexed[$node->getId()] = $node;
        }
        return $indexed;
    }
}