<?php

namespace FLE\Bundle\CrudBundle\Form;

use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
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
     * @var Router
     */
    protected $router;

    /**
     * @DI\InjectParams({
     *     "requestStack" = @DI\Inject("request_stack"),
     *     "router" = @DI\Inject("router")
     * })
     * @param RequestStack $requestStack
     * @param Router       $router
     */
    public function __construct (RequestStack $requestStack, Router $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
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
            $builder->add($label, SubmitType::class);
        }
        if (!empty($options['data_class'])) {
            preg_match('`[^\\\\]*$`', $options['data_class'], $matches);
            if (empty($options['action']) && $builder->getAction() == "" && isset($matches[0])) {
                $entityName = $matches[0];
                $route = strtolower($builder->getMethod().'_'.$entityName);
                if (!empty($entityName) && $this->router->getRouteCollection()->get($route)) {
                    if ($builder->getMethod() == 'PUT' || $builder->getMethod() == 'PATCH' || $builder->getMethod() == 'LINK' || $builder->getMethod() == 'UNLINK') {
                        $id = $builder->getData()->getId();
                        $options['action'] = $this->router->generate($route, [lcfirst($entityName) => $id]);
                    } else {
                        $options['action'] = $this->router->generate($route);
                    }
                    $builder->setAction($options['action']);
                }
            }
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
