<?php

declare(strict_types=1);

namespace DrevOps\Shellvar\Variable;

/**
 * Trait VariableAwareTrait.
 *
 * Provides functionality for classes that need to be aware of variables.
 */
trait VariableAwareTrait {

  /**
   * Variables.
   *
   * @var \DrevOps\Shellvar\Variable\Variable[]
   */
  protected $variables = [];

  /**
   * Get the variables.
   *
   * @return array<Variable>
   *   Returns list of DrevOps\Shellvar\Variable\Variable
   */
  public function getVariables(): array {
    return $this->variables;
  }

  /**
   * Set the variables.
   *
   * @param \DrevOps\Shellvar\Variable\Variable[] $variables
   *   The variables to set.
   */
  public function setVariables(array $variables): void {
    $this->variables = $variables;
  }

}
