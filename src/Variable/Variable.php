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
  protected $description;

  /**
   * The variable default value.
   *
   * @var mixed
   */
  protected $defaultValue;

  /**
   * Whether the variable is an assignment.
   *
   * @var bool
   */
  protected $isAssignment;

  /**
   * Whether the variable is an inline code.
   *
   * @var bool
   */
  protected $isInlineCode;

  /**
   * Variable constructor.
   *
   * @param string $name
   *   The variable name.
   * @param string $description
   *   The variable description.
   * @param mixed $default_value
   *   The variable default value.
   * @param bool $is_assignment
   *   Whether the variable is an assignment.
   * @param bool $is_inline_code
   *   Whether the variable is an inline code.
   */
  public function __construct($name, $description = '', $default_value = NULL, $is_assignment = FALSE, $is_inline_code = FALSE) {
    $this->name = $name;
    $this->description = $description;
    $this->defaultValue = $default_value;
    $this->isAssignment = $is_assignment;
    $this->isInlineCode = $is_inline_code;
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
   * @return array
   *   Internal variables as array.
   */
  public function toArray() {
    return [
      'name' => $this->name,
      'default_value' => $this->defaultValue,
      'description' => $this->description,
    ];
  }

}
