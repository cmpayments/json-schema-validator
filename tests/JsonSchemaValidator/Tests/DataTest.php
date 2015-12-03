<?php namespace CM\tests\JsonSchemaValidator\Tests;

use CM\JsonSchemaValidator\JsonSchemaValidator;
use CM\JsonSchemaValidator\Exceptions\ValidateException;

class DataTest extends BaseTest
{
    const VALID_SCHEMA_NUMBER_REQUIRED_JSON = '{"type": "object","properties": {"id": {"type": "number","minimum": 2,"maximum": 4}},"required": ["id"]}';

    /**
     * Verify that all required properties are set in $data->properties->requiredProperty
     */
    public function testValidateIfAllRequiredPropertiesAreSet()
    {
        $this->validateARequest('{"optionalProperty": 1}', self::VALID_SCHEMA_NUMBER_REQUIRED_JSON, __METHOD__, ValidateException::USER_CHECK_IF_ALL_REQUIRED_PROPERTIES_ARE_SET);
    }

    /**
     * Verify that all required properties are set in $data->properties->requiredProperty
     */
    public function testIfAllAttributesAreDefinedInTheSchema()
    {
        $data   = '{"testProperty": {"length": 7.0, "superfluousProperty": 12.0}}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "object","properties": {"length": {"type": "number"}}}},"additionalProperties": false}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_DATA_PROPERTY_IS_NOT_A_VALID_PROPERTY);
    }

    /**
     * Verify that all required properties are set in $data->properties->requiredProperty
     */
    public function testIfDataValueDoesNotMatchWhenDealingWithArray()
    {
        $data   = '{"testProperty": ["value1", "value2"]}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "object","properties": {"length": {"type": "number"}}}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_1);
    }

    /**
     * Verify that all required properties are set in $data->properties->requiredProperty
     */
    public function testIfDataValueDoesNotMatchWhenDealingWithInteger()
    {
        $data   = '{"testProperty": 2}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "object","properties": {"length": {"type": "number"}}}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_DATA_VALUE_DOES_NOT_MATCH_CORRECT_TYPE_2);
    }

    /**
     * Verify that a count of array items matches a specific minimum
     */
    public function testThatACountOfArrayItemsMatchesASpecificMinimum()
    {
        $data   = '{"testProperty": ["1", "2", "3", "4", "5", "6", "7"]}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "array", "items": {"type": "string"}, "minItems": 8}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_ARRAY_MINIMUM_CHECK);
    }

    /**
     * Verify that a count of array items matches a specific minimum
     */
    public function testThatACountOfArrayItemsMatchesASpecificMaximum()
    {
        $data   = '{"testProperty": ["1", "2", "3", "4", "5", "6", "7"]}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "array", "items": {"type": "string"}, "maxItems": 6}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_ARRAY_MAXIMUM_CHECK);
    }

    /**
     * Verify that all array items are unique (when dealing with strings)
     */
    public function testThatAllItemsOfArrayAreUniqueWithStrings()
    {
        $data   = '{"testProperty": ["1", "2", "3", "4", "5", "6", "7", "7"]}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "array", "items": {"type": "string"}, "uniqueItems": true}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_ARRAY_NO_DUPLICATES_ALLOWED);
    }

    /**
     * Verify that all array items are unique (when dealing with objects)
     */
    public function testThatAllItemsOfArrayAreUniqueWithObjects()
    {
        $data   = '{"testProperty": [{"property": "value"}, {"property": "value"}]}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "array", "items": {"type": "object"}, "uniqueItems": true}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_ARRAY_NO_DUPLICATES_ALLOWED);
    }

    /**
     * Verify that a value exists in a predefined list
     */
    public function testThatAValueExistsInAPredefinedList()
    {
        $data   = '{"testProperty": "value1"}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "string", "enum": ["value2", "value3"]}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_ENUM_NEEDLE_NOT_FOUND_IN_HAYSTACK);
    }

    /**
     * Verify that a value is a valid DateTime
     */
    public function testThatAValueContainsValidDateTime()
    {
        $data   = '{"testProperty": "%s"}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "string","format": "datetime"}}}';

        $this->assertTrue((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42+00:00'), $schema))->isValid());
        $this->assertTrue((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42Z'), $schema))->isValid());

        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-28-11T10:16:42Z'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '1449150418'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '28-11-2015'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '16:58'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '16:58:45'), $schema))->isValid());
    }

    /**
     * Verify that a value is a valid Date
     */
    public function testThatAValueContainsValidDate()
    {
        $data   = '{"testProperty": "%s"}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "string","format": "date"}}}';

        $this->assertTrue((new JsonSchemaValidator(sprintf($data, '2015-11-28'), $schema))->isValid());

        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42+00:00'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42Z'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '1449150418'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '28-11-2015'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '16:58'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '16:58:45'), $schema))->isValid());
    }

