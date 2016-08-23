<?php

namespace FLE\Bundle\CrudBundle\Actions;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\RequestStack;

class PostAction extends EditAction
{
    use RedirectActionTrait;

    protected $buttonNames = [
        'put' => 'update',
        'post' => 'create'
    ];

    /**
     * @return View
     * @throws \Exception
     */
    public function build ()
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        $className = strtolower($this->getClassBaseName($this->getEntity()));

        if ($this->getForm() === null) {
            $this->setForm($this->createForm(null, $this->getEntity()));
        }
        if ($this->getForm() === null) {
            throw new \Exception('The form for the entity must be defined in controller or via Annotation');
        }

        $form = $this->getForm();
        $form->handleRequest($request);
        $formMethod = strtolower($form->getConfig()->getMethod());
        $view = $this->view();

        $buttonName = $this->buttonNames[$formMethod];
        if ($form->isSubmitted() && $form->isValid() && ($this->isGranted('ROLE_API') || ($form->has($buttonName) && $form->get($buttonName)->isClicked()))) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($this->getEntity());
            $em->flush();
            if ($this->container->has('fos_elastica.index_manager')) {
                $this->container->get('fos_elastica.index_manager')->getIndex('app')->refresh();
            }

            $this->addFlash('success', $className.'.flash.'.$formMethod.'.success');

            $this->autoSetRedirectRoute();
            $view->setStatusCode(200);
        } else {
            $view->setStatusCode(400);
        }

        $view->setData([
            $className => $this->getEntity(),
            'form'     => $form->createView(),
        ]);
        return $view;
    }
}