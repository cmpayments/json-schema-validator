<?php namespace CMPayments\SchemaValidator\Exceptions;

use CMPayments\Exception\BaseException;

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
    const ERROR_USER_FORMAT_INVALID_URL                       = 135;
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
        self::ERROR_USER_FORMAT_INVALID_URL                       => 'Invalid URL \'%s\' for property \'%s\', expected format compliant with RFC2396 with the addition that the value must contain a valid scheme and a valid host',
        self::ERROR_USER_NUMBER_MINIMUM_CHECK                     => 'The minimum value for property \'%s\' is \'%d\' (current value \'%d\')',
        self::ERROR_USER_NUMBER_MAXIMUM_CHECK                     => 'The maximum value for property \'%s\' is \'%d\' (current value \'%d\')',
        self::ERROR_USER_STRING_MINIMUM_CHECK                     => 'The minimum string length for property \'%s\' is \'%d\' characters (current string length with value \'%s\' is \'%d\' characters)',
        self::ERROR_USER_STRING_MAXIMUM_CHECK                     => 'The maximum string length for property \'%s\' is \'%d\' characters (current string length with value \'%s\' is \'%d\' characters)',
        self::ERROR_USER_REGEX_NO_MATCH                           => 'The value \'%s\' of property \'%s\' is not valid',
        self::ERROR_USER_REGEX_DATA_NOT_SCALAR                    => 'The property \'%s\' should be a scalar to be able to validate it with a regular expression',
        self::ERROR_USER_REGEX_PREG_LAST_ERROR_OCCURRED           => 'Validating regular expression \'%s\' for property \'%s\' resulted in the following error: \'%s\'',
        self::ERROR_USER_REGEX_ERROR_LAST_ERROR_OCCURRED          => 'Validating regular expression \'%s\' for property \'%s\' resulted in the following error: \'%s\'',
        self::ERROR_USER_REGEX_UNKNOWN_ERROR_OCCURRED             => 'Validating regular expression \'%s\' for property \'%s\' resulted in an unknown error',
        self::ERROR_USER_REGEX_GENERAL_ERROR_OCCURRED             => 'Validating regular expression \'%s\' for property \'%s\' resulted in a general error'
    ];
}
