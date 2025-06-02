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

use Pdp\Storage\TopLevelDomainListClient;
use Pdp\TopLevelDomainList;
use Pdp\TopLevelDomains;
use Pdp\UnableToLoadResource;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class TopLevelDomainListSymfonyClient implements TopLevelDomainListClient
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    public function get(string $uri): TopLevelDomainList
    {
        try {
            $response = $this->client->request('GET', $uri);
        } catch (TransportException $exception) {
            throw UnableToLoadResource::dueToUnavailableService($uri, $exception);
        }

        if (400 <= $response->getStatusCode()) {
            throw UnableToLoadResource::dueToUnexpectedStatusCode($uri, $response->getStatusCode());
        }

        return TopLevelDomains::fromString($response->getContent());
    }
}
