<?php

namespace nadir2\core\validator;

use nadir2\core\RunnableInterface;

/**
 * This is class for input data validation.
 * @author Leonid Selikhov
 */
class Validator implements RunnableInterface
{
    /** @var mixed[] Input data for validation. */
    protected $data = null;

    /** @var array Set of fields and their corresponding rules for validation. */
    protected $items = [];

    /** @var array Set of validation rules. */
    protected $rules = [];

    /** @var array Stack of data errors which occured during the validation. */
    protected $errors = [];

    /** @var boolean Flag is equal true when the data is validated. */
    protected $isRan = false;

    /**
     * The constructor initializes the validator and also sets the input data for
     * validation.
     * @param mixed[] $data Input data.
     */
    public function __construct($data)
    {
        if (is_array($data)) {
            $this->data = $data;
            $this->init();
        } else {
            $this->isRan = true;
            $this->addError('Invalid data set format.');
        }
    }

    /**
     * The method returns a tree element by a key, which is formed as a string
     * separated by points and reflecting the nesting hierarchy.
     * @param mixed[] $data Input tree.
     * @param string $key The field name. The name of the nested field is formed by
     * the path of the tree the tiers of which are separated by the point.
     * @return mixed
     * @throws \extensions\validator\Exception
     */
    public static function getArrayItemByPointSeparatedKey(array& $data, string $key)
    {
        if (strpos($key, '.') !== false) {
            preg_match('/([a-zA-Z0-9_\-]+)\.([a-zA-Z0-9_\-\.]+)/', $key, $keys);
            if (!isset($data[$keys[1]])) {
                throw new Exception('Undefined index: '.$keys[1]);
            }
            if (!is_array($data[$keys[1]])) {
                throw new Exception("The element indexed {$keys[1]} isn't an array.");
            }
            return self::getArrayItemByPointSeparatedKey(
                $data[$keys[1]],
                $keys[2]
            );
        } elseif (isset($data[$key])) {
            return $data[$key];
        } else {
            throw new Exception('Undefined index: '.$key);
        }
    }

