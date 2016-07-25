<?php

namespace FLE\Bundle\CrudBundle\Output;

use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class OutputEntityArray
{
    /**
     * @var EntityInterface[]
     * @Serializer\Expose
     * @Serializer\Groups({"Default", "Output"})
     * @Serializer\Type("array<FLE\Bundle\CrudBundle\Entity\EntityInterface>")
     * @Serializer\XmlElement(cdata=false)
     */
    protected $entities;

    /**
     * @var ConstraintViolationListInterface
     * @Serializer\Expose
     * @Serializer\Groups({"Default", "Output"})
     * @Serializer\Since("1.0")
     */
    protected $errors;

    /**
     * OutputEntityArray constructor.
     *
     * @param EntityInterface[] $entity
     */
    public function __construct ($entity)
    {
        $this->entities = $entity;
    }

    /**
     * @return EntityInterface[]
     */
    public function getEntities ()
    {
        return $this->entities;
    }

    /**
     * @param EntityInterface[] $entities
     */
    public function setEntities ($entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getErrors ()
    {
        return $this->errors;
    }

    /**
     * @param ConstraintViolationListInterface $errors
     */
    public function setErrors (ConstraintViolationListInterface $errors)
    {
        $this->errors = $errors;
    }
}