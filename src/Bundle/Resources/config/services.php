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

use Rollerworks\Component\PdbSfBridge\Console\UpdateListsCommand;
use Rollerworks\Component\PdbSfBridge\HttpUpdatedPdpManager;
use Rollerworks\Component\PdbSfBridge\PdpMockProvider;
use Rollerworks\Component\PdbSfBridge\StaticPdpManager;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('rollerworks_pdb.pdb_manager.http', HttpUpdatedPdpManager::class)
            ->factory([HttpUpdatedPdpManager::class, 'create'])
            ->args([
                inline_service(Psr16Cache::class)->arg(0, service('rollerworks_pdb.cache_adapter')),
                service('http_client'),
            ])

        ->set('rollerworks_pdb.pdb_manager.static', StaticPdpManager::class)
            ->args([
                __DIR__ . '/../../../../Resources/list/public_suffix_list.dat',
                __DIR__ . '/../../../../Resources/list/tlds-alpha-by-domain.txt',
                inline_service(Psr16Cache::class)->arg(0, service('rollerworks_pdb.cache_adapter')),
            ])

        ->set('rollerworks_pdb.pdb_manager.mock', PdpMockProvider::class)
            ->factory([PdpMockProvider::class, 'getPdpManager'])

        ->set('rollerworks_pdb.command.update_lists', UpdateListsCommand::class)
            ->arg(0, service('rollerworks_pdb.pdb_manager'))
            ->tag('console.command')
    ;
};
