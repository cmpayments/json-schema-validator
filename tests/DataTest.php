<?php namespace CMPayments\tests\SchemaValidator\Tests;

use CMPayments\Cache\Cache;
use CMPayments\SchemaValidator\Exceptions\ValidateException;
use CMPayments\SchemaValidator\SchemaValidator;

/**
 * Class DataTest
 *
 * @package CMPayments\tests\SchemaValidator\Tests
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
class DataTest extends BaseTest
{
    /**
     * @return array
     */
    public function provideValidInput()
    {
        $valid = [
            '{"type": "boolean"}'                                                                                  => true,
            '{"type": "string"}'                                                                                   => 'test123',
            '{"type": "string", "enum": ["test"]}'                                                                 => 'test',
            '{"type": "array","items": {"type": "string"}}'                                                        => ['test123'],
            '{"type": "object", "properties": {"zipcode": {"type": "string", "pattern" : "[0-9]{4}[a-zA-Z]{2}"}}}' => json_decode('{"zipcode" : "48118EW"}')
        ];

        return $this->provideArrayForValidation($valid);
    }

    /**
     * @dataProvider provideValidInput
     *
     * @param $schema
     * @param $data
     */
    public function testParsesValidStrings($schema, $data)
    {
        $this->assertTrue((new SchemaValidator($data, json_decode($schema)))->isValid(), $schema);
    }

    /**
     * Test invalid values for valid formatted schemas
     */
    public function testParsesInvalidStrings()
    {
        // delete cache directory for optimal testing
        exec('rm -rf ' . (new Cache)->getDirectory() . '*');

        $func = function () {
        };

        $invalid =
            [
                // wrong type
                '{"type": "boolean"}'                              => ['test123', null, 1, 1.4, [], $func, curl_init(), new \stdClass()],
                '{"type": "string"}'                               => [true, null, 1, 1.4, [], $func, curl_init(), new \stdClass()],
                '{"type": "number"}'                               => [true, null, [], 'test123', $func, curl_init(), new \stdClass()],
                '{"type": "array", "items": {"type": "boolean" }}' => [null, 1, 1.4, 'test123', $func, curl_init(), new \stdClass()],
                '{"type": "object"}'                               => [true, null, 1, 1.4, 'test123', $func, curl_init(), []],

                // wrong type and wrong type for child
                '{"type": "array","items": {"type": "string"}}'    => ['test', true, 1, 1.4, new \stdClass(), [true], [1], [1.4], [new \stdClass()]]
            ];

        foreach ($invalid as $schema => $falseValues) {

            if (is_array($falseValues)) {

                foreach ($falseValues as $falseValue) {

                    $this->executeSchemaValidatorWhichResultsInvalid($schema, $falseValue);
                }
            } else {
                $this->executeSchemaValidatorWhichResultsInvalid($schema, $falseValues);
            }
        }
    }

    /**
     * Test Exceptions
     */
    public function testParseErrors()
    {
        $exceptions = [
            ValidateException::ERROR_USER_CHECK_IF_ALL_REQUIRED_PROPERTIES_ARE_SET => [
                [
                    json_decode('{"id": 2}'),
                    json_decode('{"type": "object", "properties": {"non-existent": {"type": "number"}}, "required": ["non-existent"]}')
                ]
            ],
            ValidateException::ERROR_USER_DATA_PROPERTY_IS_NOT_AN_ALLOWED_PROPERTY => [
                [
                    json_decode('{"testProperty": {"length": 7.0, "superfluousProperty": 12.0}}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "object", "properties": {"length": {"type": "number"}}}},"additionalProperties": false}')
                ]
            ],
            ValidateException::ERROR_USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_1 => [
                [
                    json_decode('{"testProperty": ["value1", "value2"]}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "object", "properties": {"length": {"type": "number"}}}}}')
                ]
            ],
            ValidateException::ERROR_USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_2 => [
                [
                    json_decode('{"testProperty": 2}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "object", "properties": {"length": {"type": "number"}}}}}')
                ]
            ],
            ValidateException::ERROR_USER_ARRAY_MINIMUM_CHECK                      => [
                [
                    json_decode('{"testProperty": ["1", "2", "3", "4", "5", "6", "7"]}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "array", "items": {"type": "string"}, "minItems": 8}}}')
                ],
                [
                    json_decode('[]'),
                    json_decode('{"type": "array", "minItems": 2, "items": {"type": "string"}}')
                ],
                [
                    json_decode('[1]'),
                    json_decode('{"type": "array", "minItems": 2, "items": {"type": "string"}}')
                ],
                [
                    json_decode('{"testProperty": ["2"]}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "array","minItems": 2, "items": {"type": "number"}}}}')
                ]
            ],
            ValidateException::ERROR_USER_ARRAY_MAXIMUM_CHECK                      => [
                [
                    json_decode('{"testProperty": ["1", "2", "3", "4", "5", "6", "7"]}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "array", "items": {"type": "string"}, "maxItems": 6}}}')
                ]
            ],
            ValidateException::ERROR_USER_ARRAY_NO_DUPLICATES_ALLOWED              => [
                [
                    json_decode('{"testProperty": [{"property": "value"}, {"property": "value"}]}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "array", "items": {"type": "object"}, "uniqueItems": true}}}')
                ],
                [
                    json_decode('{"testProperty": ["1", "2", "3", "4", "5", "6", "7", "7"]}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "array", "items": {"type": "string"}, "uniqueItems": true}}}')
                ]
            ],
            ValidateException::ERROR_USER_ENUM_NEEDLE_NOT_FOUND_IN_HAYSTACK        => [
                [
                    json_decode('{"testProperty": "value1"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string", "enum": ["value2", "value3"]}}}')
                ]
            ],
            ValidateException::ERROR_USER_FORMAT_INVALID_DATE                      => [
                [
                    json_decode('{"testProperty": "2015-11-28"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "date"}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42+00:00"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "date"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42Z"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "date"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "date"}}}')
                ],
                [
                    json_decode('{"testProperty": "1449150418"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "date"}}}')
                ],
                [
                    json_decode('{"testProperty": "28-11-2015"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "date"}}}')
                ],
                [
                    json_decode('{"testProperty": "16:58"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "date"}}}')
                ],
                [
                    json_decode('{"testProperty": "16:58:45"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "date"}}}')
                ]
            ],
            ValidateException::ERROR_USER_FORMAT_INVALID_DATETIME                  => [
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42+00:00"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42Z"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-28-11T10:16:42Z"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}')
                ],
                [
                    json_decode('{"testProperty": "1449150418"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}')
                ],
                [
                    json_decode('{"testProperty": "28-11-2015"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}')
                ],
                [
                    json_decode('{"testProperty": "16:58"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}')
                ],
                [
                    json_decode('{"testProperty": "16:58:45"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "datetime"}}}')
                ]
            ],
            ValidateException::ERROR_USER_FORMAT_INVALID_EMAIL                     => [
                [
                    json_decode('{"testProperty": "bw@cm.nl"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "email"}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "bw@cm.n"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "email"}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "bw@cm"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "email"}}}')
                ],
                [
                    json_decode('{"testProperty": "bw@cm."}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "email"}}}')
                ],
                [
                    json_decode('{"testProperty": "@cm.nl"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "email"}}}')
                ]
            ],
            ValidateException::ERROR_USER_FORMAT_INVALID_TIME                      => [
                [
                    json_decode('{"testProperty": "16:58:45"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "time"}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42+00:00"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "time"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42Z"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "time"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "time"}}}')
                ],
                [
                    json_decode('{"testProperty": "1449150418"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "time"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "time"}}}')
                ],
                [
                    json_decode('{"testProperty": "28-11-2015"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "time"}}}')
                ],
                [
                    json_decode('{"testProperty": "16:58"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "time"}}}')
                ]
            ],
            ValidateException::ERROR_USER_FORMAT_INVALID_UTC_SECONDS               => [
                [
                    json_decode('{"testProperty": "1449150418"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": 1449150418}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number","format": "utc-seconds"}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "16:58:45"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42+00:00"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42Z"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28T10:16:42"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}')
                ],
                [
                    json_decode('{"testProperty": "2015-11-28"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}')
                ],
                [
                    json_decode('{"testProperty": "28-11-2015"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}')
                ],
                [
                    json_decode('{"testProperty": "16:58"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}')
                ]
            ],
            ValidateException::ERROR_USER_NUMBER_MINIMUM_CHECK                     => [
                [
                    json_decode('{"testProperty": 3}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number", "minimum": 3}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": 3}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number", "minimum": 3, "maximum": 5}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": 2}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number", "minimum": 3}}}')
                ]
            ],
            ValidateException::ERROR_USER_NUMBER_MAXIMUM_CHECK                     => [
                [
                    json_decode('{"testProperty": 2}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number", "maximum": 2}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": 2}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number", "minimum": 2, "maximum": 3}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": 3}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number", "minimum": 2, "maximum": 3}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": 2}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number", "maximum": 1}}}')
                ],
                [
                    json_decode('{"testProperty": 4}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "number", "minimum": 2, "maximum": 3}}}')
                ],
            ],
            ValidateException::ERROR_USER_STRING_MINIMUM_CHECK                     => [
                [
                    json_decode('{"testProperty": "123456789"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string", "minLength": 9}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "1234"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string", "minLength": 5}}}')
                ]
            ],
            ValidateException::ERROR_USER_STRING_MAXIMUM_CHECK                     => [
                [
                    json_decode('{"testProperty": "12345678"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string", "maxLength": 8}}}'),
                    true
                ],
                [
                    json_decode('{"testProperty": "1234"}'),
                    json_decode('{"type": "object", "properties": {"testProperty": {"type": "string", "maxLength": 3}}}')
                ]
            ],
            ValidateException::ERROR_USER_REGEX_ERROR_LAST_ERROR_OCCURRED          => [
                [
                    json_decode('{"username": "rob"}'),
                    json_decode('{"type": "object", "properties": {"username": {"type": "string", "pattern" : "--[92929{{))"}}}'),
                ],
                [
                    json_decode('{"username": "rob"}'),
                    json_decode('{"type": "object", "properties": {"username": {"type": "string", "pattern" : "[9JDJ(JKlk3ko93030???jerjhu2/22/JJSJ"}}}'),
                ]
            ],
            ValidateException::ERROR_USER_REGEX_NO_MATCH                           => [
                [
                    json_decode('{"username": "rob"}'),
                    json_decode('{"type": "object", "properties": {"username": {"type": "string", "pattern" : "[aaaa]"}}}')
                ],
                [
                    json_decode('{"username": "rob", "zipcode" : "4811EW"}'),
                    json_decode('{"type": "object", "properties": {"username": {"type": "string"}, "zipcode" : {"type" : "string", "pattern" : "[a-zA-Z]{4}[0-9]{2}"}  }}')
                ],
                [
                    json_decode('{"username": "rob", "zipcode" : "doesnotstartwithanumber"}'),
                    json_decode('{"type": "object", "properties": {"username": {"type": "string"}, "zipcode" : {"type" : "string", "pattern" : "^[0-9][0-9a-zA-Z]{0,10}"}  }}')
                ],
            ]

            // @TODO; write tests for ValidateException::ERROR_USER_REGEX_PREG_LAST_ERROR_OCCURRED
            // @TODO; write tests for ValidateException::ERROR_USER_REGEX_UNKNOWN_ERROR_OCCURRED
            // @TODO; write tests for ValidateException::ERROR_USER_REGEX_GENERAL_ERROR_OCCURRED
        ];

        $this->executeExceptionValidation($exceptions, false);
    }
}