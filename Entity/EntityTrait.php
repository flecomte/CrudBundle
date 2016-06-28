<?php

namespace FLE\Bundle\CrudBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Security\Core\User\UserInterface;
use FLE\Bundle\CrudBundle\Annotations as CRUD;

trait EntityTrait
{
    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Expose
     * @Serializer\XmlElement(cdata=false)
     * @CRUD\CreateAt
     */
    protected $createAt;

    /**
     * @var UserInterface
     * @Serializer\Expose
     * @CRUD\CreateBy
     */
    protected $createBy;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     * @Serializer\Expose
     * @Serializer\XmlElement(cdata=false)
     * @CRUD\UpdateAt
     */
    protected $updateAt;

    /**
     * @var UserInterface
     * @Serializer\Expose
     * @CRUD\UpdateBy
     */
    protected $updateBy;

    /**
     * @return \DateTime
     */
    public function getCreateAt ()
    {
        return $this->createAt;
    }

    /**
     * @param \DateTime $createAt
     */
    public function setCreateAt ($createAt)
    {
        $this->createAt = $this->createAt === null ? $createAt : $this->createAt;
    }

    /**
     * @return UserInterface
     */
    public function getCreateBy ()
    {
        return $this->createBy;
    }

    /**
     * @param UserInterface $createBy
     */
    public function setCreateBy (UserInterface $createBy)
    {
        $this->createBy = $createBy;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateAt ()
    {
        return $this->updateAt;
    }

    /**
     * @param \DateTime $updateAt
     */
    public function setUpdateAt ($updateAt = null)
    {
        $this->updateAt = $updateAt instanceof \DateTime ? $updateAt : new \DateTime();
    }

    /**
     * @return UserInterface
     */
    public function getUpdateBy ()
    {
        return $this->updateBy;
    }

    /**
     * @param UserInterface $updateBy
     */
    public function setUpdateBy (UserInterface $updateBy)
    {
        $this->updateBy = $updateBy;
    }

    abstract public function __toString ();
}