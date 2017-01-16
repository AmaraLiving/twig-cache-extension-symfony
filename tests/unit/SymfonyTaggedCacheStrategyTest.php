<?php

use Amara\TwigCacheExtension\InvalidValueException;
use Amara\TwigCacheExtension\KeyModel;
use Amara\TwigCacheExtension\SymfonyTaggedCacheStrategy;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * SymfonyTaggedCacheStrategy
 */
class SymfonyTaggedCacheStrategyTest extends PHPUnit_Framework_TestCase
{
    /** @var AdapterInterface|ObjectProphecy */
    private $adapterMock;

    /** @var SymfonyTaggedCacheStrategy */
    private $symfonyTaggedCacheStrategy;

    public function setUp()
    {
        $this->adapterMock = $this->prophesize(AdapterInterface::class);
        $this->symfonyTaggedCacheStrategy = new SymfonyTaggedCacheStrategy($this->adapterMock->reveal());
    }

    public function testAdapterGettersAndSetters()
    {
        $adapter2 = $this->prophesize(AdapterInterface::class);

        $this->assertEquals($this->adapterMock->reveal(), $this->symfonyTaggedCacheStrategy->getAdapter());

        $this->symfonyTaggedCacheStrategy->setAdapter($adapter2->reveal());

        $this->assertEquals($adapter2->reveal(), $this->symfonyTaggedCacheStrategy->getAdapter());
    }

    public function testKeyTemplateDefaultValue()
    {
        $this->assertEquals('__SF2__%s', $this->symfonyTaggedCacheStrategy->getKeyTemplate());
    }

    public function testKeyTemplateGetterAndSetter()
    {
        $newKeyTemplate = 'foo';

        $this->symfonyTaggedCacheStrategy->setKeyTemplate($newKeyTemplate);

        $this->assertEquals($newKeyTemplate, $this->symfonyTaggedCacheStrategy->getKeyTemplate());
    }

    public function testFetchBlockWithWrongModelThrowsException()
    {
        $this->setExpectedException(InvalidValueException::class);

        $key = 'foo';

        $this->symfonyTaggedCacheStrategy->fetchBlock($key);
    }

    public function testFetchBlockReturnsContentFromCacheItemWhenHit()
    {
        $key = new KeyModel('key');
        $cacheContent = 'My cached content';
        $cacheCreateFunction = $this->createCacheCreateFunction();

        $cacheItemMock = $cacheCreateFunction('key', $cacheContent, true);

        $this->adapterMock->getItem('key')->willReturn($cacheItemMock);

        $actual = $this->symfonyTaggedCacheStrategy->fetchBlock($key);

        $this->assertEquals($cacheContent, $actual);
    }

    public function testFetchBlockReturnsFalseFromCacheItemWhenNotAHit()
    {
        $key = new KeyModel('key');
        $cacheContent = 'My cached content';
        $cacheCreateFunction = $this->createCacheCreateFunction();

        $cacheItem = $cacheCreateFunction('key', $cacheContent, false);

        $this->adapterMock->getItem('key')->willReturn($cacheItem);

        $actual = $this->symfonyTaggedCacheStrategy->fetchBlock($key);

        $this->assertFalse($actual);
    }

    public function testGenerateKeyThrowsExceptionWhenNonArrayPassed()
    {
        $this->setExpectedException(InvalidValueException::class);

        $this->symfonyTaggedCacheStrategy->generateKey('annotation', 'value');
    }

    public function testGenerateKeyLooksCorrectWithEmptyValues()
    {
        $value = [];

        $keyModel = $this->symfonyTaggedCacheStrategy->generateKey('annotation', $value);

        $this->assertEquals(0, $keyModel->lifetime);
        $this->assertEquals([], $keyModel->tags);
        $this->assertEquals('__SF2__annotation', $keyModel->key);
    }

    public function testGenerateKeyLooksCorrectWithPassedValues()
    {
        $value = [
            'tags' => ['a', 'b'],
            'lifetime' => 23,
        ];

        $keyModel = $this->symfonyTaggedCacheStrategy->generateKey('annotation', $value);

        $this->assertEquals(23, $keyModel->lifetime);
        $this->assertEquals(['a', 'b'], $keyModel->tags);
        $this->assertEquals('__SF2__annotation', $keyModel->key);
    }

    public function testSaveBlockThrowsExceptionWhenNonKeyModelPassed()
    {
        $this->setExpectedException(InvalidValueException::class);

        $this->symfonyTaggedCacheStrategy->saveBlock('not a key model', 'Content to cache');
    }

    public function testSaveBlockWithPsrCacheItemInterface()
    {
        $block = 'Content to cache';
        $lifetime = 23;

        $keyModel = new KeyModel('key', $lifetime);

        $cacheItemPsr = $this->prophesize(\Psr\Cache\CacheItemInterface::class);
        $cacheItemPsr->set($block)->shouldBeCalled();
        $cacheItemPsr->expiresAfter($lifetime)->shouldBeCalled();

        $this->adapterMock->getItem('key')->willReturn($cacheItemPsr);

        $this->adapterMock->save($cacheItemPsr)->shouldBeCalled();

        $this->symfonyTaggedCacheStrategy->saveBlock($keyModel, $block);
    }

    public function testSaveBlockWithCacheItem()
    {
        $block = 'Content to cache';
        $lifetime = 23;
        $cacheCreateFunction = $this->createCacheCreateFunction();

        // CacheItem is final, so we cannot prophesize it
        $cacheItem = $cacheCreateFunction('key', '', false);

        $keyModel = new KeyModel('key', $lifetime, ['tag1', 'tag2']);

        $this->adapterMock->getItem('key')->willReturn($cacheItem);
        $this->adapterMock->save($cacheItem)->shouldBeCalled();

        $this->symfonyTaggedCacheStrategy->saveBlock($keyModel, $block);

        // We just check the tags, we assume that the others are okay from the PSR cache item test
        PHPUnit_Framework_Assert::assertAttributeEquals(
            ['tag1' => 'tag1', 'tag2' => 'tag2'],
            'tags',
            $cacheItem
        );
    }

    /**
     * @return Closure
     */
    private function createCacheCreateFunction()
    {
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
