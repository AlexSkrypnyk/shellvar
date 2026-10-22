<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Unit;

use DrevOps\Shellvar\Tests\Traits\FixtureTrait;
use DrevOps\Shellvar\Tests\Traits\MockTrait;
use DrevOps\Shellvar\Tests\Traits\ReflectionTrait;
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