    /**
     * The method checks if the input tree contains an element with the specified
     * index (the index contains a point-separator of tiers)
     * @param mixed[] $data Input tree.
     * @param string $key The field name. The name of the nested field is formed by
     * the path of the tree the tiers of which are separated by the point.
     * @return boolean
     */
    public static function isIndexSet(array& $data, string $key): bool
    {
        try {
            self::getArrayItemByPointSeparatedKey($data, $key);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * This method fills the validator with default set of rules (and options of rules)
     * such as rules for validating required fields, strings, numbers, arrays etc.
     * @return void
     */
    private function init(): void
    {
        $data = $this->data;

        $this
            // Required value rules
            ->addRule(
                'required',
                function (string $fieldName) use ($data): bool {
                    if (self::isIndexSet($data, $fieldName)) {
                        return true;
                    }
                    return false;
                },
                function ($fieldName): string {
                    return "Field '{$fieldName}' is required.";
                }
            )
            // String rules
            ->addRule(
                'string',
                function (string $fieldName, array $options = []) use ($data): bool {
                    if (self::isIndexSet($data, $fieldName)) {
                        $value = self::getArrayItemByPointSeparatedKey($data, $fieldName);
                        if (!is_string($value)) {
                            return false;
                        }
                        if (isset($options['notEmpty'])) {
                            $trimVal = trim($value);
                            if ($options['notEmpty'] && empty($trimVal)) {
                                return false;
                            }
                            if (!$options['notEmpty'] && !empty($trimVal)) {
                                return false;
                            }
                        }
                        if (isset($options['pattern'])) {
                            if (!preg_match($options['pattern'], $value)) {
                                return false;
                            }
                        }
                        if (isset($options['length'])) {
                            $length = mb_strlen($value);
                            if (isset($options['length']['min'])) {
                                if ($length < $options['length']['min']) {
                                    return false;
                                }
                            }
                            if (isset($options['length']['max'])) {
                                if ($length > $options['length']['max']) {
                                    return false;
                                }
                            }
                            if (isset($options['length']['equal'])) {
                                if ($length != $options['length']['equal']) {
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                },
                function (string $fieldName, array $options = []): string {
                    if (!empty($options)) {
                        $keys = implode(', ', array_keys($options));
                        return "Invalid string field '{$fieldName}' value. "
                        . "Validation options: {$keys}";
                    }
                    return "Invalid string field '{$fieldName}' value.";
                }
            )
            // Number rules
            ->addRule(
                'number',
                function (string $fieldName, array $options = []) use ($data): bool {
                    if (self::isIndexSet($data, $fieldName)) {
                        $value = self::getArrayItemByPointSeparatedKey($data, $fieldName);
                        if (!is_numeric($value)) {
                            return false;
                        }
                        if (isset($options['float'])) {
                            if ($options['float'] && !is_float($value)) {
                                return false;
                            }
                            if (!$options['float'] && is_float($value)) {
                                return false;
                            }
                        }
                        if (isset($options['integer'])) {
                            if ($options['integer'] && !is_int($value)) {
                                return false;
                            }
                            if (!$options['integer'] && is_int($value)) {
                                return false;
                            }
                        }
                        if (isset($options['positive'])) {
                            if ($options['positive'] && $value <= 0) {
                                return false;
                            }
                            if (!$options['positive'] && $value >= 0) {
                                return false;
                            }
                        }
                        if (isset($options['value'])) {
                            if (isset($options['value']['equal'])) {
                                if ($value != $options['value']['equal']) {
                                    return false;
                                }
                            }
                            if (isset($options['value']['min'])) {
                                if ($value < $options['value']['min']) {
                                    return false;
                                }
                            }
                            if (isset($options['value']['max'])) {
                                if ($value > $options['value']['max']) {
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                },
                function (string $fieldName, array $options = []) {
                    if (!empty($options)) {
                        $keys = implode(', ', array_keys($options));
                        return "Invalid number field '{$fieldName}' value. "
                        . "Validation options: {$keys}";
                    }
                    return "Invalid number field '{$fieldName}' value.";
                }
            )
            // Array rules
            ->addRule(
                'array',
                function (string $fieldName, array $options = []) use ($data): bool {
                    if (self::isIndexSet($data, $fieldName)) {
                        $value = self::getArrayItemByPointSeparatedKey($data, $fieldName);
                        if (!is_array($value)) {
                            return false;
                        }
                        if (isset($options['assoc'])) {
                            $isAssoc = function (array $array): bool {
                                // return false if array is empty
                                return (bool) count(array_filter(
                                    array_keys($array),
                                    'is_string'
                                ));
                            };
                            if ($options['assoc'] && !$isAssoc($value)) {
                                return false;
                            }
                            if (!$options['assoc'] && $isAssoc($value)) {
                                return false;
                            }
                            unset($isAssoc);
                        }
                        if (isset($options['length'])) {
                            $length = count($value);
                            if (isset($options['length']['min'])) {
                                if ($length < $options['length']['min']) {
                                    return false;
                                }
                            }
                            if (isset($options['length']['max'])) {
                                if ($length > $options['length']['max']) {
                                    return false;
                                }
                            }
                            if (isset($options['length']['equal'])) {
                                if ($length != $options['length']['equal']) {
                                    return false;
                                }
                            }
                        }
                    }
                    return true;
                },
                function (string $fieldName, array $options = []): string {
                    if (!empty($options)) {
                        $keys = implode(', ', array_keys($options));
                        return "Invalid array field '{$fieldName}' value. "
                        . "Validation options: {$keys}";
                    }
                    return "Invalid array field '{$fieldName}' value.";
                }
            )
            // Boolean rules
            ->addRule(
                'boolean',
                function (string $fieldName, array $options = []) use ($data): bool {
                    if (self::isIndexSet($data, $fieldName)) {
                        $value = self::getArrayItemByPointSeparatedKey($data, $fieldName);
                        if (!is_bool($value)) {
                            return false;
                        }
                        if (isset($options['isTrue'])) {
                            if ($options['isTrue'] && !$value) {
                                return false;
                            }
                            if (!$options['isTrue'] && $value) {
                                return false;
                            }
                        }
                    }
                    return true;
                },
                function (string $fieldName, array $options = []): string {
                    if (!empty($options)) {
                        $keys = implode(', ', array_keys($options));
                        return "Invalid boolean field '{$fieldName}' value. "
                        . "Validation options: {$keys}";
                    }
                    return "Invalid boolean field '{$fieldName}' value.";
                }
            );
    }

    /**
     * The method adds a set of fields and their corresponding rules and parameters
     * for validating the input data
     * @param array $item This set is an array whose first element is a string
     * with a field name (or an array of field names), the second element is the
     * name of the validation rule (always a string), the third element is an
     * optional array of validation options.
     * @return self
     * @throws \extensions\validator\Exception
     */
    public function addItem(array $item): self
    {
        if (count($item) < 2) {
            throw new Exception('Invalid count of item elements.');
        }
        $this->items[] = $item;
        return $this;
    }

    /**
     * This is mass analog for addItem() method.
     * @param array $items The input array of sets.
     * @return self
     */
    public function setItems(array $items): self
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * The method adds a validation rule to the stack of validator rulesets.
     * @param string $name The name of rule.
     * @param callable $func The callback function that defines the functional
     * of the data validation rule. The first parameter is the name of the validated
     * field, the second optional parameter is the set of validation options,
     * and the context (closure) is the input data.
     * @param string|callable|null $errorMsg The error message or callable function
     * which generates this message. This parameter is optional.
     * @return self
     */
    public function addRule(string $name, callable $func, $errorMsg = null): self
    {
        $this->rules[$name] = array($func, $errorMsg);
        return $this;
    }

    /**
     * The method adds the error message to the error stack which occurred during
     * validation.
     * @param string $sMsg
     * @return void
     */
    protected function addError(string $msg): void
    {
        $this->errors[] = $msg;
    }

    /**
     * The method adds default message to form a description of the validation
     * errors.
     * @param string $fieldName The field name.
     * @return void
     */
    protected function addDefaultError(string $fieldName): void
    {
        $this->addError("Invalid field '{$fieldName}' value.");
    }

    /**
     * The method applies the validation rule to the validable field.
     * @param string $fieldName The field name. The name of the nested field is
     * formed by the path of the tree the tiers of which are separated by the point.
     * @param string $ruleName The validation rule name.
     * @param array $options The validation options.
     * @return void
     * @throws \extensions\validator\Exception
     */
    private function applyRuleToField(
        string $fieldName,
        string $ruleName,
        array $options = []
    ): void {
        if (!isset($this->rules[$ruleName])) {
            throw new Exception('Undefined rule name.');
        }
        $func = $this->rules[$ruleName][0];
        if (!$func($fieldName, $options)) {
            if (isset($this->rules[$ruleName][1])) {
                if (is_callable($this->rules[$ruleName][1])) {
                    // If message entity is function
                    $funcMsg = $this->rules[$ruleName][1];
                    $this->addError($funcMsg($fieldName, $options));
                } else {
                    // If message entity is string
                    $this->addError((string) $this->rules[$ruleName][1]);
                }
            } else {
                // If message entity isn't set
                $this->addDefaultError($fieldName);
            }
        }
    }

    /**
     * The main executable method.
     * @return void
     */
    public function run(): void
    {
        if (!$this->isRan) {
            $this->isRan = true;
            foreach ($this->items as $item) {
                $options      = $item[2] ?? [];
                $ruleName = $item[1];
                foreach (is_array($item[0]) ? $item[0] : [$item[0]] as $fieldName) {
                    self::applyRuleToField($fieldName, $ruleName, $options);
                }
            }
        }
    }

    /**
     * It checks if processed input data is valid or not.
     * @return boolean
     * @throws \extensions\validator\Exception
     */
    public function isValid(): bool
    {
        if (!$this->isRan) {
            throw new Exception("The validation wasn't ran.");
        }
        return empty($this->errors);
    }

    /**
     * The method returns the contents of the stack of validation errors of
     * input data.
     * @return string[] The array of validation errors.
     * @throws \extensions\validator\Exception.
     */
    public function getErrors(): array
    {
        if (!$this->isRan) {
            throw new Exception("The validation wasn't ran.");
        }
        return $this->errors;
    }
}
