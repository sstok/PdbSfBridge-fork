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

namespace Rollerworks\Component\PdbSfBridge\Tests\Bundle;

use Nyholm\BundleTest\TestKernel;
use Rollerworks\Component\PdbSfBridge\Bundle\RollerworksPdbBundle;
use Rollerworks\Component\PdbSfBridge\HttpUpdatedPdpManager;
use Rollerworks\Component\PdbSfBridge\StaticPdpManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\KernelInterface;

/** @internal */
final class BundleInitializationTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /** @var TestKernel $kernel */
        $kernel = parent::createKernel($options);
        $kernel->addTestCompilerPass(new class() implements CompilerPassInterface {
            public function process(ContainerBuilder $container)
            {
                $container->findDefinition('rollerworks_pdb.pdb_manager')->setPublic(true);
            }
        });

        $kernel->addTestBundle(RollerworksPdbBundle::class);
        $kernel->addTestConfig(__DIR__ . '/config.yml');
        $kernel->handleOptions($options);

        // $kernel->setClearCacheAfterShutdown(false);

        return $kernel;
    }

    public function test_it_initializes(): void
    {
        $container = self::getContainer();

        $this->assertTrue($container->has('rollerworks_pdb.pdb_manager'));

        $service = $container->get('rollerworks_pdb.pdb_manager');

        if (class_exists(HttpClient::class)) {
            $this->assertInstanceOf(HttpUpdatedPdpManager::class, $service);
        } else {
            $this->assertInstanceOf(StaticPdpManager::class, $service);
        }
    }

    public function test_it_enables_mock_manager(): void
    {
        self::bootKernel(['config' => function (TestKernel $kernel): void {
            $kernel->addTestConfig(__DIR__ . '/test.yml');
        }]);

        $container = self::getContainer();

        $this->assertTrue($container->has('rollerworks_pdb.pdb_manager'));

        $service = $container->get('rollerworks_pdb.pdb_manager');
        $this->assertInstanceOf(StaticPdpManager::class, $service);
    }

    public function test_works_with_static_manager(): void
    {
        self::bootKernel(['config' => function (TestKernel $kernel): void {
            $kernel->addTestConfig(__DIR__ . '/static.yml');
        }]);

        $container = self::getContainer();

        $this->assertTrue($container->has('rollerworks_pdb.pdb_manager'));

        $service = $container->get('rollerworks_pdb.pdb_manager');
        $this->assertInstanceOf(StaticPdpManager::class, $service);
    }

    public function test_works_without_http_client(): void
    {
        if (class_exists(HttpClient::class)) {
            self::markTestSkipped('This test requires the HttpClient component is not installed.');
        }

        self::bootKernel(['config' => function (TestKernel $kernel): void {
            $kernel->addTestConfig(__DIR__ . '/without_http.yml');
        }]);

        $container = self::getContainer();

        $this->assertTrue($container->has('rollerworks_pdb.pdb_manager'));

        $service = $container->get('rollerworks_pdb.pdb_manager');
        $this->assertInstanceOf(StaticPdpManager::class, $service);
    }
}
