<?php namespace CMPayments\SchemaValidator\Exceptions;

use CMPayments\Exception\BaseException;

/**
 * Class ValidateSchemaException
 *
 * @package CMPayments\SchemaValidator\Exceptions
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
class ValidateSchemaException extends BaseException
{
    const ERROR_SCHEMA_IS_NOT_VALID_JSON                                    = 1;
    const ERROR_INPUT_IS_NOT_A_OBJECT                                       = 2;
    const ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH                              = 3;
    const ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT                             = 4;
    const ERROR_SCHEMA_PROPERTY_NOT_DEFINED                                 = 5;
    const ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID                          = 6;
    const ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID                              = 7;
    const ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO                      = 8;
    const ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX                     = 9;
    const ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY                   = 10;
    const ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH                   = 11;
    const ERROR_INVALID_REFERENCE                                           = 12;
    const ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED                      = 13;
    const ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS                           = 14;
    const ERROR_CURL_NOT_INSTALLED                                          = 15;
    const ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST                             = 16;
    const ERROR_NO_DATA_WAS_FOUND_IN_REMOTE_SCHEMA                          = 17;
    const ERROR_NO_VALID_JSON_WAS_FOUND_IN_REMOTE_SCHEMA                    = 18;
    const ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION                            = 19;
    const ERROR_SCHEMA_PROPERTY_TYPE_IS_ARRAY_BUT_VALUES_ARE_NOT_UNIQUE     = 20;
    const ERROR_SCHEMA_PROPERTY_TYPE_IS_ARRAY_BUT_VALUES_AR_NOT_ALL_STRINGS = 21;

    protected $messages = [
        self::ERROR_SCHEMA_IS_NOT_VALID_JSON                                    => 'Schema is not valid JSON',
        self::ERROR_INPUT_IS_NOT_A_OBJECT                                       => '\'%s\' is not an object but %s \'%s\'%s',
        self::ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH                              => 'The Schema input cannot be empty in %s',
        self::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT                             => 'It\'s not allowed for object \'%s\' to have an empty key',
        self::ERROR_SCHEMA_PROPERTY_NOT_DEFINED                                 => 'The mandatory Schema Property \'%s\' is not defined for \'%s\'',
        self::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID                          => 'The given value \'%s\' for property \'%s.%s\' is not valid. Please use %sthe following %s: \'%s\'',
        self::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID                              => 'The Schema Property \'%s.%s\' is not %s %s but %s %s',
        self::ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO                      => 'The Schema Property \'%s.%s\' cannot be zero',
        self::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX                     => 'The Schema Property \'%s.%s\' with value \'%d\' cannot be greater than Schema Property \'%s.%s\' with value \'%d\'',
        self::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY                   => 'The Schema Property \'%s\' is not an array but %s \'%s\'',
        self::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH                   => 'The %s: \'%s\' %s defined in \'%s\' but %s not defined in \'%s.properties\'',
        self::ERROR_INVALID_REFERENCE                                           => 'Invalid reference; %s',
        self::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED                      => 'No local definitions have been defined yet',
        self::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS                           => 'The reference \'%s\' could not be matched to; \'%s\'',
        self::ERROR_CURL_NOT_INSTALLED                                          => 'cURL not installed',
        self::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST                             => 'The remote reference \'%s\' does not exist',
        self::ERROR_NO_DATA_WAS_FOUND_IN_REMOTE_SCHEMA                          => 'No data found at \'%s\'',
        self::ERROR_NO_VALID_JSON_WAS_FOUND_IN_REMOTE_SCHEMA                    => 'No valid JSON Schema found at \'%s\'',
        self::ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION                            => '\'%s\' is not a valid preposition',
        self::ERROR_SCHEMA_PROPERTY_TYPE_IS_ARRAY_BUT_VALUES_ARE_NOT_UNIQUE     => 'The Schema Property \'%s.%s\' does not contain unique values',
        self::ERROR_SCHEMA_PROPERTY_TYPE_IS_ARRAY_BUT_VALUES_AR_NOT_ALL_STRINGS => 'The values of Schema Property \'%s.%s\' are not all of type \'%s\''
    ];

    /**
     * prepend classname to clarify error origin
     *
     * @param int    $code
     * @param null   $default
     * @param string $msgArray
     *
     * @return string
     */
    public function getItemFromVariableArray($code, $default = null, $msgArray = 'messages')
    {
        return (new \ReflectionClass($this))->getShortName() . ': ' . parent::getItemFromVariableArray($code, $default, $msgArray);
    }
}
