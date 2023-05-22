<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Formatter;

/**
 * Class DummyFormatter.
 *
 * Dummy formatter class to be used in tests.
 */
class DummyFormatter extends AbstractFormatter {

  /**
   * {@inheritdoc}
   */
  public static function getName(): string {
    return 'dummy';
  }

  /**
   * {@inheritdoc}
   */
  public function format(): string {
    $output = '';

    foreach ($this->variables as $variable) {
      $output .= implode(', ', $variable->toArray()) . PHP_EOL;
    }

    return $output;
  }

}
