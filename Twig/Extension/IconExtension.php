<?php

namespace FLE\Bundle\CrudBundle\Twig\Extension;

use Mopa\Bundle\BootstrapBundle\Twig\IconExtension as MopaIconExtension;
use Symfony\Component\HttpFoundation\Response;

class IconExtension extends MopaIconExtension
{
    /**
     * Renders the icon.
     *
     * @param \Twig_Environment $env
     * @param string            $icon
     * @param boolean           $inverted
     *
     * @return Response
     */
    public function renderIcon(\Twig_Environment $env, $icon, $inverted = false)
    {
        if (substr($icon, 0, 9) == 'flaticon-') {
            $iconSet = 'flaticon';
            $icon = substr($icon, 9);
        } else {
            $iconSet = $this->iconSet;
        }
        $template = $this->getIconTemplate($env);
        $context = array(
            'icon' => $icon,
            'inverted' => $inverted,
        );

        return $template->renderBlock($iconSet, $context);
    }

    /**
     * @param \Twig_Environment $env
     *
     * @return \Twig_Template
     */
    protected function getIconTemplate(\Twig_Environment $env)
    {
        if ($this->iconTemplate === null) {
            $this->iconTemplate = $env->loadTemplate('FLECrudBundle::icons.html.twig');
        }

        return $this->iconTemplate;
    }
}
