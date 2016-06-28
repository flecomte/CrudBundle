<?php

namespace FLE\Bundle\CrudBundle\Listener;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\EventSubscriber;
use FLE\Bundle\CrudBundle\Annotations as CRUD;
use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @DI\Service()
 * @DI\Tag("doctrine.event_listener", attributes = {"event" = "prePersist"})
 * @DI\Tag("doctrine.event_listener", attributes = {"event" = "preUpdate"})
 */
class UserSetSubscriber implements EventSubscriber
{
    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @param $tokenStorage
     * @DI\InjectParams({
     *     "tokenStorage" = @DI\Inject("security.token_storage")
     * })
     */
    public function setTokenStorage ($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::prePersist,
            Events::preUpdate,
        );
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getObjectManager()->getClassMetadata(get_class($eventArgs->getEntity()));

        $reader = new AnnotationReader();
        $class = $metadata->getReflectionClass();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            /** @var EntityInterface $entity */
            $entity = $eventArgs->getObject();

            $annotation = $reader->getPropertyAnnotation($property, CRUD\CreateAt::class);
            $methodName = 'set'.ucfirst($property->getName());
            if ($annotation instanceof CRUD\CreateAt) {
                if ($class->hasMethod($methodName)) {
                    $class->getMethod($methodName)->invoke($entity, new \DateTime());
                } else {
                    $class->getProperty($property->getName())->setValue($entity, new \DateTime());
                }
            }

            if ($this->tokenStorage->getToken() instanceof TokenInterface && ($user = $this->tokenStorage->getToken()->getUser()) instanceof UserInterface) {
                $user = $this->tokenStorage->getToken()->getUser();
                $annotation = $reader->getPropertyAnnotation($property, CRUD\CreateBy::class);
                if ($annotation instanceof CRUD\CreateBy) {
                    if ($class->hasMethod($methodName)) {
                        $class->getMethod($methodName)->invoke($entity, $user);
                    } else {
                        $class->getProperty($property->getName())->setValue($entity, $user);
                    }
                }
            }
        }
    }

    /**
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getObjectManager()->getClassMetadata(get_class($eventArgs->getEntity()));

        $reader = new AnnotationReader();
        $class = $metadata->getReflectionClass();
        $properties = $class->getProperties();
        foreach ($properties as $property) {
            /** @var EntityInterface $entity */
            $entity = $eventArgs->getObject();

            $annotation = $reader->getPropertyAnnotation($property, CRUD\UpdateAt::class);
            $methodName = 'set'.ucfirst($property->getName());
            if ($annotation instanceof CRUD\UpdateAt) {
                if ($class->hasMethod($methodName)) {
                    $class->getMethod($methodName)->invoke($entity, new \DateTime());
                } else {
                    $class->getProperty($property->getName())->setValue($entity, new \DateTime());
                }
            }

            if ($this->tokenStorage->getToken() instanceof TokenInterface && ($user = $this->tokenStorage->getToken()->getUser()) instanceof UserInterface) {
                $user = $this->tokenStorage->getToken()->getUser();
                $annotation = $reader->getPropertyAnnotation($property, CRUD\UpdateBy::class);
                if ($annotation instanceof CRUD\UpdateBy) {
                    if ($class->hasMethod($methodName)) {
                        $class->getMethod($methodName)->invoke($entity, $user);
                    } else {
                        $class->getProperty($property->getName())->setValue($entity, $user);
                    }
                }
            }
        }
    }
}