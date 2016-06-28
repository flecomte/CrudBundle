<?php

namespace FLE\Bundle\CrudBundle\Twig\Extension;

class WordExtension extends \Twig_Extension
{
    public function getName()
    {
        return 'word_extension';
    }

    public function getFilters ()
    {
        return array(
            new \Twig_SimpleFilter('ucfirst', 'ucfirst'),
        );
    }
}
