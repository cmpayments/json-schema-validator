<?php namespace CM\JsonSchemaValidator\Exceptions;

class ValidateException extends BaseException
{
    const ERROR_CACHE_DIRECTORY_NOT_WRITABLE              = 1;
    const ERROR_SCHEMA_NO_OBJECT                          = 2;
    const ERROR_SCHEMA_CANNOT_BE_EMPTY                    = 3;
    const ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT           = 4;
    const ERROR_SCHEMA_PROPERTY_NOT_DEFINED               = 5;
    const ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID        = 6;
    const ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID            = 7;
    const ERROR_SCHEMA_PROPERTY_CANNOT_NOT_BE_ZERO        = 8;
    const ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX   = 9;
    const ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY = 10;
    const ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH = 11;
    const ERROR_VALIDATION_METHOD_DOES_NOT_EXIST          = 12;
    const ERROR_INVALID_REFERENCE                         = 13;
    const ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED    = 14;
    const ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS         = 15;
    const ERROR_CURL_NOT_INSTALLED                        = 16;
    const ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST           = 17;
    const ERROR_NO_JSON_SCHEMA_WAS_FOUND                  = 18;
    const ERROR_INPUT_IS_NOT_A_STRING                     = 19;
    const ERROR_INPUT_IS_NOT_VALID_JSON                   = 20;
    const ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION          = 21;
    const USER_CHECK_IF_ALL_REQUIRED_PROPERTIES_ARE_SET   = 100;
    const USER_DATA_PROPERTY_IS_NOT_A_VALID_PROPERTY      = 101;
    const USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_1   = 102;
    const USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_2   = 103;
    const USER_ARRAY_MINIMUM_CHECK                        = 110;
    const USER_ARRAY_MAXIMUM_CHECK                        = 111;
    const USER_ARRAY_NO_DUPLICATES_ALLOWED                = 112;
    const USER_ENUM_NEEDLE_NOT_FOUND_IN_HAYSTACK          = 120;
    const USER_FORMAT_INVALID_DATE                        = 130;
    const USER_FORMAT_INVALID_DATETIME                    = 131;
    const USER_FORMAT_INVALID_EMAIL                       = 132;
    const USER_FORMAT_INVALID_TIME                        = 133;
    const USER_FORMAT_INVALID_UTC_SECONDS                 = 134;
    const USER_NUMBER_MINIMUM_CHECK                       = 140;
    const USER_NUMBER_MAXIMUM_CHECK                       = 141;
    const USER_STRING_MINIMUM_CHECK                       = 150;
    const USER_STRING_MAXIMUM_CHECK                       = 151;

