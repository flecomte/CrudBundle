<?php

namespace FLE\Bundle\CrudBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Doctrine\ORM\EntityManager;
use FLE\Bundle\CrudBundle\Annotation\DisableSoftDelete;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class SoftDeleteAnnotationListener
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    public function __construct (Reader $reader, EntityManager $em, AuthorizationChecker $authorizationChecker)
    {
        $this->reader = $reader;
        $this->em = $em;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        list($controller, $method, ) = $controller;

        $this->disableSoftDeleteAnnotation ($controller, $method);
    }

    /**
     * @param $controller
     * @param $method
     *
     * @return bool
     */
    private function readAnnotation ($controller, $method)
    {
        $objectReflection = new \ReflectionObject($controller);
        $methodReflection = $objectReflection->getMethod($method);
        $methodAnnotation = $this->reader->getMethodAnnotation($methodReflection, DisableSoftDelete::class);
        if (!$methodAnnotation instanceof DisableSoftDelete) {
            return false;
        }

        $roles = $methodAnnotation->roles;
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        if (empty($roles)) {
            return true;
        }
        $allow = false;
        foreach ($roles as $role) {
            if ($this->authorizationChecker->isGranted($role)) {
                $allow = true;
            }
        }

        return $allow;
    }

    /**
     * @param $controller
     * @param $method
     */
    private function disableSoftDeleteAnnotation ($controller, $method)
    {
        if ($this->readAnnotation($controller, $method)) {
            if (in_array('softdeleteable', array_keys($this->em->getFilters()->getEnabledFilters()))) {
                $this->em->getFilters()->disable('softdeleteable');
            }
        }
    }
}