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

namespace Rollerworks\Component\PdbSfBridge\Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RollerworksPdbBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
