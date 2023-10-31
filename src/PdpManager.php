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
use Pdp\TopLevelDomainList;

interface PdpManager
{
    public function getPublicSuffixList(): PublicSuffixList;

    public function getTopLevelDomainList(): TopLevelDomainList;

    public function updateCaches(): void;
}
