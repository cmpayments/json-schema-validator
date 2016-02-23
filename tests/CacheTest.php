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
        $this->setExpectedException(CacheException::class, '', CacheException::ERROR_CACHE_DIRECTORY_NOT_WRITABLE);

        $cache = new Cache();
        $cache->setOptions(self::CONFIG_DEBUG_TRUE_CACHE_DIR_MISSING);

        new SchemaValidator(json_decode(self::VALID_EMPTY_JSON), json_decode(self::VALID_SCHEMA_NUMBER_OPTIONAL_JSON), $cache);
    }

    /**
     * checking writability of non-existing cache directory when debug is OFF must NOT trigger an exception
     */
    public function testCacheDirectoryMustBeWritableWhenDebugIsOff()
    {
        try {

            $cache = new Cache();
            $cache->setOptions(self::CONFIG_DEBUG_FALSE_CACHE_DIR_MISSING);

            new SchemaValidator(json_decode(self::VALID_EMPTY_JSON), json_decode(self::VALID_SCHEMA_NUMBER_OPTIONAL_JSON), $cache);
        } catch (\Exception $e) {

            // when an asserting goes wrong the method execution is stopped
            $this->assertTrue(false, 'A ValidateException was thrown were no ValidateException was expected.');
        }
    }
}