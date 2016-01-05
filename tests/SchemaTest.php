<?php namespace CMPayments\tests\SchemaValidator\Tests;

use CMPayments\SchemaValidator\Exceptions\ValidateException;

class SchemaTest extends BaseTest
{
    /**
     * Test Exceptions
     */
    public function testParseExceptions()
    {
        $exceptions = [
            ValidateException::ERROR_INPUT_IS_NOT_A_OBJECT                     => [
                json_decode('{"type" => "boolean"}'), // invalid JSON
                json_decode(true),
                json_decode(1),
                json_decode(1.1),
                json_decode('test123'),
                null,
                [],
                true,
                1,
                1.2,
                'test123',
                curl_init(),
                function () {
                },
                json_decode('{"type": "object", "properties": {"id": true}}'),
                json_decode('{"type": "object", "properties": {"id": null}}'),
                json_decode('{"type": "object", "properties": {"id": 1}}'),
                json_decode('{"type": "object", "properties": {"id": 2.3}}'),
                json_decode('{"type": "object", "properties": {"id": ""}}'),
                json_decode('{"type": "object", "properties": {"id": []}}')
            ],
            ValidateException::ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH            => [
                new \StdClass,
                json_decode('{}'),
                json_decode('{"type": "object", "properties": {}}'),
                json_decode('{"type": "object", "properties": {"id": {}}}')
            ],
            ValidateException::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT           => [
                json_decode('{"type": "object", "properties": {"": "test123"}}'),
                json_decode('{"type": "object", "properties": {"_empty_": "test123"}}'),
                json_decode('{"type": "object", "properties": {"": {"type": "number","minimum": 2,"maximum": 4}}}')
            ],
            ValidateException::ERROR_SCHEMA_PROPERTY_NOT_DEFINED               => [
                json_decode('{"": "object"}'),
                json_decode('{"type": "object", "properties": {"id": {"optionalProperty": "value"}}}')
            ],
            ValidateException::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID        => [
                json_decode('{"type": "object", "properties": {"id": {"type": "non-existent-value"}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "string", "format": "non-existent"}}}')
            ],
            ValidateException::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID            => [
                json_decode('{"type": "object", "properties": {"id": {"type": "string", "minLength": "1"}}}')
            ],
            ValidateException::ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO    => [
                json_decode('{"type": "object", "properties": {"id": {"type": "string", "maxLength": 0}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "number", "maximum": 0}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "array", "items": {}, "maxItems": 0}}}')
            ],
            ValidateException::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX   => [
                json_decode('{"type": "object", "properties": {"id": {"type": "number", "minimum": 2, "maximum": 1}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "string", "minLength": 2, "maxLength": 1}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "array", "items": {}, "minItems": 2, "maxItems": 1}}}')
            ],
            ValidateException::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY => [
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": true}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": 1}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": 1.4}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": {}}')
            ],
            ValidateException::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH => [
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": ["id", "non-existent"]}')
            ],
            ValidateException::ERROR_INVALID_REFERENCE                                                => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "non-existent-and-invalid"}}}')
            ],
            ValidateException::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED                           => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}}'),
                json_decode('{"type": "object", "properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}, "definitions": {}}')
            ],
            ValidateException::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS                                => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}, "definitions": {"id": {}}}')
            ],
            /** @TODO; trigger an Exception where the CURL extension is unavailable */
            ValidateException::ERROR_CURL_NOT_INSTALLED                                               => [],
            ValidateException::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST                                  => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "http://json-schema.org/non-existent"}}}')
            ],
            ValidateException::ERROR_NO_JSON_SCHEMA_WAS_FOUND                                         => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "http://boy.dev.clubmessage.local/cmpayments/_keep/empty.php"}}}')
            ],
            /** @TODO; Write test */
            ValidateException::ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION                                 => [

            ]
        ];

        $this->executeExceptionValidation($exceptions);
    }
}

