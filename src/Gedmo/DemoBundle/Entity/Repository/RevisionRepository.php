<?php

namespace Gedmo\DemoBundle\Entity\Repository;

use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

class RevisionRepository extends LogEntryRepository
{
    public function getRevisionListQuery($entity)
    {
        $objectClass = get_class($entity);
        $objectMeta = $this->_em->getClassMetadata($objectClass);
        $meta = $this->getClassMetadata();
        $dql = "SELECT log FROM {$meta->name} log";
        $dql .= " WHERE log.objectId = :objectId";
        $dql .= " AND log.objectClass = :objectClass";
        $dql .= " ORDER BY log.version DESC";
        
        $identifierField = $objectMeta->getSingleIdentifierFieldName();
        $objectId = $objectMeta->getReflectionProperty($identifierField)->getValue($entity);
        $q = $this->_em->createQuery($dql);
        $q->setParameters(compact('objectId', 'objectClass', 'order'));
        
        return $q;
    }
}