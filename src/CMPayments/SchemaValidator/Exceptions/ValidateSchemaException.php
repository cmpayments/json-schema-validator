<?php namespace CMPayments\SchemaValidator\Exceptions;

use CMPayments\Exceptions\BaseException;

/**
 * Class ValidateSchemaException
 *
 * @package CMPayments\SchemaValidator\Exceptions
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
class ValidateSchemaException extends BaseException
{
    const ERROR_INPUT_IS_NOT_A_OBJECT                     = 1;
    const ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH            = 2;
    const ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT           = 3;
    const ERROR_SCHEMA_PROPERTY_NOT_DEFINED               = 4;
    const ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID        = 5;
    const ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID            = 6;
    const ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO    = 7;
    const ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX   = 8;
    const ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY = 9;
    const ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH = 10;
    const ERROR_INVALID_REFERENCE                         = 11;
    const ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED    = 12;
    const ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS         = 13;
    const ERROR_CURL_NOT_INSTALLED                        = 14;
    const ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST           = 15;
    const ERROR_NO_JSON_SCHEMA_WAS_FOUND                  = 16;
    const ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION          = 17;

    protected $messages = [
        self::ERROR_INPUT_IS_NOT_A_OBJECT                     => '\'%s\' is not an object but %s \'%s\'%s',
        self::ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH            => 'The Schema input cannot be empty in %s',
        self::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT           => 'It\'s not allowed for object \'%s\' to have an empty key',
        self::ERROR_SCHEMA_PROPERTY_NOT_DEFINED               => 'The mandatory Schema Property \'%s\' is not defined for \'%s\'',
        self::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID        => 'The given value \'%s\' for property \'%s.%s\' is not valid. Please use %sthe following %s: \'%s\'',
        self::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID            => 'The Schema Property \'%s.%s\' is not %s %s but %s %s',
        self::ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO    => 'The Schema Property \'%s.%s\' cannot be zero',
        self::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX   => 'The Schema Property \'%s.%s\' with value \'%d\' cannot be greater than Schema Property \'%s.%s\' with value \'%d\'',
        self::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY => 'The Schema Property \'%s\' is not an array but %s \'%s\'',
        self::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH => 'The %s: \'%s\' %s defined in \'%s\' but %s not defined in \'%s.properties\'',
        self::ERROR_INVALID_REFERENCE                         => 'Invalid reference; %s',
        self::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED    => 'No local definitions have been defined yet',
        self::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS         => 'The reference \'%s\' could not be matched to; \'%s\'',
        self::ERROR_CURL_NOT_INSTALLED                        => 'cURL not installed',
        self::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST           => 'The remote reference \'%s\' does not exist',
        self::ERROR_NO_JSON_SCHEMA_WAS_FOUND                  => 'No JSON Schema was not found at \'%s\'',
        self::ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION          => '\'%s\' is not a valid preposition',
    ];
}