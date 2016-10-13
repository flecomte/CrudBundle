<?php

namespace FLE\Bundle\CrudBundle\Actions;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\RequestStack;

class PutAction extends PostAction
{

    /**
     * @return View
     * @throws \Exception
     */
    public function build ()
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        $view = parent::build();
        $this->addViewData('delete_form', $this->getDeleteForm() ?: $this->createDeleteForm($this->getEntity(), $request)->createView());
        $this->addViewData('restore_form', $this->getRestoreForm() ?: $this->createRestoreForm($this->getEntity(), $request)->createView());
        return $view;
    }
}