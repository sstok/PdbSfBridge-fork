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

use Symfony\Component\Cache\Adapter\PhpFilesAdapter;
use Symfony\Component\Cache\Psr16Cache;

/**
 * The PdpMockProvider uses the pre-bundled data which is not up-to-date
 * and must only be used for testing purposes.
 *
 * Note: The pre-bundled data is less than 1MB in size.
 */
final class PdpMockProvider
{
    private static ?StaticPdpManager $pdpManager = null;

    public static function getPdpManager(): StaticPdpManager
    {
        if (self::$pdpManager !== null) {
            return self::$pdpManager;
        }

        return self::$pdpManager = new StaticPdpManager(
            __DIR__ . '/../Resources/list/public_suffix_list.dat',
            __DIR__ . '/../Resources/list/tlds-alpha-by-domain.txt',
            new Psr16Cache(new PhpFilesAdapter('rollerworks-pdb', 0, null, true))
        );
    }
}
