<?php

namespace FLE\Bundle\CrudBundle\Filter;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractType
 * @package FLE\Bundle\CrudBundle\Filter
 */
abstract class AbstractType extends BaseType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$builder->has('search')) {
            $builder
                ->add('search', TextType::class, [
                    'required' => false
                ]);
        }
        $builder->add('reset', ResetType::class, [
            'label' => 'form.reset'
        ]);
        $builder->setMethod('GET');
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('csrf_protection', false);
        $resolver->setDefault('horizontal_label_class', '');
        $resolver->setDefault('render_optional_text', false);
        $resolver->setDefault('required', false);

        parent::configureOptions($resolver);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($view->children as $child) {
            $child->vars['horizontal_label_class'] = $options['horizontal_label_class'];
            $child->vars['render_optional_text'] = $options['render_optional_text'];
            $child->vars['required'] = $options['required'];
        }
    }
}