    /**
     * Verify that a value is a valid Time
     */
    public function testThatAValueContainsValidTime()
    {
        $data   = '{"testProperty": "%s"}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "string","format": "time"}}}';

        $this->assertTrue((new JsonSchemaValidator(sprintf($data, '16:58:45'), $schema))->isValid());

        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42+00:00'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42Z'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28T10:16:42'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '1449150418'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '2015-11-28'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '28-11-2015'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '16:58'), $schema))->isValid());
    }

    /**
     * Verify that a value is a valid UTC Seconds
     */
    public function testThatAValueContainsValidSeconds()
    {
        $numberData   = '{"testProperty": %s}';
        $numberSchema = '{"type": "object","properties": {"testProperty": {"type": "number","format": "utc-seconds"}}}';

        $stringData   = '{"testProperty": "%s"}';
        $stringSchema = '{"type": "object","properties": {"testProperty": {"type": "string","format": "utc-seconds"}}}';

        $this->assertTrue((new JsonSchemaValidator(sprintf($stringData, '1449150418'), $stringSchema))->isValid());
        $this->assertTrue((new JsonSchemaValidator(sprintf($numberData, 1449150418), $numberSchema))->isValid());

        $this->assertFalse((new JsonSchemaValidator(sprintf($stringData, '16:58:45'), $stringSchema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($stringData, '2015-11-28T10:16:42+00:00'), $stringSchema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($stringData, '2015-11-28T10:16:42Z'), $stringSchema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($stringData, '2015-11-28T10:16:42'), $stringSchema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($stringData, '2015-11-28'), $stringSchema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($stringData, '28-11-2015'), $stringSchema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($stringData, '16:58'), $stringSchema))->isValid());
    }

    /**
     * Verify that a value is a valid Time
     */
    public function testThatAValueContainsValidEmail()
    {
        $data   = '{"testProperty": "%s"}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "string","format": "email"}}}';

        $this->assertTrue((new JsonSchemaValidator(sprintf($data, 'bw@cm.nl'), $schema))->isValid());

        $this->assertFalse((new JsonSchemaValidator(sprintf($data, 'bw@cm'), $schema))->isValid());
        $this->assertFalse((new JsonSchemaValidator(sprintf($data, '@cm.nl'), $schema))->isValid());
    }

    /**
     * Verify that a string length matches minimum length
     */
    public function testThatStrLenMatchesAMinimumLength()
    {
        $data   = '{"testProperty": "12345678"}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "string", "minLength": 9}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_STRING_MINIMUM_CHECK);
    }

    /**
     * Verify that a string length matches maximum length
     */
    public function testThatStrLenMatchesAMaximumLength()
    {
        $data   = '{"testProperty": "12345678"}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "string", "maxLength": 7}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_STRING_MAXIMUM_CHECK);
    }

    /**
     * Verify that a number matches a specific minimum
     */
    public function testThatANumberMatchesASpecificMinimum()
    {
        $data   = '{"testProperty": 2}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "number", "minimum": 3}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_NUMBER_MINIMUM_CHECK);
    }

    /**
     * Verify that a number matches a specific maximum
     */
    public function testThatANumberMatchesASpecificMaximum()
    {
        $data   = '{"testProperty": 2}';
        $schema = '{"type": "object","properties": {"testProperty": {"type": "number", "maximum": 1}}}';

        $this->validateARequest($data, $schema, __METHOD__, ValidateException::USER_NUMBER_MAXIMUM_CHECK);
    }

    /**
     * @param $data
     * @param $schema
     * @param $method
     * @param $exceptionCode
     *
     * @throws ValidateException
     */
    protected function validateARequest($data, $schema, $method, $exceptionCode)
    {
        $validator = new JsonSchemaValidator($data, $schema);

        if ($validator->isValid()) {

            var_dump(md5($schema));
            $this->assertFalse($exceptionCode, $method . '; must result in an error but it didn\'t');
        } else {

            $this->assertEquals($validator->getErrors()[0]['error'], $exceptionCode);
        }
    }
}