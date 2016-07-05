<?php

namespace FLE\Bundle\CrudBundle\Form;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class DeleteType
 * @package FLE\Bundle\CrudBundle\Form
 * @DI\FormType("RestoreType")
 */
class RestoreType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('restore', SubmitType::class)
            ->setMethod('PATCH');
        $options['submit_btn'] = false;
        parent::buildForm($builder, $options);
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('submit_btn', false);
        parent::configureOptions($resolver);
    }
}
