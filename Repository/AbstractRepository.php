<?php

namespace FLE\Bundle\CrudBundle\Repository;

use Doctrine\ORM\EntityRepository;
use FLE\Bundle\CrudBundle\Annotation\SearchRepository;
use Symfony\Component\Form\Form;

/**
 * @SearchRepository(method="filterByForm")
 *
 * @deprecated
 */
class AbstractRepository extends EntityRepository
{
    public function findAll ()
    {
        return parent::findBy([], ['id' => 'ASC']);
    }

    /**
     * @param Form|null $form
     *
     * @return array
     */
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
