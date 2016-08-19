<?php

namespace FLE\Bundle\CrudBundle\Actions;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\RequestStack;

class DeleteAction extends GetAction
{
    use RedirectActionTrait;

    /**
     * @return View
     * @throws \Exception
     */
    public function build ()
    {
        $view = $this->view();

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        $form = $this->createDeleteForm($this->getEntity(), $request);
        $form->handleRequest($request);

        if (($form->isSubmitted() && $form->isValid()) || $this->isGranted('ROLE_API')) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($this->getEntity());
            $em->flush();
            if ($this->container->has('fos_elastica.index_manager')) {
                $this->container->get('fos_elastica.index_manager')->getIndex('app')->refresh();
            }

            $classBaseName = strtolower($this->getClassBaseName($this->getEntity()));
            $this->addFlash('success', $classBaseName.'.flash.delete.success');

            $this->autoSetRedirectRoute();

            $view->setStatusCode(204);
        } else {
            $view->setStatusCode(400);
        }

        return $view;
    }
}