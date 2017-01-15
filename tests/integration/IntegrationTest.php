<?php

use Amara\TwigCacheExtension\SymfonyTaggedCacheStrategy;
use Asm89\Twig\CacheExtension\Extension;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * IntegrationTest
 */
class IntegrationTest extends PHPUnit_Framework_TestCase {
	public function testDisplaysCachedContentWhenPresentInCache()
	{
		$createCacheItemFunction = $this->createCacheCreateFunction();

		$loader = new Twig_Loader_Array(array(
			'index' => "
				{%- cache 'example-item' {lifetime:900, tags: ['tag1', 'tag2']} -%}
					The content to cache
				{%- endcache -%}
			",
		));
		$twig = new Twig_Environment($loader);

		$cachedContent = 'Already cached content';

		$cacheItem = $createCacheItemFunction('example-item', $cachedContent, true);

		$tagAwareAdapter = $this->prophesize(TagAwareAdapterInterface::class);
		$tagAwareAdapter->getItem('__SF2__example-item')->willReturn($cacheItem);

		$cacheStrategy  = new SymfonyTaggedCacheStrategy($tagAwareAdapter->reveal());
		$cacheExtension = new Extension($cacheStrategy);

		$twig->addExtension($cacheExtension);

		$actualContent = $twig->render('index');

		$this->assertEquals($actualContent, $cachedContent);
	}

	public function testDisplaysAndSavesNotCachedContent()
	{
		$createCacheItem = $this->createCacheCreateFunction();

		$loader = new Twig_Loader_Array(array(
			'index' => "
				{%- cache 'example-item' {lifetime:900, tags: ['tag1', 'tag2']} -%}
					The content to cache
				{%- endcache -%}
			",
		));
		$twig = new Twig_Environment($loader);

		$cacheItemNoHit = $createCacheItem('example-item', 'Nothing here', false);

		$tagAwareAdapter = $this->prophesize(TagAwareAdapterInterface::class);
		$tagAwareAdapter->getItem('__SF2__example-item')->willReturn($cacheItemNoHit);
		$tagAwareAdapter->save(\Prophecy\Argument::that(function ($cacheItemToSave) {
			if (!$cacheItemToSave instanceof CacheItem) {
				return false;
			}

			// todo: assert expiresAt via reflection
			// todo: assert tags via reflection

			PHPUnit_Framework_Assert::assertEquals('example-item', $cacheItemToSave->getKey());
			PHPUnit_Framework_Assert::assertEquals('The content to cache', $cacheItemToSave->get());

			return true;
		}))->shouldBeCalled();

		$cacheStrategy  = new SymfonyTaggedCacheStrategy($tagAwareAdapter->reveal());
		$cacheExtension = new Extension($cacheStrategy);

		$twig->addExtension($cacheExtension);

		$actualContent = $twig->render('index');

		$this->assertEquals($actualContent, 'The content to cache');
	}

	/**
	 * @return Closure
	 */
	private function createCacheCreateFunction() {
		// This is copied from the sf2 cache library
		return \Closure::bind(
			function ($key, $value, $isHit) {
				$item = new CacheItem();
				$item->key = $key;
				$item->value = $value;
				$item->isHit = $isHit;

				return $item;
			},
			null,
			CacheItem::class
		);
	}
}
