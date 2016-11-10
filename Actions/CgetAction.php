<?php

namespace FLE\Bundle\CrudBundle\Actions;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\FilterCollection;
use FLE\Bundle\CrudBundle\Entity\EntityAbstract;
use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use FLE\Bundle\CrudBundle\Repository\AbstractRepository;
use FOS\ElasticaBundle\Repository as SearchRepository;
use FOS\RestBundle\View\View;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

class CgetAction extends ActionAbstract
{
    /**
     * @var Form
     */
    protected $formFilter;

    /**
     * @var string
     */
    protected $entityName;

    /**
     * @var Query
     */
    protected $query;

    public function __construct ($entityName)
    {
        if (!is_string($entityName)) {
            $type = gettype($entityName);
            throw new \Exception('The argument $entityName don\'t must be a string '.$type.' given');
        }
        if (class_exists($entityName)) {
            $this->entityName = $entityName;
        } else {
            throw new \Exception("The class $entityName does not exist");
        }
    }

    /**
     * @return Form
     */
    public function getFormFilter ()
    {
        return $this->formFilter;
    }

    /**
     * @param Form $formFilter
     */
    public function setFormFilter ($formFilter)
    {
        $this->formFilter = $formFilter;
    }

    /**
     * @return string
     */
    public function getEntityName ()
    {
        return $this->entityName;
    }

    /**
     * @param string $entityName
     */
    public function setEntityName ($entityName)
    {
        $this->entityName = $entityName;
    }

    /**
     * @return Query
     */
    public function getQuery ()
    {
        return $this->query;
    }

    /**
     * @param Query $query
     */
    public function setQuery ($query)
    {
        $this->query = $query;
    }

    /**
     * @return View
     * @throws \Exception
     */
    public function build ()
    {
        $entityName = $this->getEntityName();
        $classBaseName = $this->getClassBaseName($entityName);
        $view = $this->view();

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var AbstractRepository $repository */
        $repository = $em->getRepository($entityName);

        /** @var FilterCollection $filters */
        $filters = $em->getFilters();
        if (in_array('softdeleteable', array_keys($filters->getEnabledFilters()))) {
            $filters->disable('softdeleteable');
        }

        $result = $this->getQuery();
        if ($result === null && ($formFilter = $this->getFormFilter() ?: $this->createFormFilter($entityName)) !== null) {
            $formFilter->handleRequest($request);
            if ($formFilter->isValid() || !$formFilter->isSubmitted()) {
                $searchRepository = $this->getElasticaSearchRepository($entityName);
                if ($searchRepository instanceof SearchRepository) {
                    $result = $searchRepository->createPaginatorAdapter($formFilter);
                } elseif (!($result = $this->filterByForm($entityName, $formFilter))) {
                    throw new \Exception('No Repository for filter');
                }
            }
            $this->addViewData('filter', $formFilter->createView());
        }

        if ($this->container->has('knp_paginator') && $request->getRequestFormat() === 'html') {
            if ($result === null) {
                $result = $repository->createQueryBuilder(strtolower($classBaseName))->getQuery();
            }
            /** @var Paginator $paginator */
            $paginator = $this->container->get('knp_paginator');
            /** @var SlidingPagination $entities */
            $entities = $paginator->paginate(
                $result,
                $request->query->getInt('page', 1),
                $request->query->getInt('limit', 10)
            );
        } else {
            $entities = $repository->findAll();
        }

        $this->addViewData($this->plural($classBaseName, true), $entities);

        $deleteForms = $this->createDeleteForms($entities);
        $this->addViewData('delete_form', $deleteForms);

        return $view;
    }

    /**
     * @param $entities
     *
     * @return \Symfony\Component\Form\FormView[]
     */
    public function createDeleteForms ($entities)
    {
        $deleteForms = [];
        /** @var EntityInterface $entity */
        foreach ($entities as $entity) {
            if (method_exists($entity, 'isDeleted') && $entity->isDeleted()) {
                $restoreForm = $this->createRestoreForm($entity);
                if ($restoreForm instanceof Form) {
                    $deleteForms[$entity->getId()] = $restoreForm->createView();
                }
            } else {
                $deleteForm = $this->createDeleteForm($entity);
                if ($deleteForm instanceof Form) {
                    $deleteForms[$entity->getId()] = $deleteForm->createView();
                }
            }
        };
        return $deleteForms;
    }

    /**
     * @return bool|EntityAbstract[]
     * @throws \Exception
     */
    public function getEntities ()
    {
        $key = $this->plural(strtolower($this->getClassBaseName($this->getEntityName())), true);
        $entities = $this->getViewData($key);
        if (!$entities) {
            $this->build();
            $entities = $this->getViewData($this->getEntityName());
        }
        return $entities;
    }
}