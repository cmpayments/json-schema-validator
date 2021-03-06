<?php namespace CMPayments\SchemaValidator\Validators;

use CMPayments\SchemaValidator\BaseValidator;
use CMPayments\SchemaValidator\Exceptions\ValidateException;

/**
 * Class FormatTrait
 *
 * @package CMPayments\SchemaValidator\Validators
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
trait FormatTrait
{
    /**
     * Validates $data against a specific $schema->format
     *
     * @param $data
     * @param $schema
     * @param string $path
     */
    public function validateFormat($data, $schema, $path)
    {
        if (!isset($schema->format)) {
            return;
        }

        switch ($schema->format) {

            // DATE
            case BaseValidator::DATE:
                if (!$date = $this->validateDateTime($data, 'Y-m-d')) {

                    $this->addError(ValidateException::ERROR_USER_FORMAT_INVALID_DATE, [$data, $path]);
                }
                break;

            // DATETIME
            case BaseValidator::DATETIME:
                if (!$this->validateDateTime($data, 'Y-m-d\TH:i:s\Z')
                    && !$this->validateDateTime($data, 'Y-m-d\TH:i:s.u\Z')
                    && !$this->validateDateTime($data, \DateTime::RFC3339)
                    && !$this->validateDateTime($data, \DateTime::ISO8601)
                ) {
                    $this->addError(ValidateException::ERROR_USER_FORMAT_INVALID_DATETIME, [$data, $path]);
                }
                break;

            // EMAIL
            case BaseValidator::EMAIL:
                if (is_null(filter_var($data, FILTER_VALIDATE_EMAIL, FILTER_NULL_ON_FAILURE))) {

                    $this->addError(ValidateException::ERROR_USER_FORMAT_INVALID_EMAIL, [$data, $path]);
                }
                break;

            // TIME
            case BaseValidator::TIME:
                if (!$this->validateDateTime($data, 'H:i:s')) {

                    $this->addError(ValidateException::ERROR_USER_FORMAT_INVALID_TIME, [$data, $path]);
                }
                break;

            // UTC_SECONDS (in epoch seconds)
            case BaseValidator::UTC_SECONDS:
                if (!$this->validateDateTime((string)$data, 'U')) { // U = Seconds since the Unix Epoch

                    $this->addError(ValidateException::ERROR_USER_FORMAT_INVALID_UTC_SECONDS, [$data, $path]);
                }
                break;

            // URL
            case BaseValidator::URL:

                if (filter_var($data, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED) === false) {

                    $this->addError(ValidateException::ERROR_USER_FORMAT_INVALID_URL, [$data, $path]);
                }
                break;
        }
    }

    /**
     * Validate a $datetime string and match it againt a $format
     *
     * @param $datetime
     * @param string $format
     *
     * @return bool
     */
    protected function validateDateTime($datetime, $format)
    {
        $dt = \DateTime::createFromFormat($format, $datetime);

        if (!$dt) {
            return false;
        }

        return $datetime === $dt->format($format);
    }

    /**
     * @param int   $code
     * @param array $args
     *
     * @return mixed
     */
    abstract public function addError($code, array $args = []);
}
