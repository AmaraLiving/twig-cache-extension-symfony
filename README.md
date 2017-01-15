Twig cache extension / Symfony cache bridge
===========================================

Tiny library to bridge the tagged adapter from the Symfony2 cache component with 
the twig cache extension.

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
{% cache 'example-item' {lifetime:900, tags: ['tag1', 'tag2']} %}
    The content to cache
{% endcache %}
```
  