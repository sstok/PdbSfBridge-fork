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
use Pdp\Rules;
use Pdp\Storage\PublicSuffixListPsr16Cache;
use Pdp\Storage\TopLevelDomainListPsr16Cache;
use Pdp\TopLevelDomainList;
use Pdp\TopLevelDomains;
use Psr\SimpleCache\CacheInterface;

class StaticPdpManager implements PdpManager
{
    private PublicSuffixListPsr16Cache $publicSuffixListCache;
    private TopLevelDomainListPsr16Cache $topLevelDomainListCache;

    public function __construct(
        private string $publicSuffixList,
        private string $topLevelDomainList,
        CacheInterface $cache
    ) {
        $this->publicSuffixListCache = new PublicSuffixListPsr16Cache($cache);
        $this->topLevelDomainListCache = new TopLevelDomainListPsr16Cache($cache);
    }

    public function getPublicSuffixList(): PublicSuffixList
    {
        $list = $this->publicSuffixListCache->fetch(ResourceUri::PUBLIC_SUFFIX_LIST_URI);

        if ($list) {
            return $list;
        }

        $list = Rules::fromPath($this->publicSuffixList);
        $this->publicSuffixListCache->remember(ResourceUri::PUBLIC_SUFFIX_LIST_URI, $list);

        return $list;
    }

    public function getTopLevelDomainList(): TopLevelDomainList
    {
        $list = $this->topLevelDomainListCache->fetch(ResourceUri::TOP_LEVEL_DOMAIN_LIST_URI);

        if ($list) {
            return $list;
        }

        $list = TopLevelDomains::fromPath($this->topLevelDomainList);
        $this->topLevelDomainListCache->remember(ResourceUri::TOP_LEVEL_DOMAIN_LIST_URI, $list);

        return $list;
    }

    public function updateCaches(): void
    {
        $this->publicSuffixListCache->remember(ResourceUri::PUBLIC_SUFFIX_LIST_URI, Rules::fromPath($this->publicSuffixList));
        $this->topLevelDomainListCache->remember(ResourceUri::TOP_LEVEL_DOMAIN_LIST_URI, TopLevelDomains::fromPath($this->topLevelDomainList));
    }

    public function populateCaches(string $publicSuffixList, $topLevelDomainList): void
    {
        $this->publicSuffixListCache->remember(ResourceUri::PUBLIC_SUFFIX_LIST_URI, Rules::fromPath($publicSuffixList));
        $this->topLevelDomainListCache->remember(ResourceUri::TOP_LEVEL_DOMAIN_LIST_URI, TopLevelDomains::fromPath($topLevelDomainList));
    }
}
