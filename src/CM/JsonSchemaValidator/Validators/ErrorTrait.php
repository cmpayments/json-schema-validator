<?php namespace CM\JsonSchemaValidator\Validators;

use CM\JsonSchemaValidator\BaseValidator;
use CM\JsonSchemaValidator\Exceptions\ValidateException;

trait ErrorTrait
{
    protected $errors = [];

    private $stringifiedOrdinals = ['th', 'st', 'nd', 'rd'];

    private $prepositionDefault = 'a';

    private $prepositions = [
        BaseValidator::_ARRAY  => 'an',
        BaseValidator::BOOLEAN => 'a',
        BaseValidator::INTEGER => 'an',
        BaseValidator::NUMBER  => 'a',
        BaseValidator::OBJECT  => 'an',
        BaseValidator::STRING  => 'a'
    ];

    /**
     * @return null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param       $error
     * @param array $args
     */
    protected function addError($error, $args = [])
    {
        $message = (new ValidateException($error, $args))->getMessage();
        $this->errors[] = compact('error', 'args', 'message');
    }

    /**
     * Returns boolean on whether the JSON is valid or not
     *
     * @return bool
     */
    public function isValid()
    {
        return (count($this->getErrors()) === 0);
    }

    /**
     * Returns the preposition for a specific type
     *
     * @param $type
     *
     * @return mixed
     * @throws ValidateException
     */
    public function getPreposition($type)
    {
        if (!isset($this->prepositions[$type])) {

            // output exception when $this->config['debug'] === true
            if ($this->config['debug']) {

                throw new ValidateException(ValidateException::ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION, $type);
            } else {
                return $this->prepositionDefault;
            }
        }

        return $this->prepositions[$type];
    }

    /**
     * Returns a valid conjugation of the verb 'to be'
     *
     * @param $count
     *
     * @return string
     */
    public function conjugationToBe($count)
    {
        // 3rd singular or 3rd plural
        return ($count === 1) ? 'is' : 'are';
    }

    /**
     * Returns a valid representation of 'items' (or other value)
     *
     * @param        $count
     * @param string $single
     * @param string $plural
     *
     * @return string
     */
    public function conjugationObject($count, $single = 'item', $plural = 'items')
    {
        return ($count === 1) ? $single : $plural;
    }

    /**
     * Converts a number to a ordinal
     *
     * @param $number
     *
     * @return string
     */
    public function numberToOrdinal($number)
    {
        if (in_array(($lastDigit = (int)substr($number, -1)), [1, 2, 3])) {

            return $number . $this->stringifiedOrdinals[$lastDigit];
        }

        return $number . $this->stringifiedOrdinals[0];
    }
}