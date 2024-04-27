<?php

declare(strict_types=1);

namespace AlexSkrypnyk\Shellvar\Variable;

/**
 * Class Variable.
 *
 * Defines a structure to describe a variable.
 */
class Variable {

  /**
   * The variable description.
   */
  protected string $description = '';

  /**
   * The variable default value.
   */
  protected mixed $defaultValue = NULL;

  /**
   * Path to the files where the variable is defined.
   *
   * @var array<string>
   */
  protected array $paths;

  /**
   * Whether the variable is an assignment.
   */
  protected bool $isAssignment = FALSE;

  /**
   * Whether the variable is an inline code.
   */
  protected bool $isInlineCode = FALSE;

  /**
   * Variable constructor.
   *
   * @param string $name
   *   The variable name.
   */
  public function __construct(protected string $name) {
  }

  /**
   * Get the variable name.
   *
   * @return string
   *   The variable name.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Set the variable name.
   *
   * @param string $name
   *   The variable name.
   *
   * @return Variable
   *   The variable instance.
   */
  public function setName(string $name): Variable {
    $this->name = $name;

    return $this;
  }

  /**
   * Get the variable description.
   *
   * @return string
   *   The variable description.
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * Set the variable description.
   *
   * @param string $description
   *   The variable description.
   *
   * @return Variable
   *   The variable instance.
   */
  public function setDescription(string $description): Variable {
    $this->description = $description;

    return $this;
  }

  /**
   * Get the variable default value.
   *
   * @return string|null
   *   The variable default value.
   */
  public function getDefaultValue(): string|null {
    return is_string($this->defaultValue) ? $this->defaultValue : NULL;
  }

  /**
   * Set the variable default value.
   *
   * @param string|null $value
   *   The variable default value.
   *
   * @return Variable
   *   The variable instance.
   */
  public function setDefaultValue(string|null $value): Variable {
    $this->defaultValue = $value;

    return $this;
  }

  /**
   * Get path to the file where the variable is defined.
   *
   * @return array<string>
   *   Path to the file where the variable is defined.
   */
  public function getPaths(): array {
    return $this->paths;
  }

  /**
   * Set path to the file where the variable is defined.
   *
   * @param array<string> $paths
   *   Array of path to the files where the variable is defined.
   *
   * @return Variable
   *   The variable instance.
   */
  public function setPaths(array $paths): Variable {
    $this->paths = [];
    foreach ($paths as $path) {
      $this->addPath($path);
    }

    return $this;
  }

  /**
   * Add path to the file where the variable is defined.
   *
   * @param string $path
   *   Path to the file where the variable is defined.
   *
   * @return Variable
   *   The variable instance.
   */
  public function addPath(string $path): Variable {
    $this->paths[] = $path;

    return $this;
  }

  /**
   * Get whether the variable is an assignment.
   *
   * @return bool
   *   Whether the variable is an assignment.
   */
  public function getIsAssignment(): bool {
    return $this->isAssignment;
  }

  /**
   * Set whether the variable is an assignment.
   *
   * @param bool $value
   *   Whether the variable is an assignment.
   *
   * @return Variable
   *   The variable instance.
   */
  public function setIsAssignment(bool $value): Variable {
    $this->isAssignment = $value;

    return $this;
  }

  /**
   * Get whether the variable is an inline code.
   *
   * @return bool
   *   Whether the variable is an inline code.
   */
  public function getIsInlineCode(): bool {
    return $this->isInlineCode;
  }

  /**
   * Set whether the variable is an inline code.
   *
   * @param bool $value
   *   Whether the variable is an inline code.
   *
   * @return Variable
   *   The variable instance.
   */
  public function setIsInlineCode(bool $value): Variable {
    $this->isInlineCode = $value;

    return $this;
  }

  /**
   * Merge provided variable into the current one.
   *
   * @param Variable $variable
   *   The variable to merge.
   */
  public function merge(Variable $variable): void {
    $paths = array_merge($this->getPaths(), $variable->getPaths());
    $paths = array_unique($paths);
    $this->setPaths($paths);
  }

  /**
   * Convert internal variables to array representation.
   *
   * @param array<string> $fields
   *   Array of field names to sort by.
   *
   * @return array<int|string, bool|float|int|string|null>
   *   Array of values, keyed by the order and name of the fields.
   *
   * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
   */
  public function toArray(array $fields = ['name', 'default_value', 'description']): array {
    $values = [
      'name' => $this->getName(),
      'default_value' => $this->getDefaultValue(),
      'description' => $this->getDescription(),
      'paths' => implode(', ', $this->getPaths()),
      'path' => count($this->getPaths()) > 0 ? $this->getPaths()[0] : '',
    ];

    return array_intersect_key(array_merge(array_flip($fields), $values), array_flip($fields));
  }

}
