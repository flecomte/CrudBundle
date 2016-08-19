<?php

namespace FLE\Bundle\CrudBundle\Actions;

use FOS\RestBundle\Controller\Annotations\View;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface ActionInterface
{
    /**
     * @return void
     */
    public function init ();

    /**
     * @return View
     */
    public function build ();

    /**
     * @param ContainerInterface $container
     */
    public function setContainer (ContainerInterface $container);
}