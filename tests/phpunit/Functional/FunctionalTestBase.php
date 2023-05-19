<?php

namespace Drevops\Tests\Functional;

use Drevops\Tests\Unit\UnitTestBase;

/**
 * Class FunctionalTestBase.
 *
 * Base class to unit tests scripts.
 *
 * @group scripts
 */
abstract class FunctionalTestBase extends UnitTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    $this->validateScript();
  }

}
