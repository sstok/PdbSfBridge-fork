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

use Rollerworks\Component\PdbSfBridge\PdpManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

final class RollerworksPdbExtension extends ConfigurableExtension
{
    /**
     * @param array<string, mixed> $mergedConfig
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__) . '/Resources/config'));
        $loader->load('services.php');

        $container->setAlias('rollerworks_pdb.cache_adapter', $mergedConfig['cache_pool']);
        $container->setAlias(PdpManager::class, 'rollerworks_pdb.pdb_manager');

        if ($mergedConfig['manager'] === 'test') {
            $container->setAlias('rollerworks_pdb.pdb_manager', 'rollerworks_pdb.pdb_manager.mock');
        } elseif ($mergedConfig['manager'] === 'static') {
            $container->setAlias('rollerworks_pdb.pdb_manager', 'rollerworks_pdb.pdb_manager.static');
        } else {
            $container->setAlias('rollerworks_pdb.pdb_manager', 'rollerworks_pdb.pdb_manager.http');
        }
    }
}
