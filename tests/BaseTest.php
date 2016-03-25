<?php namespace CMPayments\tests\SchemaValidator\Tests;

use CMPayments\Cache\Cache;
use CMPayments\SchemaValidator\SchemaValidator;
use CMPayments\SchemaValidator\Exceptions\ValidateException;
use CMPayments\SchemaValidator\Exceptions\ValidateSchemaException;

/**
 * Class BaseTest
 *
 * @package CMPayments\tests\SchemaValidator\Tests
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
abstract class BaseTest extends \PHPUnit_Framework_TestCase
{
    const EMPTY_STRING                         = '';
    const INVALID_JSON                         = '{test}';
    const INVALID_EMPTY_JSON                   = '{test}';
    const VALID_EMPTY_JSON                     = '{}';
    const VALID_DATA_NUMBER_JSON               = '{"id": 2}';
    const VALID_SCHEMA_NUMBER_OPTIONAL_JSON    = '{"type": "object","properties": {"id": {"type": "number","minimum": 2,"maximum": 4}}}';
    const VALID_SCHEMA_NUMBER_REQUIRED_JSON    = '{"type": "object", "properties": {"id": {"type": "number","minimum": 2,"maximum": 4}},"required": ["id"]}';

    /**
     * @return array
     */
    protected function provideArrayForValidation($input)
    {
        // delete cache directory for optimal testing
        exec('rm -rf ' . (new Cache)->getDirectory() . '*');

        $checks = [];

        foreach ($input as $schema => $data) {
            $checks[] = [$schema, $data];
        }

        return $checks;
    }

    /**
     * Private end method for testing invalid values
     *
     * @param $schema
     * @param $falseValue
     */
    protected function executeSchemaValidatorWhichResultsInvalid($schema, $falseValue)
    {
        $data = (is_object($falseValue)) ? 'Empty Object' : print_r($falseValue, true);
        $msg  = vsprintf('The following schema \'%s\' validating against \'%s\' should assert to false but it didn\'t', [$schema, $data]);

        try {

            $this->assertFalse((new SchemaValidator($falseValue, json_decode($schema)))->isValid(), $msg);
        } catch (\Exception $e) {

            if ($e instanceof ValidateException OR $e instanceof ValidateSchemaException) {

                // will always fail
                $this->assertFalse('exception was thrown', $e->getMessage() . vsprintf(' with values Schema \'%s\' and Data \'%s\'', [$schema, $data]));
            } else {

                $this->assertTrue(false, vsprintf(
                    'Exception should be of type \'%s\' but got type \'%s\'',
                    [
                        implode('\', \'', [ValidateException::class, ValidateSchemaException::class]),
                        get_class($e)
                    ]
                ));
            }
        }
    }

    /**
     * Executes the Exception Validation
     *
     * @param array $exceptions
     * @param bool  $asSchema
     */
    protected function executeExceptionValidation(array $exceptions, $asSchema = true)
    {
        // delete cache directory for optimal testing
        exec('rm -rf ' . (new Cache)->getDirectory() . '*');

        foreach ($exceptions as $exception => $exceptionValues) {

            if (is_array($exceptionValues)) {

                foreach ($exceptionValues as $exceptionValue) {

                    $this->executeValidatorWhichResultsInException($exception, $exceptionValue, $asSchema);
                }
            } else {

                $this->executeValidatorWhichResultsInException($exception, $exceptionValues, $asSchema);
            }
        }
    }

    /**
     * protected end method for testing exceptions
     *
     * @param      $exception
     * @param      $exceptionValue
     * @param bool $asSchema
     */
    protected function executeValidatorWhichResultsInException($exception, $exceptionValue, $asSchema = true)
    {
        $data = (is_object($exceptionValue)) ? json_encode($exceptionValue) : print_r($exceptionValue, true);

        try {

            if ($asSchema) {
                new SchemaValidator(BaseTest::VALID_DATA_NUMBER_JSON, $exceptionValue);
                $this->assertFalse('exception was NOT thrown', vsprintf('The following Exception \'%s\' should be thrown when validating \'%s\' but no Exception was thrown', [$exception, $data]));
            } else {

                $schema  = json_decode(BaseTest::VALID_SCHEMA_NUMBER_OPTIONAL_JSON);
                $boolean = false;

                if (is_array($exceptionValue)) {

                    if (count($exceptionValue) === 3) {

                        list($exceptionValue, $schema, $boolean) = $exceptionValue;
                    } else {

                        list($exceptionValue, $schema) = $exceptionValue;
                    }
                }

                $validator = new SchemaValidator($exceptionValue, $schema);

                $msg = vsprintf('When validating Exception \'%s\' with Data \'%s\'', [$exception, $data]);

                if (!$boolean) {

                    $this->assertFalse($validator->isValid(), $msg);
                    $this->assertEquals($exception, $validator->getErrors()[0]['code'], $msg);
                } else {

                    $this->assertTrue($validator->isValid(), $msg);
                }
            }
        } catch (ValidateException $e) {

            $this->assertFalse('exception was thrown were no exception was not expected', vsprintf('The following Exception \'%s\' was thrown when validating \'%s\' but no Exception was expected', [$exception, $data]));
        } catch (ValidateSchemaException $e) {

            $this->assertEquals($exception, $e->getCode(), vsprintf('When validating Exception \'%s\' with Data \'%s\'', [$exception, $data]));
        }
    }
}