<?php

namespace CMPayments\tests\SchemaValidator\Tests;

use CMPayments\Json\Exceptions\JsonException;
use CMPayments\Json\Json;
use CMPayments\JsonLint\Exceptions\ParseException;
use CMPayments\SchemaValidator\Exceptions\ValidateException;

class SchemaRegexTest extends BaseTest {

  const INVALID_SCHEMA_PATTERN_STRING = '{"type": "object","properties": {"name": {"type": "string","pattern" : "(*92190(***(0[a-z]+"}}}';
  const VALID_SCHEMA_PATTERN_STRING = '{"type": "object","properties": {"name": {"type": "string","pattern" : "[a-z]+"}}}';
  const DATA_VALID_STRING = '{"name" : "sjaak"}';
  const DATA_INVALID_STRING = '{"name" : "sjaa1234k"}';

  /**
   * Dirty setUp with 
   */
  public function setUp() {
	set_error_handler(function($severity, $message, $file, $line) {
	  if (!(error_reporting() & $severity)) {
		return;
	  }
	  throw new ErrorException($message, $severity, $severity, $file, $line);
	});
  }

  /**
   * Verify a bad regex 
   * @expectedException \Error
   */
  public function testBadRegexExceptions() {

	$exceptions = [

		ValidateException::ERROR_USER_REGEX_PATTERN_NOT_VALID => [
			[
				json_decode('{"username": "rob"}'),
				json_decode('{"type": "object", "properties": {"username": {"type": "string", "pattern" : "--[92929{{))"}}}'),
			]
		],
	];

	$this->executeExceptionValidation($exceptions, false);
  }

  /**
   * Verify other exceptions than the bad regex one
   */
  public function testOtherExceptions() {

	$exceptions = [
		ValidateException::ERROR_USER_REGEX_NOMATCH => [
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

 
  function testRegexMatch() {
	$schema = '{"type": "object", "properties": {"zipcode": {"type": "string", "pattern" : "[0-9]{4}[a-zA-Z]{2}"}}}';
	$validator = new \CMPayments\SchemaValidator\SchemaValidator(json_decode('{"zipcode" : "48118EW"}'), json_decode($schema));
	$isValid = $validator->isValid();
	$this->assertTrue($isValid);
  }

  /** @TODO; trigger an Exception where the Linter validates correctly but where json_decode() would still fail, should result in JsonException::ERROR_INPUT_IS_NOT_VALID_JSON */

  /**
   * @param $data
   * @param $schema
   * @param $method
   * @param $exceptionClass
   * @param $exceptionCode
   */
  protected function validateARequest($data, $schema, $method, $exceptionClass, $exceptionCode) {
	if ((new Json($data))->validate($schema, $errors)) {

	  $this->assertFalse($exceptionCode, $method . '; must result in an error but it didn\'t');
	} else {

	  $this->assertEquals($errors[0]['class'], $exceptionClass);
	  $this->assertEquals($errors[0]['code'], $exceptionCode);
	}
  }

}
