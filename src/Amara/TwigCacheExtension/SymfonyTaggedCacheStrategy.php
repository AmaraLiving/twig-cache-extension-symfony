<?php

/*
 * This file is part of the twig-cache-extension-symfony package.
 *
 * (c) Amara Living Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Amara\TwigCacheExtension;

use Asm89\Twig\CacheExtension\CacheStrategyInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * SymfonyTaggedCacheStrategy
 */
class SymfonyTaggedCacheStrategy implements CacheStrategyInterface
{
    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var string
     */
    private $keyTemplate = '__SF2__%s';

    /**
     * @param AdapterInterface $adapter
     */
    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @param AdapterInterface $adapter
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return string
     */
    public function getKeyTemplate()
    {
        return $this->keyTemplate;
    }

    /**
     * @param string $keyTemplate
     */
    public function setKeyTemplate($keyTemplate)
    {
        $this->keyTemplate = $keyTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function fetchBlock($key)
    {
        if (!$key instanceof KeyModel) {
            throw new InvalidValueException('Key should have been a KeyModel');
        }

        $item = $this->adapter->getItem($key->key);

        if ($item->isHit()) {
            return $item->get();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function generateKey($annotation, $value)
    {
        if (!is_array($value)) {
            throw new InvalidValueException('Cache value must be an array');
        }

        $lifetime = isset($value['lifetime']) ? $value['lifetime'] : 0;
        $tags = isset($value['tags']) ? $value['tags'] : [];
        $key = sprintf($this->keyTemplate, $annotation);

        return new KeyModel($key, $lifetime, $tags);
    }

    /**
     * {@inheritdoc}
     */
    public function saveBlock($key, $block)
    {
        if (!$key instanceof KeyModel) {
            throw new InvalidValueException('Key should have been a KeyModel');
        }

        $item = $this->adapter->getItem($key->key);

        $item->set($block);
        $item->expiresAfter($key->lifetime);

        if ($item instanceof CacheItem) {
            $item->tag($key->tags);
        }

        $this->adapter->save($item);
    }
}
