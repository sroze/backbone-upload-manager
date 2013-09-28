<?php

namespace SRozeIO\UploadHandlerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    const PARAMETER_UPLOAD_ROOT_DIR = 'srozeio_upload_root_dir';

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('s_roze_io_upload_handler');

        $rootNode
            ->children()
            ->scalarNode('upload_root_dir')->isRequired()->end()
            ->end();

        return $treeBuilder;
    }
}
