<?php
namespace FLE\Bundle\CrudBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('fle_crud');

        $rootNode
            ->children()
                ->scalarNode('user_class')->isRequired()->cannotBeEmpty()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}