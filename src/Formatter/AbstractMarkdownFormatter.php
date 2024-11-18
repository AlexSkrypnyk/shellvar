<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Formatter;

use AlexSkrypnyk\Shellvar\Config\Config;
use AlexSkrypnyk\Shellvar\Utils;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class AbstractMarkdownFormatter.
 *
 * Abstract formatter class to be extended by all Markdown formatters.
 */
abstract class AbstractMarkdownFormatter extends AbstractFormatter {

  final const VARIABLE_LINK_CASE_PRESERVE = 'preserve';

  final const VARIABLE_LINK_CASE_LOWER = 'lower';

  final const VARIABLE_LINK_CASE_UPPER = 'upper';

  /**
   * Get console options.
   *
   * @return array<InputOption>
   *   {@inheritdoc}
   */
  public static function getConsoleOptions(): array {
    return array_merge(parent::getConsoleOptions(), [
      new InputOption(
        name: 'md-link-vars',
        mode: InputOption::VALUE_NONE,
        description: 'Link variables within usages to their definitions in the Markdown output format.'
      ),
      new InputOption(
        name: 'md-link-vars-anchor-case',
        mode: InputOption::VALUE_REQUIRED,
        description: 'The case of the anchors when linking variables. Can be one of "preserve", "lower" or "upper".',
        default: static::VARIABLE_LINK_CASE_PRESERVE
      ),
      new InputOption(
        name: 'md-no-inline-code-wrap-vars',
        mode: InputOption::VALUE_NONE,
        description: 'Do not wrap variables to show them as inline code in the Markdown output format.'
      ),
      new InputOption(
        name: 'md-no-inline-code-wrap-numbers',
        mode: InputOption::VALUE_NONE,
        description: 'Do not wrap numbers to show them as inline code in the Markdown output format.'
      ),
      new InputOption(
        name: 'md-inline-code-extra-file',
        mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
        description: 'A path to a file that contains additional strings to be formatted as inline code in the Markdown output format.',
        default: [],
      ),
    ]);
  }

  /**
   * AbstractFormatter constructor.
   *
   * @param \AlexSkrypnyk\Shellvar\Config\Config $config
   *   The configuration.
   *   {@inheritdoc}.
   */
  protected function processConfig(Config $config): void {
    parent::processConfig($config);

    if (!empty($config->get('md-inline-code-extra-file'))) {
      $paths = (array) $config->get('md-inline-code-extra-file');
      $paths = array_filter($paths, static fn($path): bool => is_string($path));
      $config->set('md-inline-code-extra-file', Utils::getNonEmptyLinesFromFiles(Utils::resolvePaths($paths)));
    }
  }

