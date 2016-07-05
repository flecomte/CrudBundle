<?php

namespace FLE\Bundle\CrudBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class DisableSoftDelete
{
    public $roles;
}
