<?php

namespace FLE\Bundle\CrudBundle\Actions;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Query\FilterCollection;
use FOS\RestBundle\View\View;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use ReflectionClass;
use Symfony\Component\HttpFoundation\RequestStack;

class RestoreAction extends GetAction
{
    use RedirectActionTrait;

    /**
     * @return View
     * @throws \Exception
     */
    public function build ()
    {
        if (!class_exists(SoftDeleteable::class)) {
            throw new \Exception('need softdeleteable extension for restore entity');
        }

        $view = $this->view();
        $em = $this->getDoctrine()->getManager();

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        /** @var FilterCollection $filters */
        $filters = $em->getFilters();
        if (in_array('softdeleteable', array_keys($filters->getEnabledFilters()))) {
            $filters->disable('softdeleteable');
        }

        $form = $this->createRestoreForm($this->getEntity(), $request);
        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid()) || $this->isGranted('ROLE_API')) {
            $reader = new AnnotationReader();
            $reflectionClass = new ReflectionClass(get_class($this->getEntity()));

            $annotation = $reader->getClassAnnotation($reflectionClass, SoftDeleteable::class);
            $methodName = 'set'.ucfirst($annotation->fieldName);
            if ($annotation !== null && $reflectionClass->hasMethod($methodName)) {
                $reflectionClass->getMethod($methodName)->invoke($this->getEntity(), null);
            }
            $em->persist($this->getEntity());
            $em->flush();

            if ($this->container->has('fos_elastica.index_manager')) {
                $this->container->get('fos_elastica.index_manager')->getIndex('app')->refresh();
            }

            $classBaseName = strtolower($this->getClassBaseName($this->getEntity()));
            $this->addFlash('success', $classBaseName.'.flash.restore.success');

            $this->autoSetRedirectRoute();

            $view->setStatusCode(204);
        } else {
            $view->setStatusCode(400);
        }

        return $view;
    }
}