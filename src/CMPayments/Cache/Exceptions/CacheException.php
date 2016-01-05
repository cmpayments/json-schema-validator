<?php namespace CMPayments\Cache\Exceptions;

use CMPayments\SchemaValidator\Exceptions\BaseException;

class CacheException extends BaseException
{
    const ERROR_CACHE_DIRECTORY_NOT_SET      = 1;
    const ERROR_CACHE_FILENAME_NOT_SET       = 2;
    const ERROR_CACHE_DIRECTORY_NOT_WRITABLE = 3;

    protected $messages = [
        self::ERROR_CACHE_DIRECTORY_NOT_SET      => 'The cache directory option \'%s\' is not set',
        self::ERROR_CACHE_FILENAME_NOT_SET       => 'The cache filename option \'%s\' is not set',
        self::ERROR_CACHE_DIRECTORY_NOT_WRITABLE => 'The cache directory \'%s\' is not writable'
    ];
}