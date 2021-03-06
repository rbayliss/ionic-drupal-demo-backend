<?php

/**
 * @file
 * Contains \Drupal\Tests\Core\Cache\CacheTagsInvalidatorTest.
 */

namespace Drupal\Tests\Core\Cache;

use Drupal\Core\Cache\CacheTagsInvalidator;
use Drupal\Core\DependencyInjection\Container;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\Core\Cache\CacheTagsInvalidator
 * @group Cache
 */
class CacheTagsInvalidatorTest extends UnitTestCase {

  /**
   * @covers ::invalidateTags
   *
   * @expectedException \LogicException
   * @expectedExceptionMessage Cache tags must be strings, array given.
   */
  public function testInvalidateTagsWithInvalidTags() {
    $cache_tags_invalidator = new CacheTagsInvalidator();
    $cache_tags_invalidator->invalidateTags(['node' => [2, 3, 5, 8, 13]]);
  }

  /**
   * @covers ::invalidateTags
   * @covers ::addInvalidator
   */
  public function testInvalidateTags() {
    $cache_tags_invalidator = new CacheTagsInvalidator();

    // This does not actually implement,
    // \Drupal\Cache\Cache\CacheBackendInterface but we can not mock from two
    // interfaces, we would need a test class for that.
    $invalidator_cache_bin = $this->getMock('\Drupal\Core\Cache\CacheTagsInvalidator');
    $invalidator_cache_bin->expects($this->once())
      ->method('invalidateTags')
      ->with(array('node:1'));

    // We do not have to define that invalidateTags() is never called as the
    // interface does not define that method, trying to call it would result in
    // a fatal error.
    $non_invalidator_cache_bin = $this->getMock('\Drupal\Core\Cache\CacheBackendInterface');

    $container = new Container();
    $container->set('cache.invalidator_cache_bin', $invalidator_cache_bin);
    $container->set('cache.non_invalidator_cache_bin', $non_invalidator_cache_bin);
    $container->setParameter('cache_bins', array('cache.invalidator_cache_bin' => 'invalidator_cache_bin', 'cache.non_invalidator_cache_bin' => 'non_invalidator_cache_bin'));
    $cache_tags_invalidator->setContainer($container);

    $invalidator = $this->getMock('\Drupal\Core\Cache\CacheTagsInvalidator');
    $invalidator->expects($this->once())
      ->method('invalidateTags')
      ->with(array('node:1'));

    $cache_tags_invalidator->addInvalidator($invalidator);

    $cache_tags_invalidator->invalidateTags(array('node:1'));
  }

}
