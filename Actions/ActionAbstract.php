<?php

namespace FLE\Bundle\CrudBundle\Actions;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use FLE\Bundle\CrudBundle\Annotation as CRUD;
use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use FLE\Bundle\CrudBundle\Form\DeleteType;
use FLE\Bundle\CrudBundle\Form\RestoreType;
use FLE\Bundle\CrudBundle\SearchRepository\AbstractRepository as AbstractSearchRepository;
use FOS\ElasticaBundle\Manager\RepositoryManager;
use FOS\RestBundle\View\View;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;

abstract class ActionAbstract implements ActionInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var View
     */
    protected $view;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer (ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Shortcut to return the Doctrine Registry service.
     *
     * @return Registry
     *
     * @throws \LogicException If DoctrineBundle is not available
     */
    protected function getDoctrine()
    {
        if (!$this->container->has('doctrine')) {
            throw new \LogicException('The DoctrineBundle is not registered in your application.');
        }

        return $this->container->get('doctrine');
    }

    /**
     * Checks if the attributes are granted against the current authentication token and optionally supplied object.
     *
     * @param mixed $attributes The attributes
     * @param mixed $object     The object
     *
     * @return bool
     *
     * @throws \LogicException
     */
    protected function isGranted($attributes, $object = null)
    {
        if (!$this->container->has('security.authorization_checker')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        return $this->container->get('security.authorization_checker')->isGranted($attributes, $object);
    }

    protected function addFlash($type, $message)
    {
        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($request->getRequestFormat() === 'html' && !$request->isXmlHttpRequest()) {
            $this->container->get('session')->getFlashBag()->add($type, $message);
        }
    }

    /**
     * @param EntityInterface|string $entity
     *
     * @return string
     */
    protected function getClassBaseName ($entity)
    {
        if ($entity instanceof EntityInterface) {
            $entity = get_class($entity);
        }
        return preg_replace('`^.*\\\\([^\\\\]+)$`', '$1', $entity);
    }

    /**
     * @param string          $type
     * @param EntityInterface $data
     * @param array           $options
     *
     * @return Form
     * @throws \Exception
     */
    public function createForm($type = null, $data = null, array $options = array())
    {
        if ($type === null && is_object($data)) {
            $reflectionClass = new \ReflectionClass(get_class($data));
            $annotation = $this->getAnnotation($reflectionClass, CRUD\Form::class);
            if ($annotation === null) {
                return null;
            }
            $type = $annotation->class;
        }
        return $this->container->get('form.factory')->create($type, $data, $options);
    }

    protected function getAnnotation (\ReflectionClass $reflectionClass, $annotationName)
    {
        $reader = new AnnotationReader();
        $annotation = $reader->getClassAnnotation($reflectionClass, $annotationName);
        if ($annotation !== null) {
            return $annotation;
        } else {
            return $this->getAnnotation($reflectionClass->getParentClass(), $annotationName);
        }
    }

    /**
     * @param string               $entityClassName
     * @param EntityInterface|null $data
     * @param array                $options
     *
     * @return null|Form
     * @throws \Exception
     */
    public function createFormFilter($entityClassName, $data = null, array $options = array())
    {
        $reflectionClass = new \ReflectionClass($entityClassName);
        $reader = new AnnotationReader();
        /** @var CRUD\FormFilter $annotation */
        $annotation = $reader->getClassAnnotation($reflectionClass, CRUD\FormFilter::class);
        if ($annotation === null) {
            return null;
        } elseif (!class_exists($annotation->class)) {
            throw new \Exception("The Entity $entityClassName declare a FormFilter ($annotation->class) but does not exist");
        }

        return $this->createForm($annotation->class, $data, $options);
    }

    /**
     * @param null|string|EntityInterface $class
     *
     * @return AbstractSearchRepository
     */
    protected function getElasticaSearchRepository ($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if ($this->container->has('fos_elastica.manager')) {
            /** @var RepositoryManager $repositoryManager */
            $repositoryManager = $this->container->get('fos_elastica.manager');
            return $repositoryManager->getRepository($class);
        } else {
            return null;
        }
    }

    /**
     * @param string $className
     * @param Form   $form
     *
     * @return array
     */
    protected function filterByForm ($className, Form $form)
    {
        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository($className);
        $filterMethod = $this->getRepositoryFilterMethod($className);
        if ($filterMethod instanceof \ReflectionMethod) {
            $result = $filterMethod->invoke($repository, $form);
            return $result;
        }
        return $this->defaultFilterQuery($className, $form);
    }

    /**
     * @param $className
     *
     * @return \ReflectionMethod|null
     * @throws \Exception
     */
    private function getRepositoryFilterMethod ($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }

        $reflectionClass = new \ReflectionClass($className);
        $reader = new AnnotationReader();
        /** @var CRUD\SearchRepository $annotation */
        $annotation = $reader->getClassAnnotation($reflectionClass, CRUD\SearchRepository::class);
        $repository = $this->getDoctrine()->getRepository($className);
        if ($repository instanceof EntityRepository) {
            $reflectionRepository = new \ReflectionClass(get_class($repository));
            if ($annotation !== null && $reflectionRepository->hasMethod($annotation->method)) {
                $method = $reflectionRepository->getMethod($annotation->method);
                if ($method->getNumberOfRequiredParameters() == 1 && $method->getParameters()[0]->getClass()->getName() == Form::class) {
                    return $method;
                } else {
                    throw new \Exception($annotation->method.' must have parameter Form');
                }
            } elseif ($annotation === null) {
                return null;
            } else {
                throw new \Exception(get_class($repository).' must have method '.$annotation->method);
            }
        }

        return null;
    }

    /**
     * @param string $className
     * @param Form   $form
     *
     * @return array
     */
    private function defaultFilterQuery ($className, Form $form)
    {
        /** @var EntityRepository $repository */
        $repository = $this->getDoctrine()->getRepository($className);

        $alias = strtolower($this->getClassBaseName($form->getConfig()->getDataClass()));
        $qb = $repository->createQueryBuilder($alias);
        $this->addWhereForSubForm($form, $qb);
        return $qb->getQuery();
    }

    private function addWhereForSubForm (FormInterface $form, QueryBuilder $qb)
    {
        $alias = strtolower($this->getClassBaseName($form->getConfig()->getDataClass()));
        /**
         * @var string $key
         * @var FormInterface $subForm
         */
        foreach ($form->all() as $key => $subForm) {
            $value = $subForm->getData();

            if ($value !== null) {
                if ($value instanceof \Closure) {
                    $value($qb, $alias);
                } elseif ($subForm->getConfig()->getMapped()) {
                    if ($subForm->getConfig()->getType()->getBlockPrefix() == "text") {
                        $qb->andWhere("lower($alias.$key) LIKE lower(:$key)")->setParameter($key, '%'.$value.'%');
                    } else {
                        if (get_class($subForm->getConfig()->getType()->getInnerType()) == EntityType::class) {
                            $qb->join($alias.'.'.$key, $key);
                            $qb->andWhere("$key.id = :{$key}_id")->setParameter($key.'_id', $value->getId());
                        } elseif (get_class($subForm->getConfig()->getType()->getInnerType()) == FormType::class) {
                            $aliasChild = strtolower($this->getClassBaseName($subForm->getConfig()->getDataClass()));
                            $qb->join($alias.'.'.$key, $aliasChild);
                            $this->addWhereForSubForm($subForm, $qb);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $name
     * @param bool   $lower
     *
     * @return string
     */
    protected function plural ($name, $lower = false)
    {
        $count = preg_match_all('`[A-Z]+[a-z]+`', $name, $matches);
        if ($count > 1) {
            list($first, $rest) = $matches[0];
            $pl = substr($first, -1) == 'y' ? substr($first, 0, -1).'ies'.$rest : $first.'s'.$rest;
        } else {
            $pl = substr($name, -1) == 'y' ? substr($name, 0, -1).'ies' : $name.'s';
        }
        return $lower ? $this->upperToLowerUnderscore($pl) : $pl;
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function upperToLowerUnderscore ($name)
    {
        return strtolower(preg_replace('/\B([A-Z])/', '_$1', $name));
    }

    /**
     * @param string $type
     * @param EntityInterface $entity
     *
     * @return null|Form
     */
    private function createActionForm ($type, EntityInterface $entity)
    {
        $classBaseName = lcfirst($this->getClassBaseName($entity));

        $method = $type == DeleteType::class ? 'delete' : 'restore';
        $route = $this->createRoute($entity, $method);
        /** @var Router $router */
        $router = $this->container->get('router');
        if ($router->getRouteCollection()->get($route) !== null) {
            /** @var RequestStack $requestStack */
            $requestStack = $this->container->get('request_stack');
            $request = $requestStack->getCurrentRequest();
            if ($request) {
                $args = ['redirect' => $request->getUri()];
            } else {
                $args = [];
            }

            /** @var Router $router */
            $router = $this->container->get('router');
            return $this->createForm($type, null, [
                'action' => $router->generate($route, array_merge([$classBaseName => $entity->getId()], $args))
            ]);
        } else {
            return $this->createForm($type);
        }
    }

    /**
     * @param EntityInterface $entity
     *
     * @return null|Form
     */
    public function createDeleteForm (EntityInterface $entity)
    {
        return $this->createActionForm(DeleteType::class, $entity);
    }

    /**
     * @param EntityInterface $entity
     *
     * @return Form
     */
    public function createRestoreForm (EntityInterface $entity)
    {
        return $this->createActionForm(RestoreType::class, $entity);
    }

    /**
     * @param EntityInterface $entity
     * @param string          $method
     * @param bool            $plural
     *
     * @return string
     */
    protected function createRoute (EntityInterface $entity, $method = 'get', $plural = false)
    {
        $classBaseName = $this->getClassBaseName($entity);
        if ($plural) {
            $route = strtolower($method).'_'.$this->plural($classBaseName, true);
            /** @var Router $router */
            $router = $this->container->get('router');
            if ($router->getRouteCollection()->get($route)) {
                return $route;
            } elseif (strtolower($method) == 'get') {
                $route = strtolower('cget_'.$classBaseName);
                return $route;
            } else {
                return $route;
            }
        } else {
            return strtolower($method.'_'.$classBaseName);
        }
    }

    /**
     * Creates a view.
     *
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return View
     */
    protected function view($data = null, $statusCode = null, array $headers = [])
    {
        if ($this->view === null) {
            $this->view = View::create($data, $statusCode, $headers);
        }
        return $this->view;
    }

    /**
     * @return void
     */
    public function init ()
    {}

    public function addViewData ($key, $value)
    {
        $data = $this->view()->getData();
        $data[$key] = $value;
        $this->view()->setData($data);
    }

    /**
     * @param $key
     *
     * @return mixed|bool
     */
    public function getViewData ($key)
    {
        $data = $this->view()->getData();
        return isset($data[$key]) ? $data[$key] : false;
    }
}