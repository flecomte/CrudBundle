<?php

namespace FLE\Bundle\CrudBundle\Listener;

use FOS\RestBundle\View\View;
use FLE\Bundle\CrudBundle\Output\OutputEntity;
use FLE\Bundle\CrudBundle\Output\OutputEntityArray;
use FLE\Bundle\CrudBundle\Entity\EntityInterface;
use Symfony\Component\Form\FormErrorIterator;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class KernelViewEvent
 * @package FLE\Bundle\CrudBundle\Listener
 *
 * @DI\Service()
 */
class KernelViewEvent
{
    /**
     * @DI\Observe(KernelEvents::VIEW, priority=1024)
     * @param GetResponseForControllerResultEvent $event
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $result = $event->getControllerResult();
        if ($request->getRequestFormat() != 'html') {
            if ($result instanceof View) {
                $view = $result;
                $result = $result->getData();
            } else {
                return; // Do Nothing
            }
            if (!is_array($result)) {
                $result = [$result];
            }
            foreach ($result as $item) {
                if ($item instanceof EntityInterface) {
                    $output = new OutputEntity($item);
                    break;
                } elseif (is_array($item) && count($item) > 0 && reset($item) instanceof EntityInterface) {
                    $output = new OutputEntityArray($item);
                    break;
                }
            }
            if (isset($output) && $output instanceof OutputEntity) {
                foreach ($result as $item) {
                    if ($item instanceof FormView && $item->vars['submitted'] === true) {
                        if (isset($item->vars['errors']) && $item->vars['errors'] instanceof FormErrorIterator) {
                            $output->setFormViewErrors($item);
                        }
                    }
                }
            } elseif (isset($output) && $output instanceof OutputEntityArray) {
            } else {
                return; // Do Nothing
            }
            $view->setData($output);
            $event->setControllerResult($view);
        }
    }
}