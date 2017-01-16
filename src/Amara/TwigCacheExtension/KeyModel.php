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

/**
 * KeyModel
 */
class KeyModel
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var int
     */
    public $lifetime;

    /**
     * @var string[]
     */
    public $tags;

    /**
     * @param string $key
     * @param int $lifetime
     * @param string[] $tags
     */
    public function __construct($key, $lifetime = null, array $tags = [])
    {
        $this->key = $key;
        $this->lifetime = $lifetime;
        $this->tags = $tags;
    }
}
