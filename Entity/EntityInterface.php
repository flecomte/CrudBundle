<?php

namespace FLE\Bundle\CrudBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

interface EntityInterface
{
    /**
     * @return int
     */
    public function getId ();

    /**
     * @return \DateTime
     */
    public function getCreateAt ();

    /**
     * @param \DateTime $createAt
     */
    public function setCreateAt ($createAt);

    /**
     * @return UserInterface
     */
    public function getCreateBy ();

    /**
     * @param UserInterface $createBy
     */
    public function setCreateBy (UserInterface $createBy);

    /**
     * @return \DateTime
     */
    public function getUpdateAt ();

    /**
     * @param \DateTime $updateAt
     */
    public function setUpdateAt ($updateAt);

    /**
     * @return UserInterface
     */
    public function getUpdateBy ();

    /**
     * @param UserInterface $updateBy
     */
    public function setUpdateBy (UserInterface $updateBy);

    /**
     * @return string
     */
    public function __toString ();
}