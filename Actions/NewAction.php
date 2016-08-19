<?php

namespace FLE\Bundle\CrudBundle\Actions;

use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;

class NewAction extends GetAction
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * @return Form
     */
    public function getForm ()
    {
        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function setForm ($form)
    {
        $this->form = $form;
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
        $view = $this->view();

        $view->setData([
            $className => $this->getEntity(),
            'form'     => $this->getForm()->createView(),
        ]);
        return $view;
    }
}