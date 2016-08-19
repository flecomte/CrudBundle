<?php

namespace FLE\Bundle\CrudBundle\Actions;

use Symfony\Component\HttpFoundation\RequestStack;

trait RedirectActionTrait
{
    /**
     * @var string
     */
    protected $redirectRoute;

    /**
     * @return string
     */
    public function getRedirectRoute ()
    {
        return $this->redirectRoute;
    }

    /**
     * @param bool $route set null to disable
     *
     * @return null|void
     */
    protected function setRedirectRoute ($route)
    {
        if ($route === null) {
            $this->redirectRoute = null;
            $this->view()->setLocation(null);
            $this->view()->setRoute(null);
        } else {
            $this->redirectRoute = $route;
        }
    }

    /**
     * @return null|void
     */
    protected function autoSetRedirectRoute ()
    {
        /** @var RequestStack $requestStack */
        $requestStack = $this->container->get('request_stack');
        $request = $requestStack->getCurrentRequest();

        if ($this->getRedirectRoute() === null && $request->get('redirect') != null) {
            $this->view()->setLocation($request->get('redirect'));
        } elseif ($this->getRedirectRoute() !== false && $this->getRedirectRoute() !== null) {
            $this->view()->setRoute($this->getRedirectRoute());
        } else {
            $this->view()->setRoute($this->createRoute($this->getEntity(), 'get', true));
        }
    }
}