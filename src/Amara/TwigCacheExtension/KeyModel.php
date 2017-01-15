<?php
/**
 *
 */

namespace Amara\TwigCacheExtension;

/**
 * KeyModel
 */
class KeyModel {
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
	 * @param \string[] $tags
	 */
	public function __construct($key, $lifetime, array $tags) {
		$this->key = $key;
		$this->lifetime = $lifetime;
		$this->tags = $tags;
	}
}
