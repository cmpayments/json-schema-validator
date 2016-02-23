<?php namespace CMPayments\SchemaValidator\Exceptions;

/**
 * Class ValidateException
 *
 * @package CMPayments\SchemaValidator\Exceptions
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 * @Author  Rob Theeuwes <Rob.Theeuwes@cm.nl>
 */
class ValidateException extends BaseException
{
    const PREG_INTERNAL_ERROR        = 'An internal PCRE error occurred';
    const PREG_BACKTRACK_LIMIT_ERROR = 'Backtrack limit was exhausted';
    const PREG_RECURSION_LIMIT_ERROR = 'Recursion limit was exhausted';
    const PREG_BAD_UTF8_ERROR        = 'Malformed UTF-8 data';
    const PREG_BAD_UTF8_OFFSET_ERROR = 'Bad UTF8 offset';
    const PREG_JIT_STACK_LIMIT_ERROR = 'PCRE failed due to limited JIT stack space';

    const ERROR_INPUT_IS_NOT_A_OBJECT                         = 1;
    const ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH                = 2;
    const ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT               = 3;
    const ERROR_SCHEMA_PROPERTY_NOT_DEFINED                   = 4;
    const ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID            = 5;
    const ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID                = 6;
    const ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO        = 7;
    const ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX       = 8;
    const ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY     = 9;
    const ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH     = 10;
    const ERROR_INVALID_REFERENCE                             = 11;
    const ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED        = 12;
    const ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS             = 13;
    const ERROR_CURL_NOT_INSTALLED                            = 14;
    const ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST               = 15;
    const ERROR_NO_JSON_SCHEMA_WAS_FOUND                      = 16;
    const ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION              = 17;
    const ERROR_USER_CHECK_IF_ALL_REQUIRED_PROPERTIES_ARE_SET = 100;
    const ERROR_USER_DATA_PROPERTY_IS_NOT_AN_ALLOWED_PROPERTY = 101;
    const ERROR_USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_1 = 102;
    const ERROR_USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_2 = 103;
    const ERROR_USER_ARRAY_MINIMUM_CHECK                      = 110;
    const ERROR_USER_ARRAY_MAXIMUM_CHECK                      = 111;
    const ERROR_USER_ARRAY_NO_DUPLICATES_ALLOWED              = 112;
    const ERROR_USER_ENUM_NEEDLE_NOT_FOUND_IN_HAYSTACK        = 120;
    const ERROR_USER_FORMAT_INVALID_DATE                      = 130;
    const ERROR_USER_FORMAT_INVALID_DATETIME                  = 131;
    const ERROR_USER_FORMAT_INVALID_EMAIL                     = 132;
    const ERROR_USER_FORMAT_INVALID_TIME                      = 133;
    const ERROR_USER_FORMAT_INVALID_UTC_SECONDS               = 134;
    const ERROR_USER_NUMBER_MINIMUM_CHECK                     = 140;
    const ERROR_USER_NUMBER_MAXIMUM_CHECK                     = 141;
    const ERROR_USER_STRING_MINIMUM_CHECK                     = 150;
    const ERROR_USER_STRING_MAXIMUM_CHECK                     = 151;
    const ERROR_USER_REGEX_NO_MATCH                           = 160;
    const ERROR_USER_REGEX_DATA_NOT_SCALAR                    = 161;
    const ERROR_USER_REGEX_PREG_LAST_ERROR_OCCURRED           = 162;
    const ERROR_USER_REGEX_ERROR_LAST_ERROR_OCCURRED          = 163;
    const ERROR_USER_REGEX_UNKNOWN_ERROR_OCCURRED             = 164;
    const ERROR_USER_REGEX_GENERAL_ERROR_OCCURRED             = 165;