  /**
   * Process variables data.
   */
  protected function processVariables(): void {
    parent::processVariables();

    if (!$this->config->get('md-no-inline-code-wrap-vars')) {
      $extra_file = $this->config->get('md-inline-code-extra-file');
      $extra_file = is_array($extra_file) ? $extra_file : [$extra_file];
      $this->variables = $this->processInlineCodeVars($this->variables, $extra_file);
    }

    if (!$this->config->get('md-no-inline-code-wrap-numbers')) {
      $this->variables = $this->processInlineCodeNumbers($this->variables);
    }

    if ($this->config->get('md-link-vars')) {
      $anchor_case = $this->config->get('md-link-vars-anchor-case');
      if (is_string($anchor_case)) {
        $this->variables = $this->processLinks($this->variables, $anchor_case);
      }
    }
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function processDescription(string $description): string {
    $description = parent::processDescription($description);

    $br = '<br/>';
    $nl = "\n";

    $lines = explode($nl, $description);

    // @code
    // List header<NL>
    // <NL>
    // - Item<NL>
    // - Item<NL>
    //   Line<NL>
    // - Item<NL>
    // <NL>
    // Not a list line1<NL>
    // Line2<NL>
    //
    // turns into:
    //
    // List header<NL>
    // <NL>
    // - Item<NL>
    // - Item<BR>Line<NL>
    // - Item<NL>
    // <NL>
    // Not a list line1<BR>Line2<NL>
    // @endcode
    $in_list = FALSE;
    foreach ($lines as $k => $line) {
      // Last line - do nothing.
      if ($k == count($lines) - 1) {
        continue;
      }
      // Current line is empty and previous line is empty - preserve NL.
      elseif ($k > 0 && empty($line[$k])) {
        $lines[$k - 1] = str_replace($br, '<NEWLINE>', $lines[$k - 1]);
        $lines[$k] .= '<NEWLINE>';
        $in_list = FALSE;
      }
      elseif ($this->isListItem($line)) {
        if ($k > 0) {
          $lines[$k - 1] = str_replace($br, '<NEWLINE>', $lines[$k - 1]);
        }
        $lines[$k] .= '<NEWLINE>';
        if ($k > 1 && $lines[$k - 1] === '<NEWLINE>') {
          $lines[$k - 2] = str_replace($br, '<NEWLINE>', $lines[$k - 2]);
        }
        $in_list = TRUE;
      }
      elseif ($in_list && !$this->isListItem($line[$k])) {
        $lines[$k - 1] = str_replace('<NEWLINE>', $br, $lines[$k - 1]);
        $lines[$k] = trim($lines[$k]) . '<NEWLINE>';
        $in_list = TRUE;
      }
      else {
        $lines[$k] .= $br;
        $in_list = FALSE;
      }
    }

    $description = implode('', $lines);

    return str_replace('<NEWLINE>', $nl, $description);
  }

  /**
   * Check if the string is a list item.
   *
   * @param string $string
   *   A string to check.
   *
   * @return bool
   *   TRUE if the string is a list item, FALSE otherwise.
   */
  protected function isListItem($string): bool {
    return str_starts_with($string, '- ') || str_starts_with($string, ' - ') || str_starts_with($string, '* ') || str_starts_with($string, ' * ');
  }

  /**
   * Process inline code.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   A list of variables to process.
   * @param string[] $tokens
   *   Additional tokens to be processed as inline code.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   A list of processed variables.
   */
  protected function processInlineCodeVars(array $variables, array $tokens = []): array {
    $var_tokens = array_map(static function ($v): string {
      return ltrim((string) $v, '$');
    }, array_keys($variables));

    foreach ($variables as $variable) {
      $variable->setName('`' . $variable->getName() . '`');

      $default_value = strval($variable->getDefaultValue());
      if ($default_value !== '') {
        // Wrap default value in code block if it contains new lines.
        if (str_contains($default_value, PHP_EOL)) {
          $default_value = PHP_EOL . '```' . PHP_EOL . $default_value . PHP_EOL . '```';
        }
        else {
          $default_value = '`' . $default_value . '`';
        }
        $variable->setDefaultValue($default_value);
      }

      $updated_paths = [];
      foreach ($variable->getPaths() as $path) {
        $updated_paths[] = '`' . $path . '`';
      }
      $variable->setPaths($updated_paths);

      // Update description: variable tokens and string tokens.
      $description = $variable->getDescription();
      foreach ($var_tokens as $var_token) {
        $description = preg_replace(
          '/(?<!`)\$' . preg_quote($var_token, '/') . '\b(?!`)/',
          '`$' . $var_token . '`',
          (string) $description
        );

        $description = preg_replace(
          '/(?<!`)\$\{' . preg_quote($var_token, '/') . '}(?!`)/',
          '`${' . $var_token . '}`',
          (string) $description
        );
      }

      foreach ($tokens as $token) {
        $token = trim($token);

        $description = preg_replace_callback('/(`.*?`)|\b' . preg_quote($token, '/') . '\b/', static function (array $matches) use ($token): string {
          return $matches[0] === $token ? sprintf('`%s`', $token) : $matches[0];
        }, (string) $description);
      }

      $variable->setDescription((string) $description);
    }

    return $variables;
  }

  /**
   * Process inline code to Convert numbers to code values.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   A list of variables to process.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   A list of processed variables.
   */
  protected function processInlineCodeNumbers(array $variables): array {
    foreach ($variables as $variable) {
      $variable->setDescription((string) preg_replace('/\b((?<!`)\d+)\b/', '`${1}`', $variable->getDescription()));
    }

    return $variables;
  }

  /**
   * Link variables.
   *
   * @param \AlexSkrypnyk\Shellvar\Variable\Variable[] $variables
   *   A list of variables to process.
   * @param string $anchor_case
   *   Anchor case.
   *
   * @return \AlexSkrypnyk\Shellvar\Variable\Variable[]
   *   A list of processed variables.
   */
  protected function processLinks(array $variables, $anchor_case = self::VARIABLE_LINK_CASE_PRESERVE): array {
    $var_tokens = array_map(static function ($v): string {
      return ltrim((string) $v, '$');
    }, array_keys($variables));

    foreach ($variables as $variable) {
      $description = $variable->getDescription();

      foreach ($var_tokens as $var_token) {
        $href = '#' . ($anchor_case == self::VARIABLE_LINK_CASE_PRESERVE ? urlencode($var_token) : ($anchor_case == self::VARIABLE_LINK_CASE_UPPER ? urlencode(strtoupper($var_token)) : urlencode(strtolower($var_token))));

        $description = preg_replace([
          '/(?<!`)\$\b' . preg_quote($var_token, '/') . '\b(?!`)/',
          '/(?<!`)\$\{\b' . preg_quote($var_token, '/') . '\b\}(?!`)/',
        ], [
          '[$' . $var_token . '](' . $href . ')',
          '[${' . $var_token . '}](' . $href . ')',
        ], (string) $description);

        $description = (string) preg_replace([
          '/`\$\b' . preg_quote($var_token, '/') . '\b`/',
          '/`\$\{\b' . preg_quote($var_token, '/') . '\b\}`/',
        ], [
          '[`$' . $var_token . '`](' . $href . ')',
          '[`${' . $var_token . '}`](' . $href . ')',
        ], (string) $description);
      }
      $variable->setDescription($description);
    }

    return $variables;
  }

}
