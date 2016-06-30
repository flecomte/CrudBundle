<?php

namespace FLE\Bundle\CrudBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use FOS\ElasticaBundle\Manager\RepositoryManager;
use FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface;
use FOS\ElasticaBundle\Repository as SearchRepository;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use FLE\Bundle\CrudBundle\Form\DeleteType;
use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use FLE\Bundle\CrudBundle\Repository\AbstractRepository;
use FLE\Bundle\CrudBundle\SearchRepository\AbstractRepository as AbstractSearchRepository;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ControllerAbstract
 * @package FLE\Bundle\CrudBundle\Controller
 */
abstract class ControllerAbstract extends FOSRestController
{
    /**
     * @param null|string|EntityInterface $class
     *
     * @return EntityRepository
     */
    protected function getRepository ($class = null)
    {
        if ($class === null) {
            $class = $this->getBundleName($this).':'.$this->getControllerName($this);
        } elseif (is_object($class)) {
            $class = get_class($class);
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository($class);
    }

    /**
     * @param null|string|EntityInterface $class
     *
     * @return AbstractSearchRepository
     */
    protected function getSearchRepository ($class = null)
    {
        if ($class === null) {
            $class = $this->getBundleName($this).':'.$this->getControllerName($this);
        } elseif (is_object($class)) {
            $class = get_class($class);
        }

        if ($this->container->has('fos_elastica.manager')) {
            /** @var RepositoryManager $repositoryManager */
            $repositoryManager = $this->container->get('fos_elastica.manager');
            return $repositoryManager->getRepository($class);
        } else {
            return null;
        }
    }

    /**
     * @param Request                         $request
     * @param Query|PaginatorAdapterInterface $query
     * @param string                          $bundle
     *
     * @return View
     * @throws \Exception
     */
    protected function getEntitiesAction(Request $request, $query = null, $bundle = null)
    {
        if ($bundle === null) {
            $bundle = $this->getBundleName($this);
        }
        $view = $this->view();
        $output = [];
        $className = $this->getControllerName($this);
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        /** @var AbstractRepository $repository */
        $repository = $em->getRepository($bundle.':'.$className);

        if ($query === null && ($formFilter = $this->createFormFilter()) !== null) {
            $formFilter->handleRequest($request);
            if ($formFilter->isValid() && $formFilter->isSubmitted()) {
                $searchRepository = $this->getSearchRepository();
                if ($searchRepository instanceof SearchRepository) {
                    $query = $searchRepository->createPaginatorAdapter($formFilter);
                } elseif (method_exists($repository, 'findQuery')) {
                    $query = $repository->findQuery($formFilter);
                } else {
                    throw new \Exception('No Repository for filter');
                }
            }
            $output['filter'] = $formFilter->createView();
        }

        $className = $this->getClassName($className);
        if ($this->has('knp_paginator') && $request->getRequestFormat() === 'html') {
            if ($query === null) {
                $query = $repository->createQueryBuilder(strtolower($className))->getQuery();
            }
            /** @var Paginator $paginator */
            $paginator = $this->get('knp_paginator');
            /** @var SlidingPagination $entities */
            $entities = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 10)
            );
        } else {
            $entities = $repository->findAll();
        }

