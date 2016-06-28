<?php

namespace FLE\Bundle\CrudBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * Class AbstractRepository
 * @package FLE\Bundle\CrudBundle\Repository
 */
class AbstractRepository extends EntityRepository
{
    public function findAll ()
    {
        return parent::findBy([], ['id' => 'ASC']);
    }
}
