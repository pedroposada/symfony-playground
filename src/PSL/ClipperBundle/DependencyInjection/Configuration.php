<?php
// PSL/ClipperBundle/DependencyInjection/Configuration.php

namespace PSL\ClipperBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('psl_clipper');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
                $rootNode
            ->children()
                ->arrayNode('google_spreadsheets')
                    ->children()
                        ->scalarNode('client_id')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('service_account_name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('p12_file_name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('spreadsheet_name')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('worksheet_name')->isRequired()->cannotBeEmpty()->end()
                    ->end()
                ->end()
            ->end();

        // ->scalarNode('sheet_id')->isRequired()->cannotBeEmpty()->end()

        return $treeBuilder;
    }
}
