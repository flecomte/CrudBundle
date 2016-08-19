<?php

namespace FLE\Bundle\CrudBundle\Actions;

use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\RequestStack;

class GetAction extends ActionAbstract
{
    /**
     * @var EntityInterface
     */
    protected $entity;

    public function __construct (EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity ()
    {
        return $this->entity;
    }

    /**
     * @param EntityInterface $entity
     */
    public function setEntity ($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return View
     * @throws \Exception
     */
    public function build ()
    {
        $view = $this->view();

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        $className = strtolower($this->getClassBaseName($this->getEntity()));
        $view->setData([
            $className    => $this->getEntity(),
            'delete_form' => $this->createDeleteForm($this->getEntity(), $request)->createView(),
        ]);
        return $view;
    }
}