<?php namespace CMPayments\SchemaValidator\Validators;

use CMPayments\SchemaValidator\Exceptions\ValidateException;

/**
 * Class StringTrait
 *
 * @package CMPayments\SchemaValidator\Validators
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
trait StringTrait
{
    /**
     * Validates a string to a Schema
     *
     * @param $data
     * @param $schema
     * @param $path
     */
    public function validateString($data, $schema, $path)
    {
        // check for $schema->minLength
        if (isset($schema->minLength) && (($currentLength = strlen($data)) < $schema->minLength)) {

            $this->addError(ValidateException::ERROR_USER_STRING_MINIMUM_CHECK, [$path, $schema->minLength, $data, $currentLength]);
        }

        // check for $schema->maxLength
        if (isset($schema->maxLength) && (($currentLength = strlen($data)) > $schema->maxLength)) {

            $this->addError(ValidateException::ERROR_USER_STRING_MAXIMUM_CHECK, [$path, $schema->maxLength, $data, $currentLength]);
        }
    }
}