<?php namespace CMPayments\tests\SchemaValidator\Tests;

use CMPayments\SchemaValidator\Exceptions\ValidateException;
use CMPayments\SchemaValidator\SchemaValidator;

class SchemaRegexTest extends BaseTest
{
    const INVALID_SCHEMA_PATTERN_STRING = '{"type": "object","properties": {"name": {"type": "string","pattern" : "(*92190(***(0[a-z]+"}}}';
    const VALID_SCHEMA_PATTERN_STRING   = '{"type": "object","properties": {"name": {"type": "string","pattern" : "[a-z]+"}}}';
    const DATA_VALID_STRING             = '{"name" : "sjaak"}';
    const DATA_INVALID_STRING           = '{"name" : "sjaa1234k"}';

    /**
     * Verify a bad regex
     */
    public function testBadRegexExceptions()
    {
        // ValidateException::ERROR_USER_REGEX_ERROR_LAST_ERROR_OCCURRED
        $exceptions = [
            ValidateException::ERROR_USER_REGEX_ERROR_LAST_ERROR_OCCURRED => [
                [
                    json_decode('{"username": "rob"}'),
                    json_decode('{"type": "object", "properties": {"username": {"type": "string", "pattern" : "--[92929{{))"}}}'),
                ],
                [
                    json_decode('{"username": "rob"}'),
                    json_decode('{"type": "object", "properties": {"username": {"type": "string", "pattern" : "[9JDJ(JKlk3ko93030???jerjhu2/22/JJSJ"}}}'),
                ]
            ]

            // @TODO; write tests for ValidateException::ERROR_USER_REGEX_PREG_LAST_ERROR_OCCURRED
            // @TODO; write tests for ValidateException::ERROR_USER_REGEX_UNKNOWN_ERROR_OCCURRED
            // @TODO; write tests for ValidateException::ERROR_USER_REGEX_GENERAL_ERROR_OCCURRED
        ];
        $this->executeExceptionValidation($exceptions, false);
    }

    /**
     * Verify other exceptions than the bad regex one
     */
    public function testRegexExceptions()
    {
        $exceptions = [
            ValidateException::ERROR_USER_REGEX_NO_MATCH => [
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
            ],
        ];

        $this->executeExceptionValidation($exceptions, false);
    }

    /**
     * Successful test
     */
    public function testRegexMatch()
    {
        $schema    = '{"type": "object", "properties": {"zipcode": {"type": "string", "pattern" : "[0-9]{4}[a-zA-Z]{2}"}}}';
        $validator = new SchemaValidator(json_decode('{"zipcode" : "48118EW"}'), json_decode($schema));
        $isValid   = $validator->isValid();
        $this->assertTrue($isValid);
    }

}
