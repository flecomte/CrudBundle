<?php

namespace FLE\Bundle\CrudBundle\Form;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DeleteType
 * @package FLE\Bundle\CrudBundle\Form
 * @DI\FormType("DeleteType")
 */
class DeleteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->setMethod('DELETE');
        parent::buildForm($builder, $options);
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}
