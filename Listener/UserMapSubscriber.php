<?php

namespace FLE\Bundle\CrudBundle\Listener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LoadClassMetadataEventArgs;
use FLE\Bundle\CrudBundle\Annotations as CRUD;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service()
 * @DI\Tag("doctrine.event_listener", attributes = {"event" = "loadClassMetadata"})
 */
class UserMapSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    protected $userClass;

    /**
     * @param $userClass
     * @DI\InjectParams({
     *     "userClass" = @DI\Inject("%fle_crud.user_class%")
     * })
     */
    public function setUserClass ($userClass)
    {
        $this->userClass = $userClass;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata,
        );
    }

    /**
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();
        $reader = new AnnotationReader();
        $class = $metadata->getReflectionClass();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            $annotation = $reader->getPropertyAnnotation($property, CRUD\CreateBy::class);
            if ($annotation instanceof CRUD\CreateBy && !$metadata->hasAssociation($property->getName())) {
//                var_dump($metadata->getName(), $property->getName(), $metadata->hasAssociation($property->getName()) ? $metadata->getAssociationMapping($property->getName()):'');
                $metadata->mapManyToOne([
                    'targetEntity' => $this->userClass,
                    'fieldName' => $property->getName()
                ]);
            }
            $annotation = $reader->getPropertyAnnotation($property, CRUD\UpdateBy::class);
            if ($annotation instanceof CRUD\UpdateBy && !$metadata->hasAssociation($property->getName())) {
                $metadata->mapManyToOne([
                    'targetEntity'  => $this->userClass,
                    'fieldName'     => $property->getName()
                ]);
            }
        }
    }
}