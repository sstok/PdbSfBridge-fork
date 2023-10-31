<?php

declare(strict_types=1);

/*
 * This file is part of the PHP Domain Parser Symfony-bridge package.
 *
 * (c) Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\PdbSfBridge\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\HttpClient\HttpClient;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('rollerworks_pdb');

        /** @var ArrayNodeDefinition $root */
        $root = $treeBuilder->getRootNode();
        $root
            ->children()
                ->enumNode('manager')
                    ->defaultValue(class_exists(HttpClient::class) ? 'http' : 'static')
                    ->values(['http', 'static', 'test'])
                ->end()
                ->scalarNode('cache_pool')->defaultValue('rollerworks.cache.public_prefix_db')->cannotBeEmpty()->end()
                ->scalarNode('http_client')->defaultValue('http_client')->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}
