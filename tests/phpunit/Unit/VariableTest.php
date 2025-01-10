<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Variable\Variable;
use AlexSkrypnyk\Shellvar\Variable\VariableAwareTrait;
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
