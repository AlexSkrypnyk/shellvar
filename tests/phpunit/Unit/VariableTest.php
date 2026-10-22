<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Tests\Unit;

use DrevOps\Shellvar\Variable\Variable;
use DrevOps\Shellvar\Variable\VariableAwareTrait;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test Variable.
 */
#[CoversClass(Variable::class)]
class VariableTest extends UnitTestBase {

  use VariableAwareTrait;

  public function testVariable(): void {
    $variable = new Variable('foo');
    $variable->setIsInlineCode(TRUE);

    $this->assertEquals(TRUE, $variable->getIsInlineCode());

    $this->setVariables([$variable]);
    $this->assertEquals([$variable], $this->getVariables());
  }

}
