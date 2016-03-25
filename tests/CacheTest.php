<?php namespace CMPayments\tests\SchemaValidator\Tests;

use CMPayments\Cache\Cache;
use CMPayments\Cache\Exceptions\CacheException;
use CMPayments\SchemaValidator\SchemaValidator;

/**
 * Class CacheTest
 *
 * @package CMPayments\tests\SchemaValidator\Tests
 * @Author  Boy Wijnmaalen <boy.wijnmaalen@cmtelecom.com>
 */
class CacheTest extends BaseTest
{
    /**
     * checking writability of non-existing cache directory when debug is ON must trigger an exception
     */
    public function testCacheDirectoryMustBeWritableWhenDebugIsOn()
    {
        $optionsList = [
            ['debug' => true, 'directory' => __DIR__ . '/non-existent/'],
            ['debug' => false, 'directory' => __DIR__ . '/non-existent/']
        ];

        foreach($optionsList as $options) {

            $warnings = [];
            $cache    = new Cache($options, $warnings);

            new SchemaValidator(json_decode(self::VALID_EMPTY_JSON), json_decode(self::VALID_SCHEMA_NUMBER_OPTIONAL_JSON), $cache);

            $this->assertTrue(isset($warnings['warnings'][0]));
            $this->assertEquals(isset($warnings['warnings'][0]), 1);
            $this->assertEquals($warnings['warnings'][0]['code'], CacheException::ERROR_CACHE_DIRECTORY_NOT_WRITABLE);
        }
    }
}