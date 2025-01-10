<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use AlexSkrypnyk\Shellvar\Filter\AbstractFilter;

/**
 * Test AbstractFilter.
 */
#[CoversClass(AbstractFilter::class)]
class AbstractFilterTest extends UnitTestBase {

  public function testAbstractFilter(): void {
    $this->assertEquals([], AbstractFilter::getConsoleOptions());
  }

}
