<?php namespace CMPayments\tests\SchemaValidator\Tests;

use CMPayments\SchemaValidator\Exceptions\ValidateSchemaException;

/**
 * Class SchemaTest
 *
 * @package CMPayments\tests\SchemaValidator\Tests
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
class SchemaTest extends BaseTest
{
    /**
     * Test Exceptions
     */
    public function testParseExceptions()
    {
        $exceptions = [
            ValidateSchemaException::ERROR_INPUT_IS_NOT_A_OBJECT                     => [
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
            ValidateSchemaException::ERROR_SCHEMA_CANNOT_BE_EMPTY_IN_PATH            => [
                new \StdClass,
                json_decode('{}'),
                json_decode('{"type": "object", "properties": {}}'),
                json_decode('{"type": "object", "properties": {"id": {}}}')
            ],
            ValidateSchemaException::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT           => [
                json_decode('{"type": "object", "properties": {"": "test123"}}'),
                json_decode('{"type": "object", "properties": {"_empty_": "test123"}}'),
                json_decode('{"type": "object", "properties": {"": {"type": "number","minimum": 2,"maximum": 4}}}')
            ],
            ValidateSchemaException::ERROR_SCHEMA_PROPERTY_NOT_DEFINED               => [
                json_decode('{"": "object"}'),
                json_decode('{"type": "object", "properties": {"id": {"optionalProperty": "value"}}}')
            ],
            ValidateSchemaException::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID        => [
                json_decode('{"type": "object", "properties": {"id": {"type": "non-existent-value"}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "string", "format": "non-existent"}}}')
            ],
            ValidateSchemaException::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID            => [
                json_decode('{"type": "object", "properties": {"id": {"type": "string", "minLength": "1"}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": ["string", null]}}}'),
            ],
            ValidateSchemaException::ERROR_SCHEMA_MAX_PROPERTY_CANNOT_NOT_BE_ZERO    => [
                json_decode('{"type": "object", "properties": {"id": {"type": "string", "maxLength": 0}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "number", "maximum": 0}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "array", "items": {}, "maxItems": 0}}}')
            ],
            ValidateSchemaException::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX   => [
                json_decode('{"type": "object", "properties": {"id": {"type": "number", "minimum": 2, "maximum": 1}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "string", "minLength": 2, "maxLength": 1}}}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "array", "items": {}, "minItems": 2, "maxItems": 1}}}')
            ],
            ValidateSchemaException::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY => [
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": true}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": 1}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": 1.4}'),
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": {}}')
            ],
            ValidateSchemaException::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH => [
                json_decode('{"type": "object", "properties": {"id": {"type": "number"}}, "required": ["id", "non-existent"]}')
            ],
            ValidateSchemaException::ERROR_INVALID_REFERENCE                         => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "non-existent-and-invalid"}}}')
            ],
            ValidateSchemaException::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED    => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}}'),
                json_decode('{"type": "object", "properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}, "definitions": {}}')
            ],
            ValidateSchemaException::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS         => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}, "definitions": {"id": {}}}')
            ],
            /** @TODO; trigger an Exception where the CURL extension is unavailable */
            ValidateSchemaException::ERROR_CURL_NOT_INSTALLED                        => [],
            ValidateSchemaException::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST           => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "http://json-schema.org/non-existent"}}}')
            ],
            ValidateSchemaException::ERROR_NO_DATA_WAS_FOUND_IN_REMOTE_SCHEMA        => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "https://raw.githubusercontent.com/cmpayments/json-schema-validator/master/tests/_empty.php"}}}')
            ],
            ValidateSchemaException::ERROR_NO_VALID_JSON_WAS_FOUND_IN_REMOTE_SCHEMA  => [
                json_decode('{"type": "object", "properties": {"id": {"$ref": "https://raw.githubusercontent.com/cmpayments/json-schema-validator/master/tests/_invalid_json.php"}}}')
            ],
            /** @TODO; Write test */
            ValidateSchemaException::ERROR_INPUT_IS_NOT_A_VALID_PREPOSITION          => [

            ],
            ValidateSchemaException::ERROR_SCHEMA_PROPERTY_TYPES_NOT_UNIQUE          => [
                json_decode('{"type": "object", "properties": {"id": {"type": ["number", "number"]}}, "required": true}'),
            ],
        ];

        $this->executeExceptionValidation($exceptions);
    }
}

