<?php

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Tests\Traits\FixtureTrait;
use AlexSkrypnyk\Shellvar\Tests\Traits\MockTrait;
use AlexSkrypnyk\Shellvar\Tests\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class UnitTestBase.
 *
 * Base class to unit tests.
 */
abstract class UnitTestBase extends TestCase {

  use MockTrait;
  use ReflectionTrait;
  use FixtureTrait;

}
