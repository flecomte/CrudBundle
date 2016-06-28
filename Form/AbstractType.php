<?php

namespace FLE\Bundle\CrudBundle\Form;

use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType as BaseType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractType
 * @package FLE\Bundle\CrudBundle\Form
 */
abstract class AbstractType extends BaseType
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @DI\InjectParams({
     *     "requestStack" = @DI\Inject("request_stack")
     * })
     * @param RequestStack          $requestStack
     */
    public function __construct (RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var EntityInterface $data */
        $data = $builder->getData();
        if ($data instanceof EntityInterface && $data->getId() !== null) {
            if ($builder->getMethod() == 'POST') {
                $builder->setMethod('PUT');
            }
        } elseif ($data instanceof EntityInterface) {
            $builder->setMethod('POST');
        }

        if ($options['submit_btn'] === true && !$builder->has('create') && !$builder->has('update') && !$builder->has('delete')) {
            if ($builder->getMethod() == 'POST') {
                $label = 'create';
            } elseif ($builder->getMethod() == 'PUT') {
                $label = 'update';
            } elseif ($builder->getMethod() == 'DELETE') {
                $label = 'delete';
            } else {
                $label = 'update';
            }
            $builder->add($label, SubmitType::class, [
                'validation_groups' => ['Default'],
            ]);
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->get('redirect') != null) {
            parse_str($request->getQueryString(), $q);
            $q['redirect'] = $request->get('redirect');
            $builder->setAction($options['action'].'?'.http_build_query($q));
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('submit_btn', true);
        $resolver->setDefault('cascade_validation', true);
    }
}
