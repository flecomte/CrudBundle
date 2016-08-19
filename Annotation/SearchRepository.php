<?php

namespace FLE\Bundle\CrudBundle\Annotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class SearchRepository
{
    public $method = 'filterByForm';
}
