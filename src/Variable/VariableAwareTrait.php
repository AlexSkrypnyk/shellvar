<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Variable;

/**
 * Trait VariableAwareTrait.
 *
 * Provides functionality for classes that need to be aware of variables.
 */
trait VariableAwareTrait {

  /**
   * Variables.
   *
   * @var \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[]
   */
  protected $variables = [];

  /**
   * Get the variables.
   */
  public function getVariables(): array {
    return $this->variables;
  }

  /**
   * Set the variables.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Variable\Variable[] $variables
   *   The variables to set.
   */
  public function setVariables(array $variables): void {
    $this->variables = $variables;
  }

}
