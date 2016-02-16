<?php namespace CMPayments\SchemaValidator\Validators;

use CMPayments\SchemaValidator\BaseValidator;
use CMPayments\SchemaValidator\Exceptions\ValidateException;

trait RegexTrait
{

    /**
     * Validates $data against a specific $schema->regex
     *
     * @param $data
     * @param $schema
     * @param $path
     */
    public function validateRegex($data, $schema, $path)
    {
        if (!isset($schema->pattern)) {
            return;
        }

        $pattern = '/' . trim($schema->pattern, '/') . '/';

        if (($schema->type !== BaseValidator::NUMBER || $schema->type !== BaseValidator::STRING) && !is_scalar($data)) {
            $this->addError(ValidateException::ERROR_USER_REGEX_DATA_NOT_SCALAR, [$data]);
        }

        /**
         * Use try catch to be able to handle malformed regex
         */
        try {
            $result = preg_match($pattern, $data);
            if ($result === false) {
                $this->addError(ValidateException::ERROR_USER_REGEX_NOMATCH, [$data, $schema->pattern]);
            } elseif ($result === null) {
                $this->addError(ValidateException::ERROR_USER_REGEX_PATTERN_NOT_VALID, [$schema->pattern, $data]);
            }
        } catch (\Exception $ex) {
            $this->addError(ValidateException::ERROR_USER_REGEX_PATTERN_NOT_VALID, [$schema->pattern, $data]);
        }
    }


}
