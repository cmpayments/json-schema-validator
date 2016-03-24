<?php namespace CMPayments\SchemaValidator\Validators;

use CMPayments\SchemaValidator\BaseValidator;
use CMPayments\SchemaValidator\Exceptions\ValidateException;

/**
 * Class RegexTrait
 *
 * @package CMPayments\SchemaValidator\Validators
 * @Author  Rob Theeuwes <Rob.Theeuwes@cm.nl>
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
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

            // must suppress the output... !@#$%^&*(
            $result = @preg_match($pattern, $data);

            // successful preg_match but no match
            if ($result === 0) {

                $this->addError(ValidateException::ERROR_USER_REGEX_NO_MATCH, [$data, $path]);
            } elseif ($result === false) {

                // preg_match resulted in an error, there are multiple causes why preg_match would result in an error
                $pregErrors = [
                    PREG_INTERNAL_ERROR        => ValidateException::PREG_INTERNAL_ERROR,
                    PREG_BACKTRACK_LIMIT_ERROR => ValidateException::PREG_BACKTRACK_LIMIT_ERROR,
                    PREG_RECURSION_LIMIT_ERROR => ValidateException::PREG_RECURSION_LIMIT_ERROR,
                    PREG_BAD_UTF8_ERROR        => ValidateException::PREG_BAD_UTF8_ERROR,
                    PREG_BAD_UTF8_OFFSET_ERROR => ValidateException::PREG_BAD_UTF8_OFFSET_ERROR
                ];

                // when dealing with a > PHP 7 environment another preg_last_error() error became available, if so add it to the list
                if (PHP_VERSION_ID > 70000) {

                    $pregErrors[PREG_JIT_STACKLIMIT_ERROR] = ValidateException::PREG_JIT_STACK_LIMIT_ERROR;
                }

                // check for preg_match error occurred
                if (isset($pregErrors[preg_last_error()])) {

                    $this->addError(ValidateException::ERROR_USER_REGEX_PREG_LAST_ERROR_OCCURRED, [$schema->pattern, $data, $pregErrors[preg_last_error()]]);
                } elseif (($error = error_get_last()) !== null) {

                    // preg_match could possibly throw an error, retrieve it
                    if (strpos($error['message'], 'preg_match()') !== false) {

                        $this->addError(ValidateException::ERROR_USER_REGEX_ERROR_LAST_ERROR_OCCURRED, [$schema->pattern, $data, substr($error['message'], strlen('preg_match(): '))]);
                    }
                } else {

                    // unknown error..
                    $this->addError(ValidateException::ERROR_USER_REGEX_UNKNOWN_ERROR_OCCURRED, [$schema->pattern, $data]);
                }
            }
        } catch (\Exception $ex) {

            $this->addError(ValidateException::ERROR_USER_REGEX_GENERAL_ERROR_OCCURRED, [$schema->pattern, $data]);
        }
    }
}
