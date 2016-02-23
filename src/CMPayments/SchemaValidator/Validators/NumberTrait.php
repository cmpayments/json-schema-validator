<?php namespace CMPayments\SchemaValidator\Validators;

use CMPayments\SchemaValidator\Exceptions\ValidateException;

/**
 * Class NumberTrait
 *
 * @package CMPayments\SchemaValidator\Validators
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
trait NumberTrait
{
    public function validateNumber($data, $schema, $path)
    {
        // check for $schema->minimum
        if (isset($schema->minimum) && ($data < $schema->minimum)) {

            $this->addError(ValidateException::ERROR_USER_NUMBER_MINIMUM_CHECK, [$path, $schema->minimum, $data]);
        }

        // check for $schema->maximum
        if (isset($schema->maximum) && ($data > $schema->maximum)) {

            $this->addError(ValidateException::ERROR_USER_NUMBER_MAXIMUM_CHECK, [$path, $schema->maximum, $data]);
        }
    }
}