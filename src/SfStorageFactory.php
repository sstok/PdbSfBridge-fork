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

namespace Rollerworks\Component\PdbSfBridge;

use Pdp\PublicSuffixList;
use Pdp\ResourceUri;
use Pdp\Storage\PublicSuffixListPsr16Cache;
use Pdp\Storage\PublicSuffixListStorage;
use Pdp\Storage\PublicSuffixListStorageFactory;
use Pdp\Storage\RulesStorage;
use Pdp\Storage\TopLevelDomainListPsr16Cache;
use Pdp\Storage\TopLevelDomainListStorage;
use Pdp\Storage\TopLevelDomainListStorageFactory;
use Pdp\Storage\TopLevelDomainsStorage;
use Pdp\TopLevelDomainList;
use Psr\SimpleCache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The SfStorageFactory creates the suffix-list and top-level domain list storages
 * using a Symfony HTTPClient and Psr16 cache.
 *
 * For general usage use the {@see HttpUpdatedPdpManager} or {@see StaticPdpManager}
 * to gain access to the {@see PublicSuffixList} and {@see TopLevelDomainList}.
 */
final readonly class SfStorageFactory implements ResourceUri, PublicSuffixListStorageFactory, TopLevelDomainListStorageFactory
{
    public function __construct(
        private CacheInterface $cache,
        private HttpClientInterface $client,
    ) {
    }

    /** @param mixed $cacheTtl The cache TTL */
    public function createPublicSuffixListStorage(string $cachePrefix = '', $cacheTtl = null): PublicSuffixListStorage
    {
        return new RulesStorage(
            new PublicSuffixListPsr16Cache($this->cache, $cachePrefix, $cacheTtl),
            new PublicSuffixListSymfonyClient($this->client)
        );
    }

    /** @param mixed $cacheTtl The cache TTL */
    public function createTopLevelDomainListStorage(string $cachePrefix = '', $cacheTtl = null): TopLevelDomainListStorage
    {
        return new TopLevelDomainsStorage(
            new TopLevelDomainListPsr16Cache($this->cache, $cachePrefix, $cacheTtl),
            new TopLevelDomainListSymfonyClient($this->client)
        );
    }
}
