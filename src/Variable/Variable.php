<?php

namespace AlexSkrypnyk\ShellVariablesExtractor\Variable;

/**
 * Class Variable.
 *
 * Defines a structure to describe a variable.
 */
class Variable {

  /**
   * The variable name.
   *
   * @var string
   */
  protected $name;

  /**
   * The variable description.
   *
   * @var string
   */
  protected $description = '';

  /**
   * The variable default value.
   *
   * @var mixed
   */
  protected $defaultValue = NULL;

  /**
   * Path to the file where the variable is defined.
   *
   * @var string
   */
  protected $path;

  /**
   * Whether the variable is an assignment.
   *
   * @var bool
   */
  protected $isAssignment = FALSE;

  /**
   * Whether the variable is an inline code.
   *
   * @var bool
   */
  protected $isInlineCode = FALSE;

  /**
   * Variable constructor.
   *
   * @param string $name
   *   The variable name.
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * Get the variable name.
   *
   * @return string
   *   The variable name.
   */
  public function getName() {
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
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * Get the variable description.
   *
   * @return string
   *   The variable description.
   */
  public function getDescription() {
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
  public function setDescription($description) {
    $this->description = $description;

    return $this;
  }

  /**
   * Get the variable default value.
   *
   * @return string
   *   The variable default value.
   */
  public function getDefaultValue() {
    return $this->defaultValue;
  }

  /**
   * Set the variable default value.
   *
   * @param string $defaultValue
   *   The variable default value.
   *
   * @return Variable
   *   The variable instance.
   */
  public function setDefaultValue($defaultValue) {
    $this->defaultValue = $defaultValue;

    return $this;
  }

  /**
   * Get path to the file where the variable is defined.
   *
   * @return string
   *   Path to the file where the variable is defined.
   */
  public function getPath(): string {
    return $this->path;
  }

  /**
   * Set path to the file where the variable is defined.
   *
   * @param string $path
   *   Path to the file where the variable is defined.
   *
   * @return Variable
   *   The variable instance.
   */
  public function setPath(string $path): Variable {
    $this->path = $path;

    return $this;
  }

  /**
   * Get whether the variable is an assignment.
   *
   * @return bool
   *   Whether the variable is an assignment.
   */
  public function getIsAssignment() {
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
    $this->isAssignment = (bool) $value;

    return $this;
  }

  /**
   * Get whether the variable is an inline code.
   *
   * @return bool
   *   Whether the variable is an inline code.
   */
  public function getIsInlineCode() {
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
    $this->isInlineCode = (bool) $value;

    return $this;
  }

  /**
   * Convert internal variables to array representation.
   *
   * @param array $fields
   *   Array of field names to sort by.
   *
   * @return array
   *   Array of values, keyed by the order and name of the fields.
   *
   * phpcs:disable Drupal.Arrays.Array.LongLineDeclaration
   */
  public function toArray(array $fields = ['name', 'default_value', 'description']): array {
    $values = [
      'name' => $this->name,
      'default_value' => $this->defaultValue,
      'description' => $this->description,
      'path' => $this->path,
    ];

    $values = array_merge(array_flip($fields), $values);

    return array_intersect_key($values, array_flip($fields));
  }

}
