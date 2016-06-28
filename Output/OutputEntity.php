<?php

namespace FLE\Bundle\CrudBundle\Output;

use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormView;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @Serializer\ExclusionPolicy("none")
 */
class OutputEntity
{
    /**
     * @var EntityInterface
     * @Serializer\Expose
     * @Serializer\Groups({"default", "output"})
     * @Serializer\Type("FLE\Bundle\CrudBundle\Entity\EntityInterface")
     * @Serializer\XmlElement(cdata=false)
     */
    protected $entity;

    /**
     * @var ConstraintViolationListInterface
     * @Serializer\Expose
     * @Serializer\Groups({"default", "output"})
     */
    protected $errors;

    /**
     * @var Form
     */
    protected $form;

    public function __construct (EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity ()
    {
        return $this->entity;
    }

    /**
     * @param EntityInterface $entity
     */
    public function setEntity (EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getErrors ()
    {
        if ($this->form instanceof Form && $this->errors === null) {
            $this->setFormErrors($this->form->getErrors());
        }
        return $this->errors;
    }

    /**
     * @param ConstraintViolationListInterface $errors
     */
    public function setErrors (ConstraintViolationListInterface $errors)
    {
        $this->errors = $errors;
    }

    public function addErrorMessage ($message)
    {
        if ($this->getErrors() === null) {
            $this->setErrors(new ConstraintViolationList());
        }
        $this->getErrors()->add(new ConstraintViolation($message, $message, [], null, null, null));
    }

    public function addError (ConstraintViolationInterface $error)
    {
        if ($this->getErrors() === null) {
            $this->setErrors(new ConstraintViolationList());
        }
        $this->getErrors()->add($error);
    }

    public function setFormViewErrors (FormView $formView)
    {
        if (isset($formView->vars['errors'])) {
            $errors = $formView->vars['errors'];
            if ($errors instanceof FormErrorIterator) {
                if ($errors->count() > 0) {
                    $this->setFormErrors($errors);
                }
            }
        }
        if ($formView->getIterator()->count() > 0) {
            foreach ($formView->getIterator() as $childrenFormView) {
                $this->setFormViewErrors($childrenFormView);
            }
        }
    }

    public function setFormErrors (FormErrorIterator $errors)
    {
        if ($this->getErrors() === null) {
            $this->setErrors(new ConstraintViolationList());
        }

        foreach ($errors as $error) {
            /** @var FormError $error */
            $this->getErrors()->add(new ConstraintViolation(
                $error->getMessage(),
                $error->getMessageTemplate(),
                $error->getMessageParameters(),
                $error->getOrigin(),
                (string)$error->getOrigin()->getPropertyPath(),
                $error->getOrigin()->getData()
            ));
        }
    }

    public function setForm ($form)
    {
        $this->form = $form;
    }
}