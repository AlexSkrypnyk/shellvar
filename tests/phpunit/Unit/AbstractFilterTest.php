<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use DrevOps\Shellvar\Filter\AbstractFilter;

/**
 * Test AbstractFilter.
 */
#[CoversClass(AbstractFilter::class)]
class AbstractFilterTest extends UnitTestBase {

  public function testAbstractFilter(): void {
    $this->assertEquals([], AbstractFilter::getConsoleOptions());
  }

}
