<?php namespace CM\JsonSchemaValidator\Validators;

use CM\JsonSchemaValidator\Exceptions\ValidateException;

trait NumberTrait
{
    public function validateNumber($data, $schema, $path)
    {
        // check for $schema->minimum
        if (isset($schema->minimum) && ($data < $schema->minimum)) {

            $this->addError(ValidateException::USER_NUMBER_MINIMUM_CHECK, [$path, $schema->minimum, $data]);
        }

        // check for $schema->maximum
        if (isset($schema->maximum) && ($data > $schema->maximum)) {

            $this->addError(ValidateException::USER_NUMBER_MAXIMUM_CHECK, [$path, $schema->maximum, $data]);
        }
    }
}