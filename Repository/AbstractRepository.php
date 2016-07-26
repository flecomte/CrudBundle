<?php

namespace FLE\Bundle\CrudBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\Form;

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

    public function filterByForm (Form $form = null)
    {
        $qb = $this->createQueryBuilder('t');
        /** @var Form $subForm */
        foreach ($form->getIterator() as $key => $subForm) {
            $value = $subForm->getData();
            if ($value != null) {
                if ($subForm->getConfig()->getType()->getBlockPrefix() == "text") {
                    $qb->andWhere("lower(t.$key) LIKE lower(:$key)")->setParameter($key, '%'.$value.'%');
                } else {
                    $qb->andWhere("t.$key = :$key")->setParameter($key, $value);
                }
            }
        }
        return $qb->getQuery()->getResult();
    }
}
