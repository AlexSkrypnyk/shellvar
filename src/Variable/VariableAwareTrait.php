<?php

namespace AlexSkrypnyk\Shellvar\Variable;

/**
 * Trait VariableAwareTrait.
 *
 * Provides functionality for classes that need to be aware of variables.
 */
trait VariableAwareTrait {

  /**
   * Variables.
   *
   * @var \AlexSkrypnyk\Shellvar\Variable\Variable[]
   */
  protected $variables = [];

  /**
   * Get the variables.
   *
   * @return array<Variable>
   *   Returns list of AlexSkrypnyk\Shellvar\Variable\Variable
   */
  public function getVariables(): array {
    return $this->variables;
  }

  /**
   * Set the variables.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   The variables to set.
   */
  public function setVariables(array $variables): void {
    $this->variables = $variables;
  }

}
