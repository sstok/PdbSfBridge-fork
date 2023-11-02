PHP Domain Parser Symfony-bridge
================================

This package provides a Symfony specific bridge for the [PHP domain-parser][pdb]
by Jeremy Kendall and Ignace Nyamagana Butera.

Providing a Cache adapter, HTTP Client adapter, and optional FrameworkBundle
integration at `\Rollerworks\Component\PdbSfBridge\Bundle\RollerworksPdbSfBundle`.

## Installation

To install this package, add `rollerworks/pdb-symfony-bridge` to your composer.json:

```bash
$ php composer.phar require rollerworks/pdb-symfony-bridge
```

Now, [Composer][composer] will automatically download all required files,
and install them for you.

[Symfony Flex][flex] (with contrib) is assumed to enable the Bundle and add
required configuration. https://symfony.com/doc/current/bundles.html

Otherwise add the following configuration:

<details>

```yaml
rollerworks_pdb:
    cache_pool: 'rollerworks.cache.public_prefix_db'
    #manager: http # either: 'http' (default), 'static' (requires manual updates) or 'mock'

framework:
    cache:
        pools:
            # This name can be changed by setting `rollerworks_pdb.cache_pool` (**Don't reuse an existing cache pool!**)
            rollerworks.cache.public_prefix_db:
                adapter: cache.adapter.array # use a persistent adapter that can be easily invalidated like cache.adapter.memcached or cache.adapter.pdo
                default_lifetime: 604800 # one week, the cache should be automatically refreshed, unless manager=static is used
```

</details>

## Requirements

You need at least PHP 8.1, and internet access is recommended but not required.

The public-suffix and top-level domain needs to manually updated from time
to time. When internet access is available the PdbManager will automatically
download the list and store it in the cache.

If no internet access is available, the local cache needs to refreshed manually.
(Current not supported yet).

## Basic Usage

Use the `HttpUpdatedPdpManager` to get a PdbManager which automatically updates
the local cache using the HttpClient.

```php
<?php

use Rollerworks\Component\PdbSfBridge\HttpUpdatedPdpManager;
use Symfony\Component\Cache\Psr16Cache(;

// Any PSR16 Compatible adapter can be used, but it's recommended to use
// A caching adapter that allows easy invalidation (like Pdo or Memcache).
$cacheAdapter = ...;
$cache = new Psr16Cache($cacheAdapter);

// Optional, if not provided created as shown
// $httpClient = new \Symfony\Component\HttpClient\HttpClient::create();

$manager = HttpUpdatedPdpManager::create($cache, /*$httpClient*/);

// Not required. But recommended to warm-up the caches.
$manager->updateCaches();

// \Pdp\PublicSuffixList
$publicSuffixList = $manager->getPublicSuffixList();

// \Pdp\PublicSuffixList
$topLevelDomainList = $manager->getTopLevelDomainList();
```

When internet access is not possible use the `StaticPdpManager` instead.

```php
<?php

use Rollerworks\Component\PdbSfBridge\StaticPdpManager;

// Any PSR16 Compatible adapter can be used, but it's recommended to use
// A caching adapter that allows easy invalidation (like Pdo or Memcache).
$cacheAdapter = ...;
$cache = new Psr16Cache($cacheAdapter);

// Provide the lists as described, realpath (not contents are string)
$publicSuffixList = ...; // File provided from https://publicsuffix.org/list/public_suffix_list.dat
$topLevelDomainList ...; // File provided from https://data.iana.org/TLD/tlds-alpha-by-domain.txt

$manager = new StaticPdpManager($publicSuffixList, $topLevelDomainList, $cache);

// Not required. But recommended to warm-up the caches.
$manager->updateCaches();

// \Pdp\PublicSuffixList
$publicSuffixList = $manager->getPublicSuffixList();

// \Pdp\PublicSuffixList
$topLevelDomainList = $manager->getTopLevelDomainList();
```

For resolving domain names see the official documentation of [PHP domain-parser].

### Testing

For using the manager in tests use
`\Rollerworks\Component\PdbSfBridge\PdpMockProvider::getPdpManager()`.

### Bundle Usage

First add set a suitable cache adapter (_the Flex recipe uses `ArrayCache` to allow
booting-up the Kernel, but for production usage it's required to use a persistent
cache adapter, as otherwise whenever the Manager is initialized the rules need to
be downloaded, parsed and cached)_.

```yaml
# config/packages/rollerworks_pdb.yaml

rollerworks_pdb:
    cache_pool: 'rollerworks.cache.public_prefix_db'

framework:
    cache:
        pools:
            # This name can be changed by setting `rollerworks_pdb.cache_pool` (**Don't reuse an existing cache pool!**)
            rollerworks.cache.public_prefix_db:
                adapter: cache.adapter.memcached # or cache.adapter.pdo
                default_lifetime: 604800 # one week, the cache should be automatically refreshed
```

#### Offline usage

When the HttpClient component is installed _and_ enabled the `HttpUpdatedPdpManager`
is automatically used. When the HttpClient is not available the `StaticPdpManager`
is used instead.

_To force usage of the static adapter set configuration `rollerworks_pdb.manager` to `static`._

When the static adapter is enabled you must run `rollerworks-pdb:update` with
the lists provided as files names.

**And disable expiration of the cache, as otherwise the pre-bundled version
will be used instead.**

First download the lists from https://publicsuffix.org/list/public_suffix_list.dat
and https://data.iana.org/TLD/tlds-alpha-by-domain.txt respectively.

And load them into the cache.

```console
$ bin/console rollerworks-pdb:update public_suffix_list.dat tlds-alpha-by-domain.txt
```

The files path can be either absolute or relative to the current working directory.

**Cache expiration disable:** Symfony cache-warming is designed as such that
once the cache is warmed-up no future file writing is expected (for security reasons).

Which is why the cache should be easily updatable, with preferable no filesystem writing.

The `rollerworks-pdb:update` updates the cache without writing the provided files
to the application var/cache directory. So when the cache does expire, there would
be no files to read from, to prevent this the pre-bundled lists (used for the `MockManager`)
are used instead, but these are only updated from time to time, and thus outdated.

## Versioning

For transparency and insight into the release cycle, and for striving to
maintain backward compatibility, this package is maintained under the
Semantic Versioning guidelines as much as possible.

Releases will be numbered with the following format:

`<major>.<minor>.<patch>`

And constructed with the following guidelines:

* Breaking backward compatibility bumps the major (and resets the minor and patch)
* New additions without breaking backward compatibility bumps the minor (and resets the patch)
* Bug fixes and misc changes bumps the patch

For more information on SemVer, please visit <http://semver.org/>.

## License

This library is released under the [MIT license](LICENSE).

## Contributing

This is an open source project. If you'd like to contribute,
please read the [Contributing Guidelines][contributing]. If you're submitting
a pull request, please follow the guidelines in the [Submitting a Patch][patches] section.

[pdb]: https://github.com/jeremykendall/php-domain-parser
[composer]: https://getcomposer.org/doc/00-intro.md
[flex]: https://symfony.com/doc/current/setup/flex.html
[contributing]: https://contributing.rollerscapes.net/
[patches]: https://contributing.rollerscapes.net/latest/patches.html