    const MESSAGES = [
        self::ERROR_CACHE_DIRECTORY_NOT_WRITABLE              => 'The cache directory \'%s\' is not writable',
        self::ERROR_SCHEMA_NO_OBJECT                          => 'The Schema is not an object in %s',
        self::ERROR_SCHEMA_CANNOT_BE_EMPTY                    => 'The Schema cannot be empty in %s',
        self::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT           => 'It\'s not allowed for object \'%s\' to have an empty key',
        self::ERROR_SCHEMA_PROPERTY_NOT_DEFINED               => 'The mandatory Schema Property \'%s\' is not defined for \'%s\'',
        self::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID        => 'The given value \'%s\' for property \'%s.%s\' is not valid. Please use %sthe following %s: \'%s\'',
        self::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID            => 'The Schema Property \'%s.%s\' is not %s %s but %s %s',
        self::ERROR_SCHEMA_PROPERTY_CANNOT_NOT_BE_ZERO        => 'The Schema Property \'%s.%s\' cannot be zero',
        self::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX   => 'The Schema Property \'%s.%s\' with value \'%d\' cannot be greater than Schema Property \'%s.%s\' with value \'%d\'',
        self::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY => 'The Schema Property \'%s\' is not an array but %s \'%s\'',
        self::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH => 'The %s: \'%s\' %s defined in \'%s\' but %s not defined in \'%s.properties\'',
        self::ERROR_VALIDATION_METHOD_DOES_NOT_EXIST          => 'The validation method \'%s\' does not exist for type \'%s\'',
        self::ERROR_INVALID_REFERENCE                         => 'Invalid reference; %s',
        self::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED    => 'No local definitions have been defined yet',
        self::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS         => 'The reference \'%s\' could not be matched to; \'%s\'',
        self::ERROR_CURL_NOT_INSTALLED                        => 'cURL not installed',
        self::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST           => 'The remote reference \'%s\' does not exist',
        self::ERROR_NO_JSON_SCHEMA_WAS_FOUND                  => 'No JSON Schema was not found at \'%s\'',
        self::ERROR_INPUT_IS_NOT_A_STRING                     => '\'%s\' is not a string but %s \'%s\'',
        self::ERROR_INPUT_IS_NOT_VALID_JSON                   => '\'%s\' input is not valid JSON',
        self::ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION          => '\'%s\' is not a valid preposition',
        self::USER_CHECK_IF_ALL_REQUIRED_PROPERTIES_ARE_SET   => 'The mandatory Data %s: \'%s\' %s not set in \'%s\'',
        self::USER_DATA_PROPERTY_IS_NOT_A_VALID_PROPERTY      => 'The Data property \'%s\' is not a valid property',
        self::USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_1   => 'The Data property \'%s\' needs to be %s \'%s\' but got %s \'%s\'',
        self::USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_2   => 'The Data property \'%s\' needs to be %s \'%s\' but got %s \'%s\' (with value \'%s\')',
        self::USER_ARRAY_MINIMUM_CHECK                        => 'The minimum amount of items required for Data property \'%s\' (array) is %d %s. Currently there %s only %d %s present',
        self::USER_ARRAY_MAXIMUM_CHECK                        => 'The maximum amount of items required for Data property \'%s\' (array) is %d. Currently there %s %d %s present',
        self::USER_ARRAY_NO_DUPLICATES_ALLOWED                => 'There are no duplicate items allowed in \'%s\' (array)',
        self::USER_ENUM_NEEDLE_NOT_FOUND_IN_HAYSTACK          => 'Invalid value for property \'%s\'. Value \'%s\' must match %s; \'%s\'',
        self::USER_FORMAT_INVALID_DATE                        => 'Invalid date \'%s\' for property \'%s\', expected format YYYY-MM-DD',
        self::USER_FORMAT_INVALID_DATETIME                    => 'Invalid Datetime \'%s\' for property \'%s\', expected format is \'YYYY-MM-DDThh:mm:ssZ\' or \'YYYY-MM-DDThh:mm:ss+hh:mm\'',
        self::USER_FORMAT_INVALID_EMAIL                       => 'Invalid email address \'%s\' for property \'%s\', expected format is a RFC 822 format with the exceptions that comments and whitespace folding are not supported',
        self::USER_FORMAT_INVALID_TIME                        => 'Invalid time \'%s\' for property \'%s\', expected format is \'hh:mm:ss\'',
        self::USER_FORMAT_INVALID_UTC_SECONDS                 => 'Invalid time \'%s\' for property \'%s\', expected format is number of seconds since Epoch',
        self::USER_NUMBER_MINIMUM_CHECK                       => 'The minimum value for property \'%s\' is \'%d\' (current value \'%d\')',
        self::USER_NUMBER_MAXIMUM_CHECK                       => 'The maximum value for property \'%s\' is \'%d\' (current value \'%d\')',
        self::USER_STRING_MINIMUM_CHECK                       => 'The minimum string length for property \'%s\' is \'%d\' characters (current string length with value \'%s\' is \'%d\' characters)',
        self::USER_STRING_MAXIMUM_CHECK                       => 'The maximum string length for property \'%s\' is \'%d\' characters (current string length with value \'%s\' is \'%d\' characters)'
    ];
}