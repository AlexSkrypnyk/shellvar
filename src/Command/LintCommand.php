<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lint for shell script variable.
 *
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class LintCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure(): void {
    parent::configure();
    $this->setName('lint');
    $this->setDescription('Check if shell script variables are wrapped in ${} and fix violations.');
    $this->setHelp('Check if shell script variables are wrapped in \${} and fix violations.');

    $this->addArgument('path', InputArgument::REQUIRED, 'File or directory to check');
    $this->addOption('extension', 'e', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'File extension to filter by. Applies only if specified path is a directory.', [
      'sh',
      'bash',
    ]);
    $this->addOption('fix', 'f', InputOption::VALUE_NONE, 'If the script should fix the variables in file.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $is_running_fix = (bool) $input->getOption('fix');

    $path = is_scalar($input->getArgument('path')) ? (string) $input->getArgument('path') : '';
    $extensions = is_array($input->getOption('extension')) ? $input->getOption('extension') : ['sh', 'bash'];

    if (is_dir($path)) {
      $files = array_map(static function (\SplFileInfo $f): string|false {
        return $f->getRealPath();
      }, array_filter(iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path))), static function (mixed $f) use ($extensions): bool {
        return $f instanceof \SplFileInfo && $f->isFile() && in_array($f->getExtension(), $extensions, TRUE);
      }));

      sort($files);
    }
    else {
      $files[] = $path;
    }

    $exit_code = 0;
    foreach ($files as $path) {
      $result = $this->processFile($path, $is_running_fix);

      if (!$result['success']) {
        $exit_code = Command::FAILURE;
      }

      $messages = empty($result['messages']) ? [] : $result['messages'];
      $output->writeln($messages);
    }

    return $exit_code;
  }

  /**
   * Process file.
   *
   * @param string $file_name
   *   File name.
   * @param bool $is_running_fix
   *   Run fix variables.
   *
   * @return array{'success': bool, 'messages': string[]}
   *   Result success or not and messages.
   */
  public function processFile(string $file_name, bool $is_running_fix = FALSE): array {
    $result = [
      'success' => TRUE,
      'messages' => [],
    ];

    // Load lines into array.
    $lines = @file($file_name);
    if ($lines === FALSE) {
      $result['success'] = FALSE;
      $result['messages'][] = 'Could not open file ' . $file_name;

      return $result;
    }

    // Process lines.
    $processed_lines = [];
    $changed_count = 0;
    foreach ($lines as $k => $line) {
      $processed_line = $this->processLine($line);
      $line_number = $k + 1;
      if ($processed_line !== $line) {
        // Removed new line from original line then push to messages.
        $line_removed_new_line = str_replace(["\r\n", "\n", "\r"], '', $line);
        $result['messages'][] = $is_running_fix ?
          sprintf('Replaced in line %s: %s', $line_number, $line_removed_new_line) : sprintf('%s: %s', $line_number, $line_removed_new_line);
        $changed_count++;
      }
      $processed_lines[] = $is_running_fix ? $processed_line : $line;
    }

    if ($is_running_fix) {
      $file_data = implode('', $processed_lines);
      file_put_contents($file_name, $file_data);
      $result['messages'][] = sprintf('Replaced %s variables in file "%s".', $changed_count, $file_name);
    }
    else {
      if ($changed_count > 0) {
        $result['messages'][] = sprintf('Found %s variables in file "%s" that are not wrapped in ${}.', $changed_count, $file_name);
      }
      $result['success'] = $changed_count === 0;
    }

    return $result;
  }

  /**
   * Process a line.
   *
   * @param string $line
   *   Line.
   *
   * @return string
   *   Line processed.
   */
  public function processLine(string $line): string {
    if (empty($line)) {
      return $line;
    }

    if (str_starts_with(trim($line), '#')) {
      return $line;
    }

    // Find and replace non-escaped variables.
    $updated_line = preg_replace_callback('/(?<!\\\)\$[a-zA-Z_]\w*/', function (array $matches) use ($line): string {
      $value = $matches[0][0];
      $pos = $matches[0][1];

      // Only replace within interpolation context.
      if (is_numeric($pos) && $this->isInterpolation($line, $pos)) {
        $value = '${' . substr($value, 1) . '}';
      }

      return $value ?: '';
    }, $line, -1, $count, PREG_OFFSET_CAPTURE);

    return $updated_line ?: $line;
  }

  /**
   * Check if the line at position is within interpolation context.
   *
   * This implementation has explicit statements to make it easier to understand
   * and maintain.
   *
   * @param string $line
   *   The line to check.
   * @param int $pos
   *   The position of the variable.
   *
   * @return bool
   *   TRUE if the line at position is within interpolation context.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  public function isInterpolation(string $line, int $pos): bool {
    // Normalize position.
    $pos = max($pos - 1, 0);

    $prev = $line[$pos] ?? '';
    $prefix = substr($line, 0, $pos);

    if (empty($prev)) {
      return FALSE;
    }

    // Find previous single or double quote.
    for ($i = $pos; $i >= 0; $i--) {
      $char = $line[$i] ?? '';
      if ($char === '"' || $char === "'") {
        $prev = $char;
        break;
      }
    }

    $double_even = substr_count($prefix, '"') % 2 == 0;

    if ($prev === '"' && $double_even) {
      return TRUE;
    }

    if ($prev === "'") {
      // Prev interpolation is closed - this is a new one.
      if ($double_even) {
        // New non-interpolation.
        return FALSE;

      }
      else {
        // Still within open interpolation.
        return TRUE;

      }
    }

    return TRUE;
  }

}
