<?php

declare(strict_types = 1);

namespace AlexSkrypnyk\Shellvar\Tests\Unit;

use AlexSkrypnyk\Shellvar\Variable\Variable;
use AlexSkrypnyk\Shellvar\Variable\VariableAwareTrait;

/**
 * Test Variable.
 *
 * @coversDefaultClass \AlexSkrypnyk\Shellvar\Variable\Variable
 * @covers \AlexSkrypnyk\Shellvar\Variable\VariableAwareTrait
 */
class VariableTest extends UnitTestBase {

  use VariableAwareTrait;

  /**
   * @covers ::getIsInlineCode
   * @covers ::setIsInlineCode
   */
  public function testVariable(): void {
    $variable = new Variable('foo');
    $variable->setIsInlineCode(TRUE);

    $this->assertEquals(TRUE, $variable->getIsInlineCode());

    $this->setVariables([$variable]);
    $this->assertEquals([$variable], $this->getVariables());
  }

}
