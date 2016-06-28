<?php

namespace FLE\Bundle\CrudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\MappedSuperclass()
 */
abstract class EntityAbstract implements EntityInterface
{
    use EntityTrait;

    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Expose
     * @Serializer\XmlAttribute
     */
    protected $id;

    /**
     * @return int
     */
    public function getId ()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId ($id)
    {
        $this->id = $id;
    }
}