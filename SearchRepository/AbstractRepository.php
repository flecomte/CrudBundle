<?php

namespace FLE\Bundle\CrudBundle\SearchRepository;

use FOS\ElasticaBundle\Repository;
use Elastica\Query;
use Elastica\Filter;
use FLE\Bundle\CrudBundle\Entity\EntityAbstract;
use Symfony\Component\Form\Form;

abstract class AbstractRepository extends Repository
{
    /**
     * @param Form|Query|string $form
     * @param array             $options
     *
     * @return \FOS\ElasticaBundle\Paginator\PaginatorAdapterInterface
     */
    public function createPaginatorAdapter($form, $options = array())
    {
        if (!$form instanceof Form) {
            return parent::createPaginatorAdapter($form);
        }

        $query = $this->findQuery($form, $options);
        return parent::createPaginatorAdapter($query);
    }

    /**
     * @param Form|Query|string $form
     * @param integer           $limit
     * @param array             $options
     *
     * @return EntityAbstract[]
     */
    public function find($form, $limit = null, $options = array())
    {
        if (!$form instanceof Form) {
            return parent::find($form);
        }

        $query = $this->findQuery($form, $options);
        return parent::find($query);
    }

    /**
     * @param Form|Query|string $form
     * @param array             $options
     *
     * @return Query
     */
    abstract public function findQuery($form, $options = array());
}