        $output[$this->plural($className, true)] = $entities;
        $view->setData($output);
        return $view;
    }

    /**
     * @param Request         $request
     * @param EntityInterface $entity
     * @param string          $type
     *
     * @return View
     */
    protected function newEntityAction(Request $request, EntityInterface $entity, $type = null)
    {
        $className = strtolower($this->getClassName($entity));
        $form = $this->createForm($type, $entity);
        $view = $this->view();

        $view->setData([
            $className => $entity,
            'form' => $form->createView(),
        ]);
        return $view;
    }

    /**
     * @param Request         $request
     * @param EntityInterface $entity
     *
     * @return View
     */
    protected function getEntityAction(Request $request, EntityInterface $entity)
    {
        $view = $this->view();
        $className = strtolower($this->getClassName($entity));
        $view->setData([
            $className => $entity,
            'delete_form' => $this->createDeleteForm($entity)->createView(),
        ]);
        return $view;
    }

    /**
     * @param Request         $request
     * @param EntityInterface $entity
     * @param null            $redirectRoute
     * @param null            $type
     *
     * @return View
     */
    protected function postEntityAction(Request $request, EntityInterface $entity, $redirectRoute = null, $type = null)
    {
        $className = strtolower($this->getClassName($entity));
        $form = $this->createForm($type, $entity);
        $form->handleRequest($request);
        $view = $this->view();

        if ($form->isSubmitted() && $form->isValid() && ($this->isGranted('ROLE_API') || ($form->has('create') && $form->get('create')->isClicked()))) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            if ($this->has('fos_elastica.index_manager')) {
                $this->get('fos_elastica.index_manager')->getIndex('app')->refresh();
            }

            $this->addFlash('success', $className.'.flash.post.success');

            if ($redirectRoute === null && $request->get('redirect') != null) {
                $view->setLocation($request->get('redirect', $this->createRoute($entity, 'get', true)));
            } else {
                $view->setRoute($redirectRoute ?: $this->createRoute($entity, 'get', true));
            }
        } else {
            $view->setStatusCode(400);
        }

        $view->setData([
            $className => $entity,
            'form' => $form->createView(),
        ]);
        return $view;
    }

    /**
     * @param Request         $request
     * @param EntityInterface $entity
     * @param null            $type
     *
     * @return View
     */
    protected function editEntityAction(Request $request, EntityInterface $entity, $type = null)
    {
        $className = strtolower($this->getClassName($entity));
        $form = $this->createForm($type, $entity);
        $view = $this->view();
        $view->setData([
            $className => $entity,
            'form' => $form->createView(),
            'delete_form' => $this->createDeleteForm($entity)->createView(),
        ]);
        return $view;
    }

    /**
     * @param EntityInterface $entity
     * @param Request         $request
     * @param null            $redirectRoute
     * @param AbstractType    $type
     *
     * @return View
     */
    protected function putEntityAction(Request $request, EntityInterface $entity, $redirectRoute = null, $type = null)
    {
        $className = strtolower($this->getClassName($entity));

        $form = $this->createForm($type, $entity);
        $form->handleRequest($request);
        $view = $this->view();

        if ($form->isSubmitted() && $form->isValid() && ($this->isGranted('ROLE_API') || ($form->has('update') && $form->get('update')->isClicked()))) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            if ($this->has('fos_elastica.index_manager')) {
                $this->get('fos_elastica.index_manager')->getIndex('app')->refresh();
            }

            if ($redirectRoute === null && $request->get('redirect') != null) {
                $view->setLocation($request->get('redirect', $this->createRoute($entity, 'get', true)));
            } else {
                $view->setRoute($redirectRoute ?: $this->createRoute($entity, 'get', true));
            }
            $view->setStatusCode(200);
        } else {
            $view->setStatusCode(400);
        }

        $view->setData([
            $className => $entity,
            'form'     => $form->createView(),
            'delete_form' => $this->createDeleteForm($entity)->createView(),
        ]);
        return $view;
    }

    /**
     * @param Request         $request
     * @param EntityInterface $entity
     * @param                 $redirectRoute
     * @param array           $routeParameters
     *
     * @return View
     */
    protected function deleteEntityAction(Request $request, EntityInterface $entity , $redirectRoute = null, $routeParameters = [])
    {
        if ($redirectRoute === null) {
            $redirectRoute = $this->createRoute($entity, 'get', true);
        }

        $view = $this->view();
        $form = $this->createDeleteForm($entity);
        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid()) || $this->isGranted('ROLE_API')) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();
            if ($this->has('fos_elastica.index_manager')) {
                $this->get('fos_elastica.index_manager')->getIndex('app')->refresh();
            }
            $view->setRoute($redirectRoute);
            $view->setRouteParameters($routeParameters);
            $view->setStatusCode(204);
        } else {
            $view->setStatusCode(400);
        }

        return $view;
    }

    /**
     * @param EntityInterface $entity
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createDeleteForm (EntityInterface $entity)
    {
        $className = $this->getClassName($entity);
        $className = $this->UpperToLowerUnderscore($className);

        return $this->createForm(DeleteType::class, null, [
            'action' => $this->generateUrl($this->createRoute($entity, 'delete'), array(lcfirst($className) => $entity->getId()))
        ]);
    }

    /**
     * @param $entity
     *
     * @return string
     */
    protected function getClassName ($entity)
    {
        if ($entity instanceof EntityInterface) {
            $entity = get_class($entity);
        }
        preg_match('`([^\\\\]+)$`', $entity, $matches);
        return $matches[1];
    }

    /**
     * @param $entity
     *
     * @return string
     */
    protected function getClassFullName (EntityInterface $entity)
    {
        preg_match('`Entity\\\\(.*)`', get_class($entity), $matches);
        return $matches[1];
    }

    /**
     * @param Controller $controller
     *
     * @return string
     */
    protected function getControllerName (Controller $controller)
    {
        preg_match('`Controller\\\\(.*)Controller`', get_class($controller), $matches);
        return $matches[1];
    }

    /**
     * @param Controller $controller
     *
     * @return string
     */
    protected function getBundleName (Controller $controller)
    {
        preg_match('`([^\\\\]*Bundle)`', get_class($controller), $matches);
        return $matches[1];
    }

    private function getFormTypeName (EntityInterface $entity)
    {
        preg_match('`(.*)\\\\Entity\\\\(.*)`', get_class($entity), $matches);
        return $matches[1].'\\Form\\'.$matches[2].'Type';
    }

    /**
     * @return string|null
     */
    private function getFormFilterTypeName ()
    {
        preg_match('`(.*)\\\\Controller\\\\(.*)Controller`', get_class($this), $matches);
        if (class_exists($matches[1].'\\Filter\\'.$matches[2].'FilterType')) {
            return $matches[1].'\\Filter\\'.$matches[2].'FilterType';
        } elseif (class_exists($matches[1].'\\Filter\\'.$matches[2].'Type')) {
            return $matches[1].'\\Filter\\'.$matches[2].'Type';
        } else {
            return null;
        }
    }

    /**
     * @param string $type
     * @param EntityInterface  $data
     * @param array  $options
     *
     * @return \Symfony\Component\Form\Form
     */
    protected function createForm($type = null, $data = null, array $options = array())
    {
        if ($type === null) {
            $type = $this->getFormTypeName($data);
        }
        return parent::createForm($type, $data, $options);
    }

    protected function createFormFilter($type = null, $data = null, array $options = array())
    {
        if ($type === null) {
            $type = $this->getFormFilterTypeName();
            if (!class_exists($type)) {
                return null;
            }
        }
        return parent::createForm($type, $data, $options);
    }

    /**
     * @param EntityInterface $entity
     * @param string          $method
     * @param bool            $plural
     *
     * @return string
     */
    protected function createRoute (EntityInterface $entity, $method = 'get', $plural = false)
    {
        $className = $this->getClassName($entity);
        if ($plural) {
            return strtolower($method).'_'.$this->plural($className, true);
        } else {
            return strtolower($method.'_'.$className);
        }
    }

    protected function plural ($name, $lower = false)
    {
        $count = preg_match_all('`[A-Z]+[a-z]+`', $name, $matches);
        if ($count > 1) {
            list($first, $rest) = $matches[0];
            $pl = substr($first, -1) == 'y' ? substr($first, 0, -1).'ies'.$rest : $first.'s'.$rest;
        } else {
            $pl = substr($name, -1) == 'y' ? substr($name, 0, -1).'ies' : $name.'s';
        }
        return $lower ? $this->UpperToLowerUnderscore($pl) : $pl;
    }

    protected function UpperToLowerUnderscore ($name)
    {
        return strtolower(preg_replace('/\B([A-Z])/', '_$1', $name));
    }

    protected function addFlash($type, $message)
    {
        if ($this->container->get('request_stack')->getCurrentRequest()->getRequestFormat() === 'html') {
            parent::addFlash($type, $message);
        }
    }
}
