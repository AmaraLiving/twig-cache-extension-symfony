Twig cache extension / Symfony cache bridge
===========================================

Tiny library to allow use of tagging cache items from the [Symfony2 cache component](https://github.com/symfony/cache) with 
the [twig cache extension](https://github.com/asm89/twig-cache-extension).

It has a `SymfonyTaggedCacheStrategy` which accepts tags.

```php
<?php

use Asm89\Twig\CacheExtension\Extension as CacheExtension;
use Amara\TwigCacheExtension\SymfonyTaggedCacheStrategy;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;

$adapter = new TagAwareAdapter(new NullAdapter());

$cacheStrategy = new SymfonyTaggedCacheStrategy($adapter);

$cacheExtension = new CacheExtension($cacheStrategy);

$twig->addExtension($cacheExtension);
```

We can now use the twig view cache with tagged values:

```jinja
{% cache 'example-item' {lifetime: 900, tags: ['tag1', 'tag2']} %}
    The content to cache
{% endcache %}
```

# Why a separate library?

The Twig cache extension supports a PSR cache, but it does not support tagging. This library exists to avoid introducing a Symfony dependency into the Twig cache extension.

  
