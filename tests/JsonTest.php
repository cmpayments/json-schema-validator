<?php namespace CMPayments\tests\SchemaValidator\Tests;

use CMPayments\Json\Exceptions\JsonException;
use CMPayments\Json\Json;
use CMPayments\JsonLint\Exceptions\ParseException;

class JsonTest extends BaseTest
{
    /**
     * Verify that Data input is NOT valid JSON
     */
    public function testDataMustBeString()
    {
        $this->validateARequest(null, BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(true, BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(1, BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(1.4, BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest([], BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(function () {
            return true;
        }, BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(curl_init(), BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(new \stdClass(), BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
    }

    /**
     * Verify that Schema input is valid JSON
     */
    public function testSchemaMustBeString()
    {
        $this->assertTrue((new Json(BaseTest::VALID_DATA_NUMBER_JSON))->validate());
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, true, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, 1, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, 1.4, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, [], __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, function () {
            return true;
        }, __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, curl_init(), __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, new \stdClass(), __METHOD__, JsonException::class, JsonException::ERROR_INPUT_IS_NOT_OF_TYPE_STRING);
    }

    /**
     * Verify that Data & Schema input is invalid JSON
     */
    public function testDataIsStringButIsValidEmptyJSON()
    {
        $this->validateARequest(BaseTest::INVALID_JSON, BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, ParseException::class, ParseException::ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE);
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, BaseTest::INVALID_EMPTY_JSON, __METHOD__, ParseException::class, ParseException::ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE);
        $this->validateARequest(BaseTest::INVALID_JSON, BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON, __METHOD__, ParseException::class, ParseException::ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE);
        $this->validateARequest(BaseTest::VALID_DATA_NUMBER_JSON, BaseTest::INVALID_JSON, __METHOD__, ParseException::class, ParseException::ERROR_EXPECTED_INPUT_TO_BE_SOMETHING_ELSE);
    }

    /**
     * Verify that Data & Schema input is valid JSON
     */
    public function testSchemaMustBeValidJSONNull()
    {
        $this->assertTrue((new Json(BaseTest::VALID_EMPTY_JSON))->validate());
        $this->assertTrue((new Json(BaseTest::VALID_DATA_NUMBER_JSON))->validate());
        $this->assertTrue((new Json(BaseTest::VALID_DATA_NUMBER_JSON))->validate(null));
        $this->assertTrue((new Json(BaseTest::VALID_DATA_NUMBER_JSON))->validate(BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON));
    }

    /** @TODO; trigger an Exception where the Linter validates correctly but where json_decode() would still fail, should result in JsonException::ERROR_INPUT_IS_NOT_VALID_JSON */

    /**
     * @param $data
     * @param $schema
     * @param $method
     * @param $exceptionClass
     * @param $exceptionCode
     */
    protected function validateARequest($data, $schema, $method, $exceptionClass, $exceptionCode)
    {
        if ((new Json($data))->validate($schema, $errors)) {

            $this->assertFalse($exceptionCode, $method . '; must result in an error but it didn\'t');
        } else {

            $this->assertEquals($errors[0]['class'], $exceptionClass);
            $this->assertEquals($errors[0]['code'], $exceptionCode);
        }
    }
}
