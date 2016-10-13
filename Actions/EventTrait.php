<?php

namespace FLE\Bundle\CrudBundle\Actions;

use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use FLE\Bundle\CrudBundle\Events\Events;
use FOS\RestBundle\View\View;
use Symfony\Component\Form\Form;

trait EventTrait
{
    /**
     * @var callable
     */
    protected $preFlushEvent;

    /**
     * @var callable
     */
    protected $postSubmitEvent;

    protected function preFlush (EntityInterface $entity, Form $form, View $view)
    {
        if (is_callable($this->preFlushEvent)) {
            return call_user_func($this->preFlushEvent, $entity, $form, $view);
        } elseif ($this->preFlushEvent !== null) {
            throw new \Exception('preFlushEvent must be a callable function');
        } else {
            return true;
        }
    }

    protected function postSubmit (EntityInterface $entity, Form $form, View $view)
    {
        if (is_callable($this->postSubmitEvent)) {
            return call_user_func($this->postSubmitEvent, $entity, $form, $view);
        } elseif ($this->postSubmitEvent !== null) {
            throw new \Exception('postSubmitEvent must be a callable function');
        } else {
            return true;
        }
    }

    /**
     * @param $event
     * @param callable $callable
     *
     * @throws \Exception
     */
    public function addEvent ($event, callable $callable)
    {
        if ($event == Events::PRE_FLUSH) {
            $this->preFlushEvent = $callable;
        } elseif ($event == Events::POST_SUBMIT) {
            $this->postSubmitEvent = $callable;
        } else {
            throw new \Exception('event must be a FLE\Bundle\CrudBundle\Event\Events constant');
        }
    }
}