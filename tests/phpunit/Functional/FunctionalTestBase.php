<?php

namespace AlexSkrypnyk\Tests\Functional;

use AlexSkrypnyk\Tests\Unit\UnitTestBase;

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
