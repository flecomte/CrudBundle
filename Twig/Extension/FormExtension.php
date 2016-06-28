<?php

namespace FLE\Bundle\CrudBundle\Twig\Extension;

use Symfony\Component\Form\FormView;

class FormExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'form_extension';
    }

    public function getFunctions ()
    {
        return array(
            new \Twig_SimpleFunction('getParent', [$this, 'getParent']),
            new \Twig_SimpleFunction('getMethod', [$this, 'getMethod']),
        );
    }

    public function getParent (FormView $form)
    {
        $parent = $form;
        while ($parent->parent !== null)
        {
            $parent = $parent->parent;
        }
        return $parent;
    }

    public function getMethod (FormView $form)
    {
        $parent = $this->getParent($form);
        return $parent->vars['method'];
    }
}
