<?php

declare(strict_types = 1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Filter\AbstractFilter;

/**
 * Test AbstractFilter.
 *
 * @coversDefaultClass \AlexSkrypnyk\Shellvar\Filter\AbstractFilter
 */
class AbstractFilterTest extends UnitTestBase {

  /**
   * @covers ::getConsoleOptions
   */
  public function testAbstractFilter(): void {
    $this->assertEquals([], AbstractFilter::getConsoleOptions());
  }

}
