<?php

namespace FLE\Bundle\CrudBundle\Actions;

use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RequestStack;

class EditAction extends NewAction
{
    /**
     * @var Form
     */
    protected $deleteForm;

    /**
     * @var Form
     */
    protected $restoreForm;

    /**
     * @return Form
     */
    public function getDeleteForm ()
    {
        return $this->deleteForm;
    }

    /**
     * @param Form $deleteForm
     */
    public function setDeleteForm ($deleteForm)
    {
        $this->deleteForm = $deleteForm;
    }

    /**
     * @return Form
     */
    public function getRestoreForm ()
    {
        return $this->restoreForm;
    }

    /**
     * @param Form $restoreForm
     */
    public function setRestoreForm ($restoreForm)
    {
        $this->restoreForm = $restoreForm;
    }

    /**
     * @return View
     * @throws \Exception
     */
    public function build ()
    {
        $className = strtolower($this->getClassBaseName($this->getEntity()));
        if ($this->getForm() === null) {
            $this->setForm($this->createForm(null, $this->getEntity()));
        }
        if ($this->getForm() === null) {
            throw new \Exception('The form for the entity must be defined in controller or via Annotation');
        }

        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        $view = $this->view();
        $view->setData([
            $className    => $this->getEntity(),
            'form'        => $this->getForm()->createView(),
            'delete_form' => $this->getDeleteForm() ?: $this->createDeleteForm($this->getEntity(), $request)->createView(),
            'restore_form' => $this->getRestoreForm() ?: $this->createRestoreForm($this->getEntity(), $request)->createView()
        ]);
        return $view;
    }
}