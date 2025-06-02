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
use Pdp\Storage\PublicSuffixListStorage;
use Pdp\Storage\TopLevelDomainListStorage;
use Pdp\TopLevelDomainList;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * The UpdatedPdpManager uses the official public-suffix and top-level domain lists
 * with automatic updates (when the cache has expired).
 *
 * Unless internet access is not possible this is the recommended
 * manager to use. Otherwise use the {@see StaticPdpManager}
 *
 * Use the {@see SfStorageFactory} to populate the lists.
 */
final readonly class HttpUpdatedPdpManager implements PdpManager
{
    public function __construct(
        private PublicSuffixListStorage $rulesStorage,
        private TopLevelDomainListStorage $topLevelDomainsStorage,
    ) {
    }

    public function getPublicSuffixList(): PublicSuffixList
    {
        return $this->rulesStorage->get(ResourceUri::PUBLIC_SUFFIX_LIST_URI);
    }

    public function getTopLevelDomainList(): TopLevelDomainList
    {
        return $this->topLevelDomainsStorage->get(ResourceUri::TOP_LEVEL_DOMAIN_LIST_URI);
    }

    /** Creates a new instance of the PdpManager using the Factory. */
    public static function create(CacheInterface $cache, ?HttpClientInterface $client = null): self
    {
        $client ??= HttpClient::create();
        $factory = new SfStorageFactory($cache, $client);

        return new self($factory->createPublicSuffixListStorage(), $factory->createTopLevelDomainListStorage());
    }

    public function updateCaches(): void
    {
        $this->rulesStorage->delete(ResourceUri::PUBLIC_SUFFIX_LIST_URI);
        $this->topLevelDomainsStorage->delete(ResourceUri::TOP_LEVEL_DOMAIN_LIST_URI);

        $this->getPublicSuffixList();
        $this->getTopLevelDomainList();
    }
}
