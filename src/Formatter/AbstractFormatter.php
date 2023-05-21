<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

/**
 * Class AbstractFormatter.
 *
 * Abstract formatter class to be extended by all formatters.
 */
abstract class AbstractFormatter implements FormatterInterface {

  /**
   * Array of variables.
   *
   * @var \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[]
   */
  protected $variables;

  /**
   * Array of configuration options.
   *
   * @var array
   */
  protected $config;

  /**
   * Command constructor.
   *
   * @param \AlexSkrypnyk\ShellVariablesExtractor\Entity\Variable[] $variables
   *   Array of variables.
   * @param array $config
   *   Array of configuration options.
   */
  public function __construct(array $variables, array $config = []) {
    $this->variables = $variables;
    $this->config = $config;
  }

}
