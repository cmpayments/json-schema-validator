<?php namespace CM\tests\JsonSchemaValidator\Tests;

use CM\JsonSchemaValidator\JsonSchemaValidator;
use CM\JsonSchemaValidator\Exceptions\ValidateException;

class SchemaTest extends BaseTest
{
    const CONFIG_DEBUG_TRUE_CACHE_DIR_MISSING  = ['debug' => true, 'cache.directory' => __DIR__ . '/non-existent/'];
    const CONFIG_DEBUG_FALSE_CACHE_DIR_MISSING = ['debug' => false, 'cache.directory' => __DIR__ . '/non-existent/'];
    const EMPTY_STRING                         = '';
    const INVALID_JSON                         = '{test}';
    const INVALID_EMPTY_JSON                   = '{test}';
    const VALID_EMPTY_JSON                     = '{}';
    const VALID_DATA_NUMBER_JSON               = '{"id": 2}';
    const VALID_SCHEMA_NUMBER_OPTIONAL_JSON    = '{"type": "object","properties": {"id": {"type": "number","minimum": 2,"maximum": 4}}}';

    /**
     * checking writability of non-existing cache directory when debug is ON must trigger an exception
     */
    public function testCacheDirectoryMustBeWritableWhenDebugIsOn()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_CACHE_DIRECTORY_NOT_WRITABLE);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, self::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, self::CONFIG_DEBUG_TRUE_CACHE_DIR_MISSING);
    }

    /**
     * checking writability of non-existing cache directory when debug is OFF must NOT trigger an exception
     */
    public function testCacheDirectoryMustBeWritableWhenDebugIsOff()
    {
        try {
            new JsonSchemaValidator(self::VALID_EMPTY_JSON, self::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, self::CONFIG_DEBUG_FALSE_CACHE_DIR_MISSING);
        } catch (\Exception $e) {

            // when an asserting goes wrong the method execution is stopped
            $this->assertTrue(false, 'A ValidateException was thrown were no ValidateException was expected.');
        }

        $this->assertTrue(true);
    }

    /**
     * $schema must at least contain an object
     */
    public function testSchemaMustAtLeastBeAnObject()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_NO_OBJECT);

        (new JsonSchemaValidator())->check(self::VALID_EMPTY_JSON, self::EMPTY_STRING);
    }

    /**
     * $schema must at least contain an object
     */
    public function testSchemaObjectCannotBeEmpty()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_CANNOT_BE_EMPTY);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, self::VALID_EMPTY_JSON);
    }

    /**
     * A $schema mandatory property is not set
     */
    public function testChildSchemaObjectCannotBeEmpty()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_CANNOT_BE_EMPTY);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {}}}');
    }

    /**
     * $schema properties key cannot be empty
     */
    public function testSchemaPropertiesKeyCannotBeEmpty()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_EMPTY_KEY_NOT_ALLOWED_IN_OBJECT);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"": {"type": "number","minimum": 2,"maximum": 4}}}');
    }

    /**
     * A $schema mandatory property is not set
     */
    public function testSchemaMandatoryPropertyIsNotSet()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_NOT_DEFINED);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"optionalProperty": "value"}}}');
    }

    /**
     * $schema property 'value' is not valid
     */
    public function testSchemaPropertyTypeIsNotValidWithType()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "non-existent-value"}}}');
    }

    /**
     * $schema property 'value' is not valid
     */
    public function testSchemaPropertyTypeIsNotValidWithFormat()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_VALUE_IS_NOT_VALID);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "string", "format": "non-existent"}}}');
    }

    /**
     * $schema property 'type' is not valid
     */
    public function testSchemaPropertyValueIsNotValid()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_TYPE_NOT_VALID);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "string", "minLength": "1"}}}');
    }

    /**
     * $schema property 'type' is not valid
     */
    public function testSchemaPropertyMaxLengthCannotBeZero()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_CANNOT_NOT_BE_ZERO);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "string", "maxLength": 0}}}');
    }

    /**
     * $schema property 'type' is not valid
     */
    public function testSchemaPropertyMaxitemsCannotBeZero()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_CANNOT_NOT_BE_ZERO);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "array", "items": {}, "maxItems": 0}}}');
    }

    /**
     * $schema property 'type' is not valid
     */
    public function testSchemaPropertyMaximumCannotBeZero()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_CANNOT_NOT_BE_ZERO);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "number", "maximum": 0}}}');
    }

    /**
     * $schema property 'minLength' cannot be bigger than 'maxLength'
     */
    public function testSchemaPropertyMinLengthCannotBeBiggerThanMaxLengthCannotBeZero()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "string", "minLength": 2, "maxLength": 1}}}');
    }

    /**
     * $schema property 'minimum' cannot be minItems than 'maxItems'
     */
    public function testSchemaPropertyMinItemsCannotBeBiggerThanMaxitemsCannotBeZero()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "array", "items": {}, "minItems": 2, "maxItems": 1}}}');
    }

    /**
     * $schema property 'minimum' cannot be bigger than 'maximum'
     */
    public function testSchemaPropertyMinimumCannotBeBiggerThanMaximumCannotBeZero()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_MIN_NOT_BIGGER_THAN_MAX);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "number", "minimum": 2, "maximum": 1}}}');
    }

    /**
     * $schema property 'Required' must be an array (object test)
     */
    public function testSchemaPropertyRequiredMustBeAnArray()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "number"}}, "required": {"test1": "test123", "test2": "test123"}}');
    }

    /**
     * $schema property 'Required' must be an array (boolean test)
     */
    public function testSchemaPropertyRequiredMustBeAnArray2()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_PROPERTY_REQUIRED_MUST_BE_AN_ARRAY);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "number"}}, "required": true}');
    }

    /**
     * $schema property 'Required' items must match the 'properties' items
     */
    public function testSchemaPropertyRequiredItemsMustMatchPropertiesItems()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_SCHEMA_REQUIRED_AND_PROPERTIES_MUST_MATCH);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "number"}}, "required": ["id", "non-existent"]}');
    }

    /**
     * Check if for every $validType in ValidateException::validTypes a validator method exist
     */
    public function testSchemaValidationMethodDoesNotExist()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_VALIDATION_METHOD_DOES_NOT_EXIST);

        $validator = new JsonSchemaValidator();
        $validator->addValidType('nieuwType');
        $validator->check(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"type": "number"}}, "required": ["id"]}');
    }

    /**
     * Check if a requested Reference actually exists
     */
    public function testSchemaCheckIfReferenceExistsLocally()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_INVALID_REFERENCE);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"$ref": "non-existent-and-invalid"}}}');
    }

    /**
     * Check if any References are locally defined 1
     */
    public function testSchemaCheckIfAnyReferencesAreDefinedLocally1()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}, "definitions": {}}');
    }

    /**
     * Check if any References are locally defined 2
     */
    public function testSchemaCheckIfAnyReferencesAreDefinedLocally2()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_NO_LOCAL_DEFINITIONS_HAVE_BEEN_DEFINED);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}}');
    }

    /**
     * Check if the requested local References is defined in the locally defined definitions
     */
    public function testSchemaCheckIfRequestedReferencesIsDefinedLocally()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_CHECK_IF_LOCAL_DEFINITIONS_EXISTS);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"$ref": "#/definitions/non-existent-but-valid"}}, "definitions": {"id": {}}}');
    }

    /**
     * Check if the requested remote References was found // 404
     */
    public function testSchemaCheckIfRequestedRemoteReferencesWasNotFound()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_REMOTE_REFERENCE_DOES_NOT_EXIST);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"$ref": "http://json-schema.org/non-existent"}}}');
    }

    /**
     * Check if a existing remote References has returned any data
     */
    public function testSchemaCheckIfRequestedRemoteReferencesReturnedAnyData()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_NO_JSON_SCHEMA_WAS_FOUND);

        new JsonSchemaValidator(self::VALID_EMPTY_JSON, '{"type": "object","properties": {"id": {"$ref": "http://boy.dev.clubmessage.local/empty.php"}}}');
    }

    /**
     * Verify Input other than string
     */
    public function testSchemaMustAtLeastBeAnObjectWithBooleanInput()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_INPUT_IS_NOT_A_STRING);

        (new JsonSchemaValidator())->check(self::VALID_EMPTY_JSON, true);
    }

    /**
     * Verify Input other than string
     */
    public function testDataMustAtLeastBeAnObjectWithBooleanInput()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_INPUT_IS_NOT_A_STRING);

        (new JsonSchemaValidator())->check(true, self::VALID_EMPTY_JSON);
    }

    /**
     * Verify Input other than string
     */
    public function testDataMustAtLeastBeAnObjectWithArrayInput()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_INPUT_IS_NOT_A_STRING);

        (new JsonSchemaValidator())->check([], self::VALID_EMPTY_JSON);
    }

    /**
     * Verify that Schema input is valid JSON
     */
    public function testSchemaMustBeValidJSON()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_INPUT_IS_NOT_VALID_JSON);

        (new JsonSchemaValidator())->check(self::VALID_DATA_NUMBER_JSON, self::INVALID_JSON);
    }

    /**
     * Verify that Data input is valid JSON
     */
    public function testDataMustBeValidJSON()
    {
        $this->setExpectedException(ValidateException::class, '', ValidateException::ERROR_INPUT_IS_NOT_VALID_JSON);

        (new JsonSchemaValidator())->check(self::INVALID_JSON, self::VALID_SCHEMA_NUMBER_OPTIONAL_JSON);
    }
}