    protected $messages = [
        self::ERROR_INPUT_IS_NOT_A_OBJECT                         => '\'%s\' is not an object but %s \'%s\'%s',
        self::ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH                => 'The Schema input cannot be empty in %s',
        self::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT               => 'It\'s not allowed for object \'%s\' to have an empty key',
        self::ERROR_SCHEMA_PROPERTY_NOT_DEFINED                   => 'The mandatory Schema Property \'%s\' is not defined for \'%s\'',
        self::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID            => 'The given value \'%s\' for property \'%s.%s\' is not valid. Please use %sthe following %s: \'%s\'',
        self::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID                => 'The Schema Property \'%s.%s\' is not %s %s but %s %s',
        self::ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO        => 'The Schema Property \'%s.%s\' cannot be zero',
        self::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX       => 'The Schema Property \'%s.%s\' with value \'%d\' cannot be greater than Schema Property \'%s.%s\' with value \'%d\'',
        self::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY     => 'The Schema Property \'%s\' is not an array but %s \'%s\'',
        self::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH     => 'The %s: \'%s\' %s defined in \'%s\' but %s not defined in \'%s.properties\'',
        self::ERROR_INVALID_REFERENCE                             => 'Invalid reference; %s',
        self::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED        => 'No local definitions have been defined yet',
        self::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS             => 'The reference \'%s\' could not be matched to; \'%s\'',
        self::ERROR_CURL_NOT_INSTALLED                            => 'cURL not installed',
        self::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST               => 'The remote reference \'%s\' does not exist',
        self::ERROR_NO_JSON_SCHEMA_WAS_FOUND                      => 'No JSON Schema was not found at \'%s\'',
        self::ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION              => '\'%s\' is not a valid preposition',
        self::ERROR_USER_CHECK_IF_ALL_REQUIRED_PROPERTIES_ARE_SET => 'The mandatory Data %s: \'%s\' %s not set in \'%s\'',
        self::ERROR_USER_DATA_PROPERTY_IS_NOT_AN_ALLOWED_PROPERTY => 'The Data property \'%s\' is not an allowed property',
        self::ERROR_USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_1 => 'The Data property \'%s\' needs to be %s \'%s\' but got %s \'%s\'',
        self::ERROR_USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_2 => 'The Data property \'%s\' needs to be %s \'%s\' but got %s \'%s\' (with value \'%s\')',
        self::ERROR_USER_ARRAY_MINIMUM_CHECK                      => 'The minimum amount of items required for Data property \'%s\' (array) is %d %s. Currently there %s only %d %s present',
        self::ERROR_USER_ARRAY_MAXIMUM_CHECK                      => 'The maximum amount of items required for Data property \'%s\' (array) is %d. Currently there %s %d %s present',
        self::ERROR_USER_ARRAY_NO_DUPLICATES_ALLOWED              => 'There are no duplicate items allowed in \'%s\' (array)',
        self::ERROR_USER_ENUM_NEEDLE_NOT_FOUND_IN_HAYSTACK        => 'Invalid value for property \'%s\'. Value \'%s\' must match %s; \'%s\'',
        self::ERROR_USER_FORMAT_INVALID_DATE                      => 'Invalid date \'%s\' for property \'%s\', expected format YYYY-MM-DD',
        self::ERROR_USER_FORMAT_INVALID_DATETIME                  => 'Invalid Datetime \'%s\' for property \'%s\', expected format is \'YYYY-MM-DDThh:mm:ssZ\' or \'YYYY-MM-DDThh:mm:ss+hh:mm\'',
        self::ERROR_USER_FORMAT_INVALID_EMAIL                     => 'Invalid email address \'%s\' for property \'%s\', expected format is a RFC 822 format with the exceptions that comments and whitespace folding are not supported',
        self::ERROR_USER_FORMAT_INVALID_TIME                      => 'Invalid time \'%s\' for property \'%s\', expected format is \'hh:mm:ss\'',
        self::ERROR_USER_FORMAT_INVALID_UTC_SECONDS               => 'Invalid time \'%s\' for property \'%s\', expected format is number of seconds since Epoch',
        self::ERROR_USER_NUMBER_MINIMUM_CHECK                     => 'The minimum value for property \'%s\' is \'%d\' (current value \'%d\')',
        self::ERROR_USER_NUMBER_MAXIMUM_CHECK                     => 'The maximum value for property \'%s\' is \'%d\' (current value \'%d\')',
        self::ERROR_USER_STRING_MINIMUM_CHECK                     => 'The minimum string length for property \'%s\' is \'%d\' characters (current string length with value \'%s\' is \'%d\' characters)',
        self::ERROR_USER_STRING_MAXIMUM_CHECK                     => 'The maximum string length for property \'%s\' is \'%d\' characters (current string length with value \'%s\' is \'%d\' characters)',
        self::ERROR_USER_REGEX_NO_MATCH                           => 'The property \'%s\' does not match the regular expression \'%s\'',
        self::ERROR_USER_REGEX_DATA_NOT_SCALAR                    => 'The property \'%s\' should be a scalar to be able to validate it with a regular expression',
        self::ERROR_USER_REGEX_PREG_LAST_ERROR_OCCURRED           => 'Validating regular expression \'%s\' for property \'%s\' resulted in the following error: \'%s\'',
        self::ERROR_USER_REGEX_ERROR_LAST_ERROR_OCCURRED          => 'Validating regular expression \'%s\' for property \'%s\' resulted in the following error: \'%s\'',
        self::ERROR_USER_REGEX_UNKNOWN_ERROR_OCCURRED             => 'Validating regular expression \'%s\' for property \'%s\' resulted in an unknown error',
        self::ERROR_USER_REGEX_GENERAL_ERROR_OCCURRED             => 'Validating regular expression \'%s\' for property \'%s\' resulted in a general error'

    ];
}